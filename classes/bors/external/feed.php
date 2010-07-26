<?php

class bors_external_feed extends base_object_db
{
	function table_name() { return 'external_feeds'; }

	function main_table_fields()
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
			'id_extract_regexp',
			'skip_entry_content_regexp',
			'titles_in_posts',
			'is_washeable',
		);
	}

	function update()
	{
		$xml = bors_lib_http::get($this->feed_url());
		$data = bors_lib_xml::xml2array($xml);
		$rss = $data['rss'][0];
		$channel = $rss['channel'][0];

		$items = $channel['item'];

		foreach($items as $item)
		{
			$title = @$item['title'][0]['cdata']; // html_entity_decode($item['title'][0]['cdata'], ENT_QUOTES, 'UTF-8');
			$description = $item['description'][0]['cdata']; // html_entity_decode($item['description'][0]['cdata'], ENT_QUOTES, 'UTF-8');
			$link = $item['link'][0]['cdata'];
			$guid = @$item['guid'][0]['cdata'];
			$pub_date = $item['pubDate'][0]['cdata'];
			if(preg_match('/ UT$/', $pub_date)) // глючный формат 'Tue, 20 Jul 2010 18:26:41 UT'
				$pub_date .= 'C';

			$pub_date = strtotime($pub_date);

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

			$entry = objects_first('bors_external_feeds_entry', array(
				'feed_id' => $this->id(),
				'entry_id' => $feed_entry_id,
			));

//			echo date('r', $pub_date)."\n";

			$is_suspended = $this->skip_entry_content_regexp() && preg_match('!'.$this->skip_entry_content_regexp().'!', $description);

			if($entry && $pub_date <= $entry->pub_date())
				continue;

			echo "=== $title ===\n";
			$tags = array(); // explode(',', $this->append_keywords());

			if(!empty($item['category']))
			{
				foreach($item['category'] as $cat)
				{
					$tag = trim(str_replace('_', ' ', $cat['cdata']));

					foreach(explode(',', $tag) as $t)
						$tags[] = $t;
				}
			}

			$keywords_string = join(', ', $tags);

//			echo "=== $title ===\ntags: ".join(', ', $tags)."\n// $link\n".$forum->debug_title()."\n\n";
//	echo "=== $title ===\n$description\n// $link\n".$forum->debug_title()."\n\n";

			if($entry)
			{
				$entry->set_pub_date($pub_date, true);
				$entry->set_title($title, true);
				$entry->set_author_name($author_name, true);
				$entry->set_keywords_string($keywords_string, true);
				$entry->set_text($description, true);
				$entry->set_is_suspended($is_suspended, true);
			}
			else
			{
				$entry = object_new_instance('bors_external_feeds_entry', array(
					'entry_url' => $link,
					'pub_date' => $pub_date,
					'title' => $title,
					'keywords_string' => $keywords_string,
					'text' => $description,
					'author_name' => $author_name,
					'feed_id' => $this->id(),
					'entry_id' => $feed_entry_id,
//					'target_class_name',
//					'target_object_id',
					'is_suspended' => $is_suspended,
				));
			}

//			if(!$entry->target_object_id() && $this->target_topic_id())

			if(!$is_suspended)
				$entry->update_target();
//			echo "update_target($forum_id, {$this->target_topic_id()});\n";
//			if(!$is_suspended)
//				return;
		} // endforeach $items
	}
}
