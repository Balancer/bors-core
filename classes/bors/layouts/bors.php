<?php

class bors_layouts_bors extends bors_layouts_html
{
	function table_class() { return $this->object()->get('use_bootstrap') ? 'table table-bordered table-hover' : 'btab w100p'; }
	function ul_tab_class() { return 'pages-tabs'; }
}
