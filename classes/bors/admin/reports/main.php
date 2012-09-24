<?php

class bors_admin_reports_main extends bors_admin_page_bb
{
	function title() { return ec('Отчёты'); }
	function config_class() { return config('admin_config_class'); }
}
