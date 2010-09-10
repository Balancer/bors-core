<?php

class bors_list extends base_list
{
	static function item($class_name, $id) { return object_load($class_name, $id)->title(); }
}
