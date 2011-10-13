<?php

class bors_system_js_touch extends bors_js
{
	function body_data()
	{
		$module_htmls = array();
		if($modules = bors()->request()->data('modules'))
		{
			foreach($modules as $uri)
				if($x = bors_load_uri($uri))
					$module_htmls[] = array('id' => md5($uri), 'html' => $x->html());
		}

		return compact('module_htmls');
	}
}
