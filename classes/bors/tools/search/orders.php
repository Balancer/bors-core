<?php

class bors_tools_search_orders extends base_list
{
	function named_list()
	{
		return array(
			't' => ec('По релевантности и актуальности'),
			'r' => ec('По релевантности'),
			'c' => ec('По дате сообщения от новых'),
			'co' => ec('По дате сообщения от старых'),
			'u' => ec('По дате сообщения от обновлённых'),
//			'ans' => ec('По убыванию числа ответов'),
		);
	}
}
