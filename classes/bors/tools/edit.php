<?php

class bors_tools_edit extends base_page
{
	function title() { return ec('Редактор'); }
	function object() { return object_load($this->id()); }
}
