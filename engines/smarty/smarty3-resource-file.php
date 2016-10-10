<?php
    // from PHP script
    // put these function somewhere in your application

    function smarty_resource_file_get_template($tpl_name, &$tpl_source, $smarty)
    {
//		echo "engines/smarty3: load template $tpl_name<br/>\n";
        // do database call here to fetch your template,
        // populating $tpl_source
		if(file_exists($tpl_name))
		{
			$tpl_source = ec(file_get_contents($tpl_name));
			return true;
		}

		if(($dirs = $smarty->getTemplateVars('template_dirnames')))
			foreach($dirs as $dir)
			{
				if(file_exists($fn = str_replace('xfile:', '', $dir)."/".$tpl_name))
				{
					$tpl_source = ec(file_get_contents($fn));
					return true;
				}
			}

		foreach(bors_dirs(true) as $dir)
		{
			if(file_exists($fn = $dir.'/templates/'.$tpl_name))
			{
				$tpl_source = ec(file_get_contents($fn));
				return true;
			}
			if(file_exists($fn = $dir.'/'.$tpl_name))
			{
				$tpl_source = ec(file_get_contents($fn));
				return true;
			}
		}

        return false;
    }

    function smarty_resource_file_get_timestamp($tpl_name, &$tpl_timestamp, $smarty)
    {
//		echo "engines/smarty3: load template $tpl_name<br/>\n";
    	static $cache;
    	if(!empty($cache[$tpl_name]))
			return ($tpl_timestamp = $cache[$tpl_name]) > 0;

		$found = false;

		if(file_exists($tpl_name))
		{
			$tpl_timestamp = filemtime($tpl_name);
			$found = true;
		}

		if(($dirs = $smarty->getTemplateVars('template_dirnames')))
			foreach($dirs as $dir)
			{
				if(!$found && file_exists($fn = str_replace('xfile:', '', $dir)."/".$tpl_name))
				{
					$tpl_timestamp = filemtime($fn);
					$found = true;
				}
			}

		// WTF? Так и не удалось понять, откуда сюда подлезает массив в http://airbase.home.balancer.ru/
		if(!$found && is_array($smarty->template_dir))
		{
			foreach($smarty->template_dir as $dir)
			{
				if(file_exists($fn = "$dir/$tpl_name"))
				{
					$tpl_timestamp = filemtime($fn);
					$found = true;
					break;
				}
			}
		}

		if(!$found && !is_array($smarty->template_dir) && file_exists($fn = $smarty->template_dir."/".$tpl_name))
		{
			$tpl_timestamp = filemtime($fn);
			$found = true;
		}

		$find_tpl = '/templates/'.$tpl_name;
		$find_classes_tpl = '/'.$tpl_name;
		$default_template_dir = '/templates/'.dirname(\B2\Cfg::get('default_template')).'/'.$tpl_name;

		if(!$found)
		{
			foreach(bors_dirs(true) as $dir)
			{
				if(file_exists($fn = $dir.$find_tpl))
				{
					$tpl_timestamp = filemtime($fn);
					$found = true;
					break;
				}

				if(file_exists($fn = $dir.$find_classes_tpl))
				{
					$tpl_timestamp = filemtime($fn);
					$found = true;
					break;
				}

				if(file_exists($fn = $dir.$default_template_dir))
				{
					$tpl_timestamp = filemtime($fn);
					$found = true;
					break;
				}
			}
		}

		if(!$found)
			return false;

		if(\B2\Cfg::get('templates_cache_disabled'))
			$tpl_timestamp = time();

		$cache[$tpl_name] = $tpl_timestamp;

        return true;
    }

    function smarty_resource_file_get_secure($tpl_name, &$smarty_obj)
    {
        // assume all templates are secure
        return true;
    }

    function smarty_resource_file_get_trusted($tpl_name, &$smarty_obj)
    {
        // not used for templates
    }
