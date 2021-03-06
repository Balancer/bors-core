<?php

require_once BORS_CORE.'/inc/texts.php';

class bors_external_feeds_entry extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function ignore_on_new_instance() { return true; }

	function class_title() { return 'Запись импорта RSS'; }

	function table_name() { return 'external_feeds_entries'; }
	function table_fields()
	{
		return array(
			'id',
			'entry_url',
			'pub_date' => array('type' => 'timestamp'),
			'simplepie_item_raw',
			'title',
			'keywords_string',
			'text',
			'author_name',
			'feed_id',
			'entry_id',
			'target_class_name',
			'target_object_id',
			'create_time',
			'modify_time',
			'is_suspended',
		);
	}

	function keywords() { return preg_split('/\s*,\s*/', $this->keywords_string()); }

	function auto_objects()
	{
		return array_merge(parent::auto_objects(), array(
			'feed' => 'bors_external_feed(feed_id)',
			'blog' => 'balancer_board_blog(target_object_id)',
		));
	}

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'target' => 'target_class_name(target_object_id)',
		));
	}

	function check_target()
	{
		$t = $this->target();
		if($t)
			return $t;

		$b = bors_find_first('balancer_board_blog', array(
			'blog_source_class' => 'bors_external_feeds_entry',
			'blog_source_id' => $this->id(),
			'order' => 'post_id',
		));

		if($b)
		{
			$t = $b->post();
			$this->set_attr('target', $t);
			$this->set_target_object_id($t->id(), true);
			return $t;
		}

		$this->set_target_class_name(NULL);
		$this->set_target_object_id(NULL);
		return NULL;
	}

	function make_source()
	{
		$link = $this->entry_url();
		$text = $this->text();

		if($this->feed_id() == 2) // Juick
		{
			// Разворачиваем в BB-код теги picasaweb с Juick'а
			$text = preg_replace('!<a href="https?://picasaweb.google.\w+.+photo/(.+?)\?feat=directlink.*?</a>!', "\n[picasa notitle]\\1[/picasa]\n", $text);
			// Разворачиваем в BB-код картинки
			// <a href="http://img200.imageshack.us/img200/2530/screenshotdl.png" rel="nofollow">img200.imageshack.us</a>
			$text = preg_replace('!<a href="([^"]+?\.(jpe?g|png|gif))" rel="nofollow">[^\s\<]+</a>!i', "[img $1]", $text);
//			exit("$text");
			$text = preg_replace('!<a href="http://www.youtube.\w+/watch\?v=([^"%]+)" rel="nofollow">youtube.com</a>!', '[youtube]$1[/youtube]', $text);
			$text = preg_replace('!<a href="http://smotri.com/video/view/\?id=([^"]+)" rel="nofollow">smotri.com</a>!', '[smotricom]$1[/smotricom]', $text);
			$text = preg_replace('!<a href="http://vimeo.com/(\d+)" rel="nofollow">vimeo.com</a>!', '[vimeo]$1[/vimeo]', $text);
			$text = preg_replace('!<a href="(http://[^/]*fotki.yandex.ru/get/\d+/[^/]+/\w+_(XL|orig))" rel="nofollow">[^<]+</a>!', '[img $1]', $text);
			// http://img821.imageshack.us/img821/933/gamedevcaptcha.png
			$text = preg_replace('!<a href="(http://\w+.imageshack.us/\w+/\w+/[^"/]+)" rel="nofollow">[^<]+</a>!', '[img $1]', $text);
			// http://pics.livejournal.com/morenwen/pic/0023etrp
			$text = preg_replace('!<a href="(http://pics.livejournal.com/\w+/pic/[^"]+)" rel="nofollow">[^<]+</a>!', '[img $1]', $text);
		}

		$text = html2bb(bors_close_tags($text), array('origin_url' => $link, 'strip_forms' => true));
		$feed = $this->feed();
		$text = explode("\n", $text);
		$limit = $feed->para_limit();
		if($limit && count($text) > $limit)
		{
			$text = array_slice($text, 0, $limit);
			$text[] = "\n[small][...][/small]";
			$text[] = "\n// Полный текст по адресу [url]{$link}[/url] ...\n";
		}
		else
			$text[] = "\n[span class=\"transgray\"]// Транслировано с ".$this->url_host_link($link)."[/span]\n";

		$text = str_replace("/\n{3,}/", "\n\n", join("\n", $text));
		if($this->feed()->titles_in_posts())
			$text = "[b]{$this->title()}[/b]\n\n$text";

		return $text;
	}

	function recalculate() { $this->update_target(true); }

	static function url_host_link($url)
	{
		if(preg_match('!https?://([^/]+)!', $url, $m))
//			return "[url={$url}]{$m[1]}[/url]"; — однако, почему-то глючит: http://balancer.ru/g/p2618390
			return "[a href=\"{$url}\"]{$m[1]}[/a]";

		return "[url]{$url}[/url]";
	}

	static function url_host_link_html($url)
	{
		if(preg_match('!https?://([^/]+)!', $url, $m))
			return "<a href=\"{$url}\">{$m[1]}</a>";

		return "<a href=\"{$url}\">{$url}</a>";
	}

	function update_target($update_lcml = false, $find_topic = true)
	{
		$feed = $this->feed();

//		echo var_dump($feed->skip_entry_content_regexp());

		// Если это не транслируемая запись
		if($feed->skip_entry_content_regexp() && preg_match('!'.$feed->skip_entry_content_regexp().'!i', $this->text()))
		{
			$post = $this->check_target();

			if(!$feed->parser_class_name() || !$post || $post->is_deleted()) // Не транслировалась или уже скрыта
				return;

			// Нужно скрыть запись
			$post->set_is_deleted(true);
			echo "Hide post {$post->debug_title()}\n";
			return;
		}


		if($parser_class_name = $feed->parser_class_name())
		{
//			exit($parser_class_name);
			$parser = new $parser_class_name(NULL);
			$data = $parser->parse(array(
				'title' => $this->title(),
				'text' => $this->text(),
				'link' => $this->entry_url(),
				'raw_data' => $this->simplepie_item_raw(),
				'feed' => $feed,
			));

			$source	= @$data['bb_code'];
			$markup	= @$data['markup'];
			$tags	= @$data['tags'];
			$title	= @$data['title'];
			if(!$markup)
				$source .= "\n\n[span class=\"transgray\"]// Транслировано с ".$this->url_host_link($this->entry_url()).".[/span]\n";
		}
		else
		{
//			exit('nope');
			$source = $this->make_source();
			$title = NULL;
		}

		if(empty($tags))
			$keywords = join(',', $this->full_keywords_list());
		else
			$keywords = join(',', $this->full_keywords_list($tags));

		$owner_id = $feed->owner_id();
		$owner_name = $this->author_name() ? $this->author_name() : $feed->owner_name();

		if($this->get('target_object_id')) // Уже было запощено ранее.
		{
			$post = $this->check_target();
			if(!$post)
				return;

			$post->set_owner_id($owner_id, true);
			$post->set_author_name($owner_name, true);
			$post->set_markup_class_name(@$markup, true);
			$post->set_source($source, true);
			$post->set_body(NULL, true);
			$post->set_post_body(NULL, true);
			$post->cache_clean();
			$post->store();

			if($update_lcml)
			{
				config_set('lcml_cache_disable', true);
				$post->post_body();

				$post->set_post_body(NULL, true);
				$post->set_warning_id(NULL, true);
				$post->set_flag_db(NULL, true);
				$post->cache_clean();
				$post->store();

//				$blog = bors_load('balancer_board_blog', $post->id());

				$post->body();
			}

			$topic = $post->topic();
			$topic->cache_clean();
			$topic->set_modify_time(time(), true);
			$topic->store();

			$pub_date = $this->pub_date();
			if(!$pub_date)
				$pub_date = $this->create_time();

			if(!$pub_date)
				bors_debug::syslog('error_rss_translation', "No pubdate for ".$this->debug_title());

			$post->set_create_time($pub_date, true);
			$post->topic()->recalculate();

			if($blog = bors_load_ex('balancer_board_blog', $post->id(), array('no_load_cache' => true)))
			{
				$blog->set_blogged_time($this->pub_date(), true);
				$blog->set_keywords(explode(',', $keywords), true);
				$blog->set_title($title, true);
				$blog->set_is_microblog($feed->is_microblog(), true);
			}
			else
			{
				echo " *** Error: Can't load blog {$post->id()}\n";
				bors_debug::syslog('feeds_translate', " *** Error: Can't load blog {$post->id()}");
			}
//			$blog->store();

			$blog->set_is_public($topic->is_public());
			$post->set_is_public($topic->is_public());

			return;
		}

		// Не были запощены. Ищем топик и форум.
		if(!$find_topic)
		{
			// Топик не ищем!
			if($feed->target_topic_id())
				$topic = bors_load('balancer_board_topic', $feed->target_topic_id());
			else
				$topic = bors_load('balancer_board_topic', 66326); // Флуд, флейм, кто что делает... / За жизнь
		}
		else
		{
			// Ищем подходящий.
			$topic = $this->find_topic();
		}

		$forum = $topic->forum();

		$post = bors_new('balancer_board_post', array(
			'topic_id' => $topic->id(),
			'owner_id' => $owner_id,
			'author_name' => $owner_name,
			'source' => $source,
			'create_time' => $this->pub_date(),
			'markup_class_name' => @$markup,
			'is_public' => $topic->is_public(),
		));

		$post->cache_clean();
		$post->store();
		$post->body();
		$post->store();

		$topic->set_modify_time(time(), true);
		$topic->cache_clean();
		$topic->store();

		$topic->recalculate();

		echo "\nnew post {$post->debug_title()}\n\t{$post->url_for_igo()}\n";

		$forum->update_num_topics();

		$blog = bors_new('balancer_board_blog', array(
			'id' => $post->id(),
			'keywords_string' => $keywords,
			'owner_id' => $owner_id,
			'topic_id' => $topic->id(),
			'forum_id' => $topic->forum_id(),
			'blogged_time' => $this->pub_date(),
			'is_public' => $topic->is_public(),
			'is_microblog' => $this->feed()->is_microblog(),
			'blog_source_class' => $this->class_name(),
			'blog_source_id'    => $this->id(),
		));

		echo "\tnew blog {$blog->debug_title()}\n";

		$this->set_target_class_name($post->class_name(), true);
		$this->set_target_object_id($post->id(), true);
		$this->store();
	}

	function find_topic()
	{
		$feed = $this->feed();

		if($feed->target_topic_id()) // Если прописан топик
		{
			if(!$feed->topics_auto_search()) // и не прописан автопоиск, то это у нас - фиксированный топик:
			{
				$topic_id = $feed->target_topic_id();
				$topic = object_load('balancer_board_topic', $topic_id);
				return $topic;
			}

			// В противном случае мы должны искать подходящий топик по ключевым словам.
			$kws = join(', ', $this->full_keywords_list());
			$topic_id = common_keyword::best_topic($kws, $feed->target_topic_id());
			$topic = object_load('balancer_board_topic', $topic_id);
			echo 'Found topic [def='.$feed->target_topic_id().'] for '.$kws." = {$topic->debug_title()}\n";
			return $topic;
//			bors_exit("\nend: $topic_id, {$topic->debug_title()}, {$topic->url()}\n");
		}

		// Топик не прописан. Ищем дальше

		if(!$forum_id = $feed->target_forum_id()) // Форум не указан. Ищем лучший.
			$forum_id = common_keyword::best_forum($this->keywords_string());

		return $this->make_topic($forum_id);
	}

	function full_keywords_list($tags = array())
	{
		$tags = array_merge($tags, explode(',', $this->keywords_string() .',' . $this->feed()->append_keywords()));

		sort($tags);
		$ftags = array();
		foreach($tags as $t)
			if($t = trim($t))
				$ftags[bors_lower($t)] = $t;

		$tags = array_values($ftags);
		sort($tags);

		return $tags;
	}

	function make_topic($forum_id)
	{
		$owner_id = $this->feed()->owner_id();
		$owner_name = $this->author_name() ? $this->author_name() : $this->feed()->owner_name();

		$topic = object_new_instance('balancer_board_topic', array(
			'forum_id' => $forum_id,
			'title'	=> $this->title() ? $this->title() : ec('Без названия'),
			'is_public' => true,
			'owner_id'=> $owner_id,
			'last_poster_name' => $owner_name,
			'author_name' => $owner_name,
			'keywords_string' => join(',', $this->full_keywords_list()),
			'create_time' => $this->pub_date(),
		));

		echo "\tnew topic {$topic->debug_title()}\n";

		return $topic;
	}

	function blog_title_info()
	{
		if(!($b = $this->blog()))
			return '';

		return $b->post()->titled_link_in_container();
	}

	function blog_pretend()
	{
		if($this->blog())
			return '+';

		$blog = bors_find_first('balancer_board_blog', array(
//			'blog_source_class' => 'bors_external_feeds_entry',
			'blog_source_id' => $this->id(),
			'order' => 'create_time',
		));

		if($blog)
			return '='.$blog->post()->titled_link_in_container();

		return '?';
	}

	function actions()
	{
		return join("<br/>\n", array(
			"<a href=\"?act=recalculate&target={$this->internal_uri_ascii()}\">пересчитать</a>",
		));
	}

	function item_list_admin_fields()
	{
		return array(
			'id',
			'text' => 'Сообщение',
			'blog_title_info' => 'Пост блога',
			'blog_pretend' => 'Кандидат',
			'actions' => 'Действия',
		);
	}
}
