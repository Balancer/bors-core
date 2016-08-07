<?php

class bors_templaters_markdown
{
	static function fetch($template, $data)
	{
		$md = bors_markup_markdown::factory(file_get_contents($template));

		return $md->html();
	}
}
