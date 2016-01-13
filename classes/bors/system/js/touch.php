<?php

class bors_system_js_touch extends bors_js
{
	function body_data()
	{
		$module_htmls = array();
		if($modules = bors()->request()->data('modules'))
		{
			if(preg_match('/^\[/', $modules)) // JSON
			{
				foreach(json_decode($modules, true) as $args)
				{
					if($x = bors_load_ex($args['class'], NULL, $args))
						$module_htmls[] = array('id' => md5($x->internal_uri_ascii()), 'html' => $x->html());
				}
			}
			elseif(is_array($modules))
			{
				foreach($modules as $uri)
					if($x = bors_load_uri($uri))
						$module_htmls[] = array('id' => md5($uri), 'html' => $x->html());
			}
			else
				bors_debug::syslog('error-data', "Is not array: ".print_r($modules, true));
		}

		// А то на pre_show от модуля вылезает content-type от pors_page
		header('Content-type: text/javascript; charset='.$this->output_charset());
		return array_merge(parent::body_data(), compact('module_htmls'));
	}
}
