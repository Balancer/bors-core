<?php

    // from PHP script
    // put these function somewhere in your application

    function smarty_resource_file_get_template($tpl_name, &$tpl_source, &$smarty)
    {
        // do database call here to fetch your template,
        // populating $tpl_source
		if(file_exists($tpl_name))
		{
			$tpl_source = ec(file_get_contents($tpl_name));
			return true;
		}

		if(file_exists($fn = $smarty->template_dir."/".$tpl_name))
		{
			$tpl_source = ec(file_get_contents($fn));
			return true;
		}

		if(file_exists($fn = $GLOBALS['cms']['base_dir'].'/templates/'.$tpl_name))
		{
			$tpl_source = ec(file_get_contents($fn));
			return true;
		}

/*		if(file_exists($fn = $GLOBALS['cms']['base_dir'].'/templates/aviaport/'.$tpl_name))
		{
			$tpl_source = ec(file_get_contents($fn));
			return true;
		}
*/
        return false;
    }
    
    function smarty_resource_file_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
    {
		$found = false;
	
		if(file_exists($tpl_name))
		{
			$tpl_timestamp = filemtime($tpl_name);
			$found = true;
		}

		if(file_exists($fn = $smarty->template_dir."/".$tpl_name))
		{
			$tpl_timestamp = filemtime($fn);
			$found = true;
		}

		if(file_exists($fn = $GLOBALS['cms']['base_dir'].'/templates/'.$tpl_name))
		{
			$tpl_timestamp = filemtime($fn);
			$found = true;
		}

//        if(!$found && preg_match('!_head!', $tpl_name)) { echo "$tpl_name => ".$found; debug_trace(); print_d($smarty); }

/*		if(file_exists($fn = $GLOBALS['cms']['base_dir'].'/templates/aviaport/'.$tpl_name))
		{
			$tpl_timestamp = filemtime($fn);
			$found = true;
		}
*/

		if(!$found)
			return false;

		if(!empty($GLOBALS['cms']['templates_cache_disabled']))
			$tpl_timestamp = $GLOBALS['now'];

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
