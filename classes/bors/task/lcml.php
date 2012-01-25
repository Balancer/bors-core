<?php

/**
	Отложенная перекомпиляция LCML-разметки.
*/

class bors_task_lcml extends base_empty
{
	function execute($object)
	{
		if(method_exists($object, 'do_lcml_compile'))
			$object->do_lcml_full_compile();
		else
			debug_hidden_log('errors.lcml_task_compile', "Cannot find do_lcml_full_compile method in {$object->debug_title()}");
	}
}
