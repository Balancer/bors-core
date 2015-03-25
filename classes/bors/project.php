<?php

class bors_project extends bors_object
{
	function _title_def() { return config('project.title'); }
	function _nav_name_def() { return config('project.nav_name'); }
	function _url_def() { return '/'; }

	function _class_prefix_def() { return bors_core_object_defaults::project_name($this); }

	// Файлы проектов грузятся раньше конфигурации объектов и потому сами не конфигурируются.
	// Иначе получается бесконечная рекурсия.
	function _configure() { }
	function object_data() { return array(); }
	function config_class() { return NULL; }
	function data_load() { return false; }
}
