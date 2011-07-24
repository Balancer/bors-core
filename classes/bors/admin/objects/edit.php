<?php

class bors_admin_objects_edit extends bors_admin_meta_edit
{
	function main_class() { return $this->page(); }
	function main_admin_class() { return $this->page(); }

//	function config_class() { return 'aviaport_admin_directory_airlines_config'; }
//	function main_admin_class() { return 'aviaport_admin_airline'; }
}
