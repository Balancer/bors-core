<?php

class bors_tools_search_orders extends base_list
{
	function named_list()
	{
		return array(
			't' => ec('По релевантности и актуальности'),
			'r' => ec('По релевантности'),
			'c' => ec('По дате сообщения'),
		);
	}
}
