<?php

class bors_votes_thumb extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }

	function table_name() { return 'bors_thumb_votes'; }
	function table_fields()
	{
		return array(
			'id',
			'user_id',
			'target_class_name',
			'target_class_id',
			'target_object_id',
			'target_user_id',
			'score',
			'create_time',
		);
	}

	function replace_on_new_instance() { return true; }

	function object() { return $this->target(); }
	function target() { return $this->__havec('target') ? $this->__lastc() : $this->__setc(object_load($this->target_class_name(), $this->target_object_id())); }

	function auto_objects()
	{
		return array_merge(parent::auto_objects(), array(
			'owner' => 'forum_user(user_id)',
			'target_user' => 'balancer_board_user(target_user_id)',
		));
	}

	function score_html()
	{
		if($this->score() > 0)
			return "<span style=\"color:green\">+".intval($this->score())."</span>";
		else
			return "<span style=\"color:red\">".$this->score()."</span>";
	}

	static function colorize_html($score)
	{
		if($score > 0)
			return "<span style=\"color:green\">+".intval($score)."</span>";
		else
			return "<span style=\"color:red\">".$score."</span>";
	}

	static function colorize_pm($plus, $minus)
	{
		if(!$plus && !$minus)
			return '';

		$result = array();
		if($plus)
			$result[] = "<span style=\"color:green\">+".intval($plus)."</span>";
		if($plus && $minus)
			$result[] = ' / ';
		if($minus)
			$result[] = "<span style=\"color:red\">".intval($minus)."</span>";

		return join('', $result);
	}

	function item_list_fields()
	{
		return array(
			'target_user' => 'Кому',
			'ctime' => 'Когда',
			'score_html' => 'Оценка',
		);
	}
}
