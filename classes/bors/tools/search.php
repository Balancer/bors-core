<?php

class bors_tools_search extends base_page
{
	function class_file() { return __FILE__; } // не удалять, шаблон в субклассах.
	function template() { templates_noindex(); return 'forum/_header.html'; }
	
	function parents() { return array('/tools/', '/forum/'); }
	
	function title() { return ec('Поиск по форуму'); }
	function nav_name() { return ec('поиск'); }
	function total_items() { return 0; }
	function q() { return ''; }
	function f() { return array(); }
	function s() { return 'r'; }
	function x() { return false; }
	function u() { return ''; }

	function access() { return $this; }
	function can_action() { return true; }
	function can_read() { return true; }
}

