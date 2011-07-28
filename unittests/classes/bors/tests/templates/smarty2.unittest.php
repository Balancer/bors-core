<?php

class bors_tests_templates_smarty2_unittest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
		$page = bors_load('bors_tests_templates_smarty2', NULL);
        $this->assertNotNull($page);

		$body = explode("\n", $page->body());
//		var_dump($body);
		$this->assertEquals(ec('Переменная 1 = значение переменной 1'), $body[0]);
		$this->assertEquals(ec('Глобальная переменная 2 = значение глобальной переменной 2'), $body[1]);
		$this->assertEquals(ec('Цикл var3: —1——2—LAST—3—'), $body[2]);
	}
}
