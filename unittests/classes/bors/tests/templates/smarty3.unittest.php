<?php

class bors_tests_templates_smarty3_unittest extends PHPUnit_Framework_TestCase
{
    public function test($class = 'bors_tests_templates_smarty3')
    {
		$page = bors_load($class, NULL);
        $this->assertNotNull($page);

		$body = explode("\n", $page->body());
		$page = $page->content();
//		var_dump($body);
//		var_dump($page);

		$i = 0;
		foreach(array(
			ec('Переменная 1 = значение переменной 1.'),
			ec('Глобальная переменная 2 = значение глобальной переменной 2.'),
			ec('Цикл var3: —1——2—LAST—3—.'),
			ec('Цикл for: 1,2,3,4,5,.')
		) as $s)
		{
			$this->assertEquals($s, $body[$i++]);
			$this->assertRegExp('/^'.preg_quote($s).'$/m', $page);
		}
	}
}
