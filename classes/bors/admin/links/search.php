<?php

class bors_admin_links_search extends bors_admin_links_main
{
	function title() { return ec('Поиск связей'); }
	function nav_name() { return ec('поиск'); }

	function xpre_show()
	{
		var_dump($_GET);
		return parent::pre_show();
	}

	function where($cond)
	{
		$from_class = $this->fc();
		$to_class = $this->tc();

		$from_id = $this->fi();
		$to_id = $this->ti();

		if($from_class > $to_class)
		{
			list($from_class, $to_class) = array($to_class, $from_class);
			list($from_id, $to_id) = array($to_id, $from_id);
		}
		elseif($from_class == $to_class)
		{
			list($from_id, $to_id) = array($to_id, $from_id);
		}

		if($from_class)
			$cond['from_class'] = class_name_to_id($from_class);

		if($from_id)
			$cond['from_id'] = $from_id();

		if($to_class)
			$cond['to_class'] = class_name_to_id($to_class);

		if($to_id)
			$cond['to_id'] = $to_id;

		return parent::where($cond);
	}

	function skip_save() { return true; }
	function id() { return rand(); }
	function act() { return bors()->request()->data('act') ; }
	function fc() { return bors()->request()->data('fc'); }
	function fi() { return bors()->request()->data('fi'); }
	function tc() { return bors()->request()->data('tc'); }
	function ti() { return bors()->request()->data('ti'); }
}
