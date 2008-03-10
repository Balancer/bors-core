<?
	require_once("engines/bors.php");

    function smarty_bors_get_template ($tpl_name, &$tpl_source, &$smarty_obj)
    {
        // do database call here to fetch your template,
        // populating $tpl_source
        $obj = class_load($tpl_name);
        $tpl = $obj->body();

        if($tpl) 
        {
            $tpl_source = $tpl;
            return true;
        } 
        else 
        {
            return false;
        }
    }
    
    function smarty_bors_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj)
    {
        // do database call here to populate $tpl_timestamp.
        $obj = class_load($tpl_name);
        $time = $obj->modify_time();
	
		if(bors()->main_object())
	        $time = max($time, bors()->main_object()->modify_time(), bors()->main_object()->compile_time());

//      $time = max($time, $obj->dbh->get_value('hts_ext_system_data', 'key', 'global_recompile', 'value'));

        if($time) 
        {
            $tpl_timestamp = $time;
            return true;
        } 
        else 
        {
            return false;
        }
    }

    function smarty_bors_get_secure($tpl_name, &$smarty_obj)
    {
        // assume all templates are secure
        return true;
    }

    function smarty_bors_get_trusted($tpl_name, &$smarty_obj)
    {
        // not used for templates
    }
