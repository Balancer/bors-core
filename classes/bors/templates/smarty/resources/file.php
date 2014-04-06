<?php

class bors_templates_smarty_resources_file extends Smarty_Resource_Custom
{
	private $smarty = NULL;

	function __construct($smarty)
	{
		$this->smarty = $smarty;
	}

/**
  * Fetch a template and its modification time from database
  *
  * @param string $name template name
  * @param string $source template source
  * @param integer $mtime template modification timestamp (epoch)
  * @return void
  */
	protected function fetch($tpl_name, &$tpl_source, &$mtime)
	{
		if(file_exists($tpl_name))
		{
			$tpl_source	= ec(file_get_contents($tpl_name));
			$mtime = filemtime($tpl_name);
			return;
		}

		if(($dirs = $this->smarty->getTemplateVars('template_dirnames')))
		{
			foreach($dirs as $dir)
			{
				if(file_exists($fn = str_replace('xfile:', '', $dir)."/".$tpl_name))
				{
					$tpl_source = ec(file_get_contents($fn));
					$mtime = filemtime($fn);
					return;
				}
			}
		}

		if(file_exists($fn = $this->smarty->template_dir."/".$tpl_name))
		{
			$tpl_source = ec(file_get_contents($fn));
			$mtime = filemtime($fn);
			return;
		}

		foreach(bors_dirs(true) as $dir)
		{
			if(file_exists($fn = $dir.'/templates/'.$tpl_name))
			{
				$tpl_source = ec(file_get_contents($fn));
				$mtime = filemtime($fn);
				return;
			}

			if(file_exists($fn = $dir.'/'.$tpl_name))
			{
				$tpl_source = ec(file_get_contents($fn));
				$mtime = filemtime($fn);
				return;
			}
		}

		$tpl_source = NULL;
		$mtime = NULL;
		return;
	}

/**
  * Fetch a template's modification time from database
  *
  * @note implementing this method is optional. Only implement it if modification times can be accessed faster than loading the comple template source.
  * @param string $name template name
  * @return integer timestamp (epoch) the template was modified
  */
	protected function fetchTimestamp($tpl_name)
	{
		static $cache;
		if(!empty($cache[$tpl_name]))
			return $cache[$tpl_name];

		$tpl_timestamp = NULL;

		if(file_exists($tpl_name))
			$tpl_timestamp = filemtime($tpl_name);

		if(($dirs = $this->smarty->getTemplateVars('template_dirnames')))
		{
			foreach($dirs as $dir)
			{
				if(!$tpl_timestamp && file_exists($fn = str_replace('xfile:', '', $dir)."/".$tpl_name))
				{
					$tpl_timestamp = filemtime($fn);
					break;
				}
			}
		}

		foreach($this->smarty->getTemplateDir() as $d)
			if(!$tpl_timestamp && file_exists($fn = "$d/$tpl_name"))
				$tpl_timestamp = filemtime($fn);

		$find_tpl = '/templates/'.$tpl_name;
		$find_classes_tpl = '/'.$tpl_name;
		$default_template_dir = '/templates/'.dirname(config('default_template')).'/'.$tpl_name;

		if(!$tpl_timestamp)
		{
			foreach(bors_dirs(true) as $dir)
			{
				if(file_exists($fn = $dir.$find_tpl))
				{
					$tpl_timestamp = filemtime($fn);
					break;
				}

				if(file_exists($fn = $dir.$find_classes_tpl))
				{
					$tpl_timestamp = filemtime($fn);
					break;
				}

				if(file_exists($fn = $dir.$default_template_dir))
				{
					$tpl_timestamp = filemtime($fn);
					break;
				}
			}
		}

		if(!$tpl_timestamp)
			return $tpl_timestamp;

		if(config('templates_cache_disabled'))
			$tpl_timestamp = time();

		$cache[$tpl_name] = $tpl_timestamp;

		return $tpl_timestamp;
	}
}
