<?php

class bors_external_feed extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function table_name() { return 'external_feeds'; }
	function table_fields()
	{
		return array(
			'id',
			'feed_url',
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

	function update($is_test = false)
	{
		$xml = bors_lib_http::get($this->feed_url());
//		if($is_test)
//			echo "xml = $xml\n";
		$data = bors_lib_xml::xml2array($xml);
		$rss = @$data['rss'][0];
		if(!$rss)
		{
			echo "RSS {$this->feed_url()} not found\n";
			if($xml)
			{
				print_d($xml)."\n";
				print_d($data)."\n";
			}
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
//			echo "author name = $author_name\n";

			if(empty($guid))
				$guid = $link;

			if(preg_match('!http://www.aviaport.ru/news/\d{4}/\d{2}/\d{2}/(\d+).html!', $link, $m))
				$feed_entry_id = $m[1];
			else
				$feed_entry_id = $guid;

			$entry = bors_find_first('bors_external_feeds_entry', array(
				'entry_url' => $guid,
			));

			if(!$entry && $link)
				$entry = bors_find_first('bors_external_feeds_entry', array(
					'entry_url' => $link,
				));

//			echo date('r', $pub_date)."\n";

			$is_skipped = $this->skip_entry_content_regexp() && preg_match('!'.$this->skip_entry_content_regexp().'!i', $description);

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
					&& $title == $entry->title()
					&& $description == $entry->text()
					&& $keywords_string == $entry->keywords_string()
			)
				continue;

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
			}

//			if(!$entry->target_object_id() && $this->target_topic_id())

			if(!$is_skipped && !$is_test)
				$entry->update_target(true, $find_topic);
//			echo "update_target($forum_id, {$this->target_topic_id()});\n";
//			if(!$is_skipped)
//				return;
		} // endforeach $items
	}
}
