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
			'feed_url' => array('title' => ec('URL фида')),
			'charset' => array('title' => ec('Кодировка ленты')),
			'append_keywords' => array('title' => ec('Добавляемые ключевые слова')),
			'post_as_topics',
			'topics_auto_search',
			'owner_name',
			'owner_id',
			'target_forum_id',
			'target_topic_id',
			'para_limit',
			'is_suspended',
			'is_microblog',
			'show_only_after_time' => array(
				'name' => 'UNIX_TIMESTAMP(`show_only_after_time`)',
				'title' => ec('Размещать только появившиеся после этого времени'),
				'type' => 'input_date',
				'can_drop' => true,
			),
			'id_extract_regexp',
			'skip_entry_content_regexp',
			'keywords_in_sqbr',
			'titles_in_posts',
			'is_washeable',
			'parser_class_name',
		);
	}

	function _class_title_def() { return ec('лента'); }
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

	function update($is_test = false, $rss_reread = false, $force_update = false)
	{
		require_once('/var/www/bors/composer/vendor/autoload.php');

		$feed = new SimplePie();
		$feed->set_feed_url($this->feed_url());
		$feed->enable_cache(false);
		$feed->init();

		foreach($feed->get_items() as $item)
		{
			$title = $item->get_title();
			$description = html_entity_decode($item->get_description());
			$link = $item->get_permalink();
			$pub_date = $item->get_date('U');
			if($pub_date && $this->show_only_after_time() && $pub_date < $this->show_only_after_time())
				continue;

			$author_name = ($a = $item->get_author()) ? $a->get_name() : NULL;

			if(!$author_name)
				$author_name = ($a = $feed->get_author()) ? $a->get_name() : NULL;

			if(!$author_name)
				$author_name = $this->owner_name();

			if(preg_match('/^\S+@\S+ \((.+)\)$/', $author_name, $m))
				$author_name = $m[1];

			$guid = bors_substr($link, 0, 255);

			$entry = bors_find_first('bors_external_feeds_entry', array(
				'entry_url' => $guid,
			));

			if(!$entry && $link)
				$entry = bors_find_first('bors_external_feeds_entry', array(
					'entry_url' => $link,
				));

			$raw_data = serialize($item->data);

			$is_skipped = $this->skip_entry_content_regexp()
				&& preg_match('!'.$this->skip_entry_content_regexp().'!i', $description);

			$tags = array();

//			if($kws = @$item['media:group'][0]['media:keywords'][0]['cdata'])
//				$tags = array_map('trim', explode(',', $kws));
			if($cats = $item->get_categories())
			{
				foreach($cats as $cat)
				{
					$tag = trim(str_replace('_', ' ', $cat->get_label()));
					if(preg_match('!http://\S+!', $tag))
						continue;

					foreach(explode(',', $tag) as $t)
						$tags[] = trim($t);
				}
			}

			if(0)
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

				if($this->keywords_in_sqbr()) // Теги в квадратных скобках
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
					&& !$force_update
					&& $pub_date <= $entry->pub_date()
					&& $author_name == $entry->author_name()
					&& bors_substr($title, 0, 255) == bors_substr($entry->title(), 0, 255)
					&& $description == $entry->text()
					&& $keywords_string == $entry->keywords_string()
			)
				continue;
/*
			if($entry)
				debug_hidden_log('__rss_update', "check 1: why not skipped? entry={$entry->id()}; entry=".((bool)$entry)
					." && pubdate <= :".($pub_date <= $entry->pub_date())
					." && title==:".($title == $entry->title())
					." && desc==:".($description == $entry->text())
					." && kws==:".($keywords_string == $entry->keywords_string()));
*/
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

//			echo "Entry: $entry\n";
			if($entry)
			{
				$entry->set_pub_date(max($entry->pub_date(), $pub_date), true);
				$entry->set_title($title, true);
				if($raw_data)
					$entry->set_simplepie_item_raw($raw_data, true);
				$entry->set_author_name($author_name, true);
				$entry->set_keywords_string($keywords_string, true);
				$entry->set_text($description, true);
//				print_dd($description);
				$entry->set_is_suspended($is_skipped, true);
				if(@$topic)
					$entry->set_is_public($topic->is_public());
				$was = 'updated';
			}
			else
			{
				$entry = bors_new('bors_external_feeds_entry', array(
					'entry_url' => $link,
					'pub_date' => time(), // $pub_date,
					'simplepie_item_raw' => $raw_data,
					'title' => $title,
					'keywords_string' => $keywords_string,
					'text' => $description,
					'author_name' => $author_name,
					'feed_id' => $this->id(),
					'entry_id' => $guid,
//					'target_class_name',
//					'target_object_id',
					'is_suspended' => $is_skipped,
					'is_public' => object_property(@$topic, 'is_public'),
				));

				$was = 'new';
			}

//			if(!$entry->target_object_id() && $this->target_topic_id())

			$entry->store();

			if(!$is_skipped && !$is_test)
			{
/*
				if($entry)
					debug_hidden_log('__rss-update2', "why not skipped? entry={$entry->debug_title()}; was=$was; entry=".((bool)$entry)
						." && pubdate <= :".($pub_date <= $entry->pub_date())
						." && title==:".($title == $entry->title())
						." && desc==:".($description == $entry->text())
						." && kws==:".($keywords_string == $entry->keywords_string()));
*/
				$entry->update_target(true, $find_topic);
			}

			bors()->changed_save();

//			echo "update_target($forum_id, {$this->target_topic_id()});\n";
//			if(!$is_skipped)
//				return;

//			break;
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
