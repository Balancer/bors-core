<?php

class bors_storage_htsuIterator implements Iterator
{
	var $object;
	var $__class_name;
	var $storage;
	private $dhs;

    public function rewind()
    {
		$this->files = search_dir($this->root, '\.htsu$');
		$this->position = 0;
		$class_name = $this->__class_name;
		$object = new $class_name(NULL);
		$this->storage = $object->storage();
    }

    public function valid()
    {
		return isset($this->files[$this->position]);
    }

    public function current()
    {
		return $this->__init_object();
    }

    public function key()
    {
		// Not implemented yet
    }

    public function next()
    {
		$this->position++;
    }

	private function __init_object()
	{
		$file_name = $this->files[$this->position];
		$class_name = $this->__class_name;
		$object = new $class_name($file_name);
		$object->set_attr('htsu_file', $file_name);
		$object->set_attr('htsu_root', $this->root);
		$url = str_replace($this->root, '', $file_name);
		$url = preg_replace('!/main\.htsu$!', '/', $url);
		$url = preg_replace('!/([^\/]+)\.htsu!', '/$1/', $url);
		$object->set_attr('url', config('main_site_url').$url);
		$object->set_attr('url_engine', 'bors_url_attr');
		$this->storage->load($object);
		return $this->object = $object;
	}
}
