<?php

class bors_object_unittest extends PHPUnit_Framework_TestCase
{
    public function test_bors_object()
    {
		$object = object_load('bors_object');
        $this->assertNotNull($object);

		// Проверка предустановленных атрибутов
        $this->assertEquals($object->url_engine(), 'url_calling');
    }
}
