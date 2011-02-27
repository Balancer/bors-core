<?php

class bors_external_feeds_entry extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }

	function table_name() { return 'external_feeds_entries'; }
	function table_fields()
	{
		return array(
			'id',
			'entry_url',
			'pub_date',
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
		return array('feed' => 'bors_external_feed(feed_id)');
	}

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'target' => 'target_class_name(target_object_id)',
		));
	}

	function make_source()
	{
		$link = $this->entry_url();
		$text = $this->text();

		if($this->feed_id() == 2) // Juick
		{
			// Разворачиваем в BB-код тэги picasaweb с Juick'а
			$text = preg_replace('!<a href="https?://picasaweb.google.\w+.+photo/(.+?)\?feat=directlink.*?</a>!', "\n[picasa notitle]\\1[/picasa]\n", $text);
			// Разворачиваем в BB-код картинки
			// <a href="http://img200.imageshack.us/img200/2530/screenshotdl.png" rel="nofollow">img200.imageshack.us</a>
			$text = preg_replace('!<a href="([^"]+?\.(jpe?g|png|gif))" rel="nofollow">[^\s\<]+</a>!i', "[img $1]", $text);
//			exit("$text");
			$text = preg_replace('!<a href="http://www.youtube.\w+/watch\?v=([^"]+)" rel="nofollow">youtube.com</a>!', '[youtube]$1[/youtube]', $text);
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
			$text[] = "\n// Транслировано с [url]{$link}[/url]\n";

		$text = str_replace("/\n{3,}/", "\n\n", join("\n", $text));
		if($this->feed()->titles_in_posts())
			$text = "[b]{$this->title()}[/b]\n\n$text";

		return $text;
	}

	function recalculate() { $this->update_target(true); }

	function update_target($update_lcml = false)
	{
		$feed = $this->feed();

		if($parser_class_name = $feed->parser_class_name())
		{
//			exit($parser_class_name);
			$parser = new $parser_class_name(NULL);
			$data = $parser->parse(array(
				'text' => $this->text(),
			));

			$source	= $data['bb_code'];
			$tags	= $data['tags'];
			$title	= $data['title'];
			$source .= "\n// Транслировано с {$this->entry_url()}\n";
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
			$post = $this->target();
			if(!$post)
				return;

			$post->set_owner_id($owner_id, true);
			$post->set_author_name($owner_name, true);
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

				$topic = $post->topic();
				$topic->cache_clean();
				$topic->set_modify_time(time(), true);
				$topic->store();

//				$blog = bors_load('balancer_board_blog', $post->id());

				$post->body();
			}

			$post->set_create_time($this->pub_date(), true);
			$post->topic()->recalculate();

			$blog = bors_load_ex('balancer_board_blog', $post->id(), array('no_load_cache' => true));
			$blog->set_blogged_time($this->pub_date(), true);
			$blog->set_keywords(explode(',', $keywords), true);
			$blog->set_title($title, true);
//			$blog->store();

			return;
		}

		// Не были запощены. Ищем топик и форум.

		$topic = $this->find_topic();
		$forum = $topic->forum();

		$post = object_new_instance('balancer_board_post', array(
			'topic_id' => $topic->id(),
			'owner_id' => $owner_id,
			'author_name' => $owner_name,
			'source' => $source,
			'create_time' => $this->pub_date(),
		));

		$topic->recalculate();

		echo "\tnew post {$post->debug_title()}\n";

		$forum->update_num_topics();

		$blog = object_new_instance('balancer_board_blog', array(
			'id' => $post->id(),
			'keywords_string' => $keywords,
			'owner_id' => $owner_id,
			'topic_id' => $topic->id(),
			'forum_id' => $topic->forum_id(),
			'blogged_time' => $this->pub_date(),
			'is_public' => true,
		));

		echo "\tnew blog {$blog->debug_title()}\n";

		$this->set_target_class_name($post->class_name(), true);
		$this->set_target_object_id($post->id(), true);
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
}
