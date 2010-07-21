<?php

class bors_tests_page_unittest extends PHPUnit_Framework_TestCase
{
    public function testPage()
    {
		$page = object_load('bors_tests_page');
        $this->assertNotNull($page);
        $this->assertEquals($page->test_method(), 'test_method');

		// Проверка работы свойств, вызываемых как методы
        $this->assertEquals($page->test_property(), 'test property');

		$page->set_qwertyasdf('test', false);
        $this->assertEquals($page->qwertyasdf(), 'test');

		// Имя объекта по умолчанию - имя его класса
        $this->assertEquals($page->title(), 'bors_tests_page');

		// Точное имя объекта
        $this->assertNull($page->title_true());
    }
}
