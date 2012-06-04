<?php

class bors_external_feed extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function table_name() { return 'external_feeds'; }
	function table_fields()
	{
		return array(
			'id',
			'title',
			'feed_url',
			'charset' => array('title' => ec('Кодировка ленты')),
			'append_keywords',
			'post_as_topics',
			'topics_auto_search',
			'owner_name',
			'owner_id',
			'target_forum_id',
			'target_topic_id',
			'para_limit',
			'is_suspended',
			'is_microblog',
			'show_only_after_time' => 'UNIX_TIMESTAMP(`show_only_after_time`)',
			'id_extract_regexp',
			'skip_entry_content_regexp',
			'keywords_in_sqbr',
			'titles_in_posts',
			'is_washeable',
			'parser_class_name',
		);
	}

	function class_title() { return ec('лента'); }
	function _class_title_rp_def() { return ec('ленты'); }
	function class_title_vp() { return ec('ленту'); }
	function class_title_tpm() { return ec('лентами'); }
	function class_title_m() { return ec('ленты'); }

	function auto_objects()
	{
		return array_merge(parent::auto_objects(), array(
			'default_topic' => 'balancer_board_topic(target_topic_id)',
		));
	}

	static function _atom_extract($data)
	{
		$feed = array();
		$feed_data = $data['feed'][0];
		$feed['title'] = $feed_data['title'][0]['cdata'];
		$author_name = $feed_data['author'][0]['name'][0]['cdata'];
		foreach($feed_data['entry'] as $x)
		{
//			print_r($x);
			$feed['entries'][] = array(
				'title' => $x['title'][0]['cdata'],
				'link'  => $x['link'][0]['href'],
				'pub_date'  => strtotime($x['updated'][0]['cdata']),
				'id'	=> $x['id'][0]['cdata'],
				'description'	=> $x['summary'][0]['cdata'],
				'author_name' => $author_name,
			);
		}
//		print_d($feed)."\n";
//		print_d($feed_data)."\n";
		return $feed;
	}

	function update($is_test = false, $rss_reread = false)
	{
		$xml = bors_lib_http::get_ex($this->feed_url(), array('charset' => $this->charset()));

		$xml = $xml['content'];

//		if($is_test)
//			echo "xml = ".var_dump($xml)."\n";

		$data = bors_lib_xml::xml2array($xml);
		if(!empty($data['feed'])) // Это ATOM
		{
			$feed = self::_atom_extract($data);

			if(!$this->title_true() && $feed['title'])
				$this->set_title($feed['title']);

			foreach($feed['entries'] as $entry)
				self::_check_entry($entry, $is_test);
//	var_dump($dntries);
			return;
		}

		$rss = @$data['rss'][0];
		if(!$rss)
		{
			echo "RSS {$this->feed_url()} not found\n";

			if($xml)
			{
//				print_d($xml)."\n";
//				print_d($data)."\n";
			}

			if(!$is_test)
				debug_hidden_log('rss_error', "Can't get rss {$this->feed_url()}");

			return;
		}

		$channel = $rss['channel'][0];

		$items = array_reverse($channel['item']);
//		if($is_test) { print_dd($items); exit(); }

		foreach($items as $item)
		{
			$title = @$item['title'][0]['cdata']; // html_entity_decode($item['title'][0]['cdata'], ENT_QUOTES, 'UTF-8');
//			echo "Check $title\n";
			$description = $item['description'][0]['cdata']; // html_entity_decode($item['description'][0]['cdata'], ENT_QUOTES, 'UTF-8');
			$link = $item['link'][0]['cdata'];
			$guid = @$item['guid'][0]['cdata'];
			$pub_date = $item['pubDate'][0]['cdata'];
			if(preg_match('/ UT$/', $pub_date)) // глючный формат 'Tue, 20 Jul 2010 18:26:41 UT'
				$pub_date .= 'C';

			$pub_date = strtotime($pub_date);
//			echo "if($pub_date && {$this->show_only_after_time()} && $pub_date < {$this->show_only_after_time()})\n";
			if($pub_date && $this->show_only_after_time() && $pub_date < $this->show_only_after_time())
				continue;

			$author_name = @$item['author'][0]['cdata'];
			if(!$author_name)
				$author_name = @$item['lj:poster'][0]['cdata'];

			if(empty($guid))
				$guid = $link;

			if(preg_match('!http://www.aviaport.ru/news/\d{4}/\d{2}/\d{2}/(\d+).html!', $link, $m))
				$feed_entry_id = $m[1];
			else
				$feed_entry_id = $guid;

			$guid = bors_substr($guid, 0, 255);

			$entry = bors_find_first('bors_external_feeds_entry', array(
				'entry_url' => $guid,
			));

			if(!$entry && $link)
				$entry = bors_find_first('bors_external_feeds_entry', array(
					'entry_url' => $link,
				));

			$is_skipped = $this->skip_entry_content_regexp()
				&& preg_match('!'.$this->skip_entry_content_regexp().'!i', $description);

			$tags = array();

			if($kws = @$item['media:group'][0]['media:keywords'][0]['cdata'])
				$tags = array_map('trim', explode(',', $kws));
			elseif(!empty($item['category']))
			{
				foreach($item['category'] as $cat)
				{
					$tag = trim(str_replace('_', ' ', $cat['cdata']));
					if(preg_match('!http://\S+!', $tag))
						continue;

					foreach(explode(',', $tag) as $t)
						$tags[] = trim($t);
				}
			}
			else
			{
				//TODO: пока жёсткий харкод. Нужно будет придумать общие настройки
				if(stripos($title, 'Имхонет') !== false && strpos($description, 'Фильм:') !== false)
				{
					$tags[] = 'кино';
					$tags[] = 'фильм';
				}

				if(preg_match('/Отзыв о фильме "(.+)" на Имхонет/', $title, $m))
					$tags[] = $m[1];

				if(preg_match('/Отзыв о книге "(.+)" на Имхонет/', $title, $m))
				{
					$tags[] = $m[1];
					$tags[] = 'литература';
				}

				if($this->keywords_in_sqbr()) // Тэги в квадратных скобках
				{
					if(preg_match_all('/\[(.+?)\]/', $title, $matches))
					{
						foreach($matches[1] as $m)
							$tags[] = $m;

						$title = preg_replace('/^(\[[^]]+?\])+\s*/', '', $title);
					}
				}
			}

			$keywords_string = join(', ', $tags);

			if($entry
					&& $pub_date <= $entry->pub_date()
					&& bors_substr($title, 0, 255) == bors_substr($entry->title(), 0, 255)
					&& $description == $entry->text()
					&& $keywords_string == $entry->keywords_string()
			)
				continue;

			if($entry)
				debug_hidden_log('__keywords_delete', "check 1: why not skipped? entry={$entry->id()}; entry=".((bool)$entry)
					." && pubdate <= :".($pub_date <= $entry->pub_date())
					." && title==:".($title == $entry->title())
					." && desc==:".($description == $entry->text())
					." && kws==:".($keywords_string == $entry->keywords_string()));

			echo "=== $title ===\n";

//			echo "=== $title ===\ntags: ".join(', ', $tags)."\n// $link\n".$forum->debug_title()."\n\n";
//	echo "=== $title ===\n$description\n// $link\n".$forum->debug_title()."\n\n";

//			echo "=== $title ({$entry->title()}), $pub_date / {$entry->pub_date()} ===\n";

			if($is_test)
			{
				if($is_skipped)
				{
					echo "kws = ".$keywords_string."\n";
					echo "\t>>>suspended'\n\n";
				}
				elseif($keywords_string)
				{
					echo "keywords string = '$keywords_string'\n";
					$topic_id = common_keyword::best_topic($keywords_string, $this->target_topic_id(), true);
					$topic = bors_load('balancer_board_topic', $topic_id);
					echo 'Found topic [def='.$this->target_topic_id()."]: {$topic->debug_title()}, w={$GLOBALS['__debug_last_topic_weight']}\n\n";
				}
				continue;
			}

			$find_topic = !!$tags;

			$tags = array_merge(array_map('trim', explode(',', $this->append_keywords())), $tags);

			if($entry)
			{
				$entry->set_pub_date(max($entry->pub_date(), $pub_date), true);
				$entry->set_title($title, true);
				$entry->set_author_name($author_name, true);
				$entry->set_keywords_string($keywords_string, true);
				$entry->set_text($description, true);
				$entry->set_is_suspended($is_skipped, true);
				$was = 'updated';
			}
			else
			{
				$entry = object_new_instance('bors_external_feeds_entry', array(
					'entry_url' => $link,
					'pub_date' => time(), // $pub_date,
					'title' => $title,
					'keywords_string' => $keywords_string,
					'text' => $description,
					'author_name' => $author_name,
					'feed_id' => $this->id(),
					'entry_id' => $feed_entry_id,
//					'target_class_name',
//					'target_object_id',
					'is_suspended' => $is_skipped,
				));
				$was = 'new';
			}

//			if(!$entry->target_object_id() && $this->target_topic_id())

			if(!$is_skipped && !$is_test)
			{

				if($entry)
					debug_hidden_log('__keywords_delete', "why not skipped? entry={$entry->debug_title()}; was=$was; entry=".((bool)$entry)
						." && pubdate <= :".($pub_date <= $entry->pub_date())
						." && title==:".($title == $entry->title())
						." && desc==:".($description == $entry->text())
						." && kws==:".($keywords_string == $entry->keywords_string()));

				$entry->update_target(true, $find_topic);
			}

//			echo "update_target($forum_id, {$this->target_topic_id()});\n";
//			if(!$is_skipped)
//				return;
		} // endforeach $items
	}

	private function _check_entry($entry_data, $is_test = false)
	{
		$title = popval($entry_data, 'title');
		$link  = popval($entry_data, 'link');
		$pub_date = popval($entry_data, 'pub_date');
		$id = popval($entry_data, 'id');
		$description = popval($entry_data, 'description');
		$author_name = popval($entry_data, 'author_name');

		if($this->show_only_after_time() && $pub_date < $this->show_only_after_time())
			return;

		if(!$id)
			$id = $link;

		$entry = bors_find_first('bors_external_feeds_entry', array(
			'entry_url' => $id,
		));

		if(!$entry && $link)
			$entry = bors_find_first('bors_external_feeds_entry', array(
				'entry_url' => $link,
			));

//		echo "check entry";
//		print_r($entry_data);
//		exit();

		$is_skipped = $this->skip_entry_content_regexp()
			&& preg_match('!'.$this->skip_entry_content_regexp().'!i', $description);

		$tags = popval($entry_data, 'tags', array());
		$entry_title = $title;

		if($parser_class_name = $this->parser_class_name())
		{
			$parser = new $parser_class_name(NULL);
			$data = $parser->parse(array(
				'title' => $title,
				'text' => $description,
				'link' => $link,
				'tags' => $tags,
			));

			$tags	= defval($data, 'tags', $tags);
			$title	= defval($data, 'title', $title);

//			var_dump($data);
		}

		$keywords_string = join(', ', $tags);

		if($entry
				&& $pub_date <= $entry->pub_date()
				&& $title == $entry->title()
				&& $description == $entry->text()
				&& $keywords_string == $entry->keywords_string()
		)
			return;

		echo "=== $entry_title ===\n";

//			echo "=== $title ===\ntags: ".join(', ', $tags)."\n// $link\n".$forum->debug_title()."\n\n";
//	echo "=== $title ===\n$description\n// $link\n".$forum->debug_title()."\n\n";
//			echo "=== $title ({$entry->title()}), $pub_date / {$entry->pub_date()} ===\n";

		if($is_test)
		{
			if($is_skipped)
			{
				echo "kws = ".$keywords_string."\n";
				echo "\t>>>suspended'\n\n";
			}
			elseif($keywords_string)
			{
				echo "keywords string = '$keywords_string'\n";
				$topic_id = common_keyword::best_topic($keywords_string, $this->target_topic_id(), true);
				$topic = bors_load('balancer_board_topic', $topic_id);
				echo 'Found topic [def='.$this->target_topic_id()."]: {$topic->debug_title()}, w={$GLOBALS['__debug_last_topic_weight']}\n\n";
			}

			return;
		}

		$find_topic = !!$tags;

		$tags = array_merge(array_map('trim', explode(',', $this->append_keywords())), $tags);

		if($entry) // Были изменения, обновляем запись
		{
			$entry->set_pub_date(max($entry->pub_date(), $pub_date), true);
			$entry->set_title($title, true);
			$entry->set_author_name($author_name, true);
			$entry->set_keywords_string($keywords_string, true);
			$entry->set_text($description, true);
			$entry->set_is_suspended($is_skipped, true);
		}
		else // Создаём новую запись
		{
			$entry = bors_new('bors_external_feeds_entry', array(
				'entry_url' => $link,
				'pub_date' => $pub_date,
				'title' => $title,
				'keywords_string' => $keywords_string,
				'text' => $description,
				'author_name' => $author_name,
				'feed_id' => $this->id(),
				'entry_id' => $id,
//				'target_class_name',
//				'target_object_id',
				'is_suspended' => $is_skipped,
			));
		}

//			if(!$entry->target_object_id() && $this->target_topic_id())

		if(!$is_skipped && !$is_test)
			$entry->update_target(true, $find_topic);
//			echo "update_target($forum_id, {$this->target_topic_id()});\n";
	}
}
