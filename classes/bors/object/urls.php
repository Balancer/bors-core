<?php

// Создание всевозможных url'ов

class bors_object_urls extends base_empty
{
	function object() { return $this->id(); }

	function share_twit()
	{
		return 'http://twitter.com/home?status='.urlencode(bors_external_twitter::linkify($this->object()));
	}

	function share_facebook()
	{
		$x = $this->object();
		return 'http://www.facebook.com/sharer.php?u='.urlencode($x->url())
			.'&t='.urlencode($x->title());
	}

	function go($go)
	{
		$go = str_replace('%OBJECT_ID%', $this->object()->id(), $go);

		if($go == "newpage_admin")
			return $this->object()->admin_url(1);

		return $go;
	}
}
