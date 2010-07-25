<?php

class bors_external_feeds_entry extends base_object_db
{
	function table_name() { return 'external_feeds_entries'; }

	function main_table_fields()
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

	function auto_objects()
	{
		return array('feed' => 'bors_external_feed(feed_id)');
	}

	function auto_targets()
	{
		return array('target' => 'target_class_name(target_object_id)');
	}

	function make_source()
	{
		$link = $this->entry_url();
		$text = html2bb($this->text(), $link);
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
//		if($this->feed()->post_as_topics())
//		return "[b]{$this->title()}[/b]\n\n$text";
		return $text;
	}

	function update_target()
	{
		$feed = $this->feed();

		if($feed->target_topic_id())
		{
			$topic_id = $feed->target_topic_id();
			$topic = object_load('balancer_board_topic', $topic_id);
			$forum_id = $topic->forum_id();
		}
		else
		{
			$forum_id = $feed->target_forum_id();
			if(!$forum_id)
			{
				$forum_id = common_keyword::best_forum($this->keywords_string());
				$forum = object_load('balancer_board_forum', $forum_id);
				echo "Best forum for '{$this->keywords_string()}' is {$forum->debug_title()}\n";
//				bors_exit();
			}
		}

		$tags = explode(',', $this->keywords_string() .',' . $feed->append_keywords());
		sort($tags);
		$ftags = array();
		foreach($tags as $t)
			if($t = trim($t))
				$ftags[bors_lower($t)] = $t;

		$tags = array_values($ftags);
		sort($tags);

		$keywords = join(', ', $tags);

		$forum = object_load('balancer_board_forum', $forum_id);

		$owner_id = $feed->owner_id();
		$owner_name = $this->author_name() ? $this->author_name() : $feed->owner_name();

		echo "update entry {$this->title()} [{$this->keywords_string()}]\n";
				$forum->update_num_topics();
		if($this->target_object_id())
		{
			$target = $this->target();
			$target->set_owner_id($owner_id, true);
			$target->set_author_name($owner_name, true);
			$target->set_source($this->make_source(), true);
			$target->set_source_html(NULL, true);
			$target->set_create_time($this->pub_date(), true);

			if(!$feed->target_topic_id())
			{
				$topic = $target->topic();
				$topic->set_title($this->title(), true);
				$topic->set_owner_id($owner_id, true);
				$topic->set_author_name($owner_name, true);
				$topic->recalculate();
			}

			echo "target = ".$target->debug_title()." ({$target->url()})\n";

			$blog = object_load('balancer_board_blog', $target->id());
			$blog->set_blogged_time($this->pub_date(), true);
		}
		else
		{
//			echo "feed->post_as_topics = ".$feed->post_as_topics()."\n";
			if($feed->post_as_topics())
			{
				$topic = object_new_instance('balancer_board_topic', array(
					'forum_id' => $forum_id,
					'title'	=> $this->title() ? $this->title() : ec('Без названия'),
					'is_public' => true,
					'owner_id'=> $owner_id,
					'last_poster_name' => $owner_name,
					'author_name' => $owner_name,
					'keywords_string' => $keywords,
					'create_time' => $this->pub_date(),
				));

				$forum->update_num_topics();
			}
			else
				$topic = object_load('balancer_board_topic', $topic_id);

//			echo "topic = ".$topic->debug_title()."\n";

			$post = object_new_instance('balancer_board_post', array(
				'topic_id' => $topic->id(),
				'owner_id' => $owner_id,
				'author_name' => $owner_name,
				'source' => $this->make_source(),
				'create_time' => $this->pub_date(),
			));

			echo "post = ".$post->debug_title()." ({$post->url()})\n";

			$topic->recalculate();

			$blog = object_new_instance('balancer_board_blog', array(
				'id' => $post->id(),
				'keywords_string' => $keywords,
				'owner_id' => $owner_id,
				'forum_id' => $topic->forum_id(),
				'blogged_time' => $this->pub_date(),
				'is_public' => true,
			));

			echo "blog = ".$blog->debug_title()."\n";

			$this->set_target_class_name($post->class_name(), true);
			$this->set_target_object_id($post->id(), true);
		}
	}
}
