<?php

class common_list_crTypes extends base_list
{
	function named_list()
	{
		return array(
			'save_cr' => ec('С сохранением переводов строк'),
			'empty_as_para' => ec('Пустая строка = параграф'),
		);
	}
}
