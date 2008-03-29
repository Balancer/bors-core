<?php

class base_page_db_list extends base_page_db
{
	function data_provider() { return 'dataprovider_dblist'; }
	function can_be_empty() { return true; }

	function where() { return NULL; }
	function left_join()   { return array(); }
	function inner_join()  { return array(); }
	function id_field()	   { return 'id'; }
	function list_name()	{ return 'list'; }
	function limit()		{ return NULL; }
}
