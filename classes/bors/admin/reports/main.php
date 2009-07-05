<?php

class bors_admin_reports_main extends base_page_bb
{
	function title() { return ec('Отчёты'); }
	function config_class() { return config('admin_config_class'); }
}
