<?php

class common_redirect extends base_object
{
	function pre_parse()
	{
		return go($this->id());
	}
}
