<?php

/**
	Класс загрузки внешних JSON-объектов
*/

class bors_storage_rpc_json extends bors_storage
{
	var $rpc_project;
	var $rpc_class_name;
	var $rpc_id;

	function load($object)
	{
		$url_data = parse_url($object->id());
		var_dump($url_data);
		if($url_data['scheme'] != 'bors')
			bors_throw("Unknow protocol '{$url_data['scheme']}' instead bors://");

		$this->rpc_project = $url_data['host'];
		list($foo, $this->rpc_class_name, $this->rpc_id)  = explode('/', $url_data['path']);

		var_dump($this->rpc_class_name, $this->rpc_id);

		return false;
	}
}
