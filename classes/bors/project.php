<?php

class bors_project extends bors_object
{
	function _title_def() { return config('project.title'); }
	function _nav_name_def() { return config('project.nav_name'); }
	function url($page=NULL) { return '/'; }
}
