<?php

class bors_page_unittest extends PHPUnit_Framework_TestCase
{
    public function test_bors_page_titles()
    {
		$page = object_load('bors_page');
        $this->assertNotNull($page);

		// Работа заголовков
		$page->set_title(ec('Тест'), false);
        $this->assertEquals(ec('Тест'), $page->title());
        $this->assertEquals(ec('Тест'), $page->page_title());
        $this->assertEquals(ec('Тест'), $page->browser_title());

		$page->set_page_title('', false);
        $this->assertEquals(ec('Тест'), $page->title());
        $this->assertEquals(ec('Тест'), $page->browser_title());
        $this->assertEquals('', $page->page_title());

		$page->set_browser_title(ec('Заголовок браузера'), false);
        $this->assertEquals($page->title(), ec('Тест'));
        $this->assertEquals('', $page->page_title());
        $this->assertEquals(ec('Заголовок браузера'), $page->browser_title());
    }
}
