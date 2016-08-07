<?php

/**
	Отложенная перекомпиляция LCML-разметки.
*/

class bors_task_lcml extends bors_object_simple
{
	function execute($object)
	{
		if(method_exists($object, 'do_lcml_compile'))
			$object->do_lcml_full_compile();
		else
			bors_debug::syslog('errors.lcml_task_compile', "Cannot find do_lcml_full_compile method in {$object->debug_title()}");
	}
}
