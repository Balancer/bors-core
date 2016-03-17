<?php

class bors_templates_mdtpl extends bors_templates_smarty3
{
	static function fetch($template, $data = array(), $instance=NULL)
	{
		$text = parent::fetch($template, $data);
		$md = bors_markup_markdown::factory($text);

		return $md->html();
	}
}
