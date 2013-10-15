<?php

class bors_object_unittest extends PHPUnit_Framework_TestCase
{
	public function test_bors_object()
	{
		$object = bors_load('bors_object', NULL);
		$this->assertNotNull($object);

		// Проверка предустановленных атрибутов
		$this->assertEquals($object->url_engine(), 'url_calling2');

		// Заголовок по умолчанию равен имени класса
		//TODO: сделать как правильно $this->assertEquals($object->title(), 'bors_object');

		// Истинный заголовок не определён
		$this->assertNull($object->title_true());

		// Проверяем полную работу base_empty.
		$object->set_attr('attr1', 'qwe');
		$object->set_attr('attr2', 'asd');
		$object->set('set3', 'zxc', false);
		$object->set_set4('rty', false);

		// Теперь смотрим, как оно сохранилось
		$this->assertEquals('qwe', $object->attr('attr1'));
		$this->assertEquals('asd', $object->attr2());
		$this->assertEquals('zxc', $object->set3());
		$this->assertEquals('rty', $object->get('set4'));
		$this->assertEquals('rty', $object->get_data('set4'));

		// И чтобы не запоминалось
		$this->assertNull($object->get('set5'));
		$this->assertEquals('uio', $object->get_data('set5', 'uio'));
		$this->assertNull($object->get('set5'));
		$this->assertNull($object->get_data('set5'));

//		$this->assertEquals('??', print_r(bors_lib_orm::all_fields($object), true));
	}
}
