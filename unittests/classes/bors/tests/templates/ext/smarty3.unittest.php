<?php

// Просто должен работать шаблон из родительского класса

class bors_tests_templates_ext_smarty3_unittest extends bors_tests_templates_smarty3_unittest
{
    public function test()
    {
    	parent::test('bors_tests_templates_ext_smarty3');
    }

    public function test2()
    {
		$page = bors_load('bors_tests_templates_ext_smarty3', 'smarty3-2.html');
        $this->assertNotNull($page);

		$body = explode("\n", $page->body());
		$page = $page->content();
//		var_dump($body);
//		var_dump($page);

		$i = 0;
		foreach(array(
			ec('Альтернативный шаблон'),
			ec('Переменная 11 = значение переменной 11.'),
		) as $s)
		{
			$this->assertEquals($s, $body[$i++]);
			$this->assertRegExp('/^'.preg_quote($s).'$/m', $page);
		}
	}
}
