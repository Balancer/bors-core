<?php

class base_jss extends base_page
{
	function pre_show()
	{
		header("Content-type", "text/javascript");
		return $this->cacheable_body();
	}
}
