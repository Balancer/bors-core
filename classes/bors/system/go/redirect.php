<?php

class bors_system_go_redirect extends bors_page
{
	function title() { return object_property($this->object(), 'title'); }

	function pre_show()
	{
		if($object = $this->object())
		{
			if(bors()->user() && $object->class_name() == 'balancer_board_post')
			{
				$this->set_attr('direct_url',   $object->url_in_container());
				$this->set_attr('direct_title', $object->title());

				$unvisited = $object->topic()->find_first_unvisited_post(bors()->user());
				if($unvisited && $unvisited->create_time() < $object->create_time())
				{
					$this->set_attr('old_url',   $unvisited->url_in_container());
					$this->set_attr('old_title', $unvisited->title());
				}


				if($object->owner_id() == bors()->user_id()
					&& ($answer = $object->answer_to())
					&& $answer->page() != $object->page()
					)
				{
					$this->set_attr('reply_url',   $answer->url_in_container());
					$this->set_attr('reply_title', $answer->title());
				}

				if($this->get('old_url') || $this->get('reply_url'))
					return parent::pre_show();
			}

			if(method_exists($object, 'url_in_topic'))
				return go($object->url_in_topic(NULL, true), true);
			else
				return go($object->url_in_container(), true);
		}

		return false; // bors_message("Can't find object {$this->id()}");
	}

	function parents()
	{
		return $this->object()->parents();
	}

	function object()
	{
		$object = NULL;

		if(preg_match('/^(\w)(\d+)$/', $this->id(), $m))
		{
			switch($m[1])
			{
				case 'p':
					$object = bors_load('balancer_board_post', $m[2]);
					break;
				case 't':
					$object = bors_load('balancer_board_topic', $m[2]);
					break;
			}
		}

		return $object;
	}
}
