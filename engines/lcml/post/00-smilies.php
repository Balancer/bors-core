<?php
// Smilies processing
// Global vars:
// $GLOBALS['cms_smilies_dir'] - full path to smilies dir
// $GLOBALS['cms_smilies_url'] - full or relative url of smilies dir
//
// (c) Balancer 2003-2004


function lcml_smilies($txt)
{
	if(!config('smilies_dir'))
		return $txt;

	$txt = lcml_smilies_by_list($txt);
	$txt = lcml_smilies_by_files(config('smilies_dir'), $txt);

	return $txt;
}

function lcml_smilies_by_list(&$txt)
{
	global $smilies_list;

	if(empty($smilies_list))
		$smilies_list = @file(config('smilies_dir')."/list.txt");

	foreach($smilies_list as $x)
	{
		@list($code, $file) = explode(' ', chop($x));
		if($file)
			$txt = preg_replace('!(?<=^|\s)'.preg_quote($code).'(?=\s|$|\)|\]|\.)!us', "<img src=\"".config('smilies_url')."/{$file}.gif\" alt=\"$code\" title=\"$code\" class=\"smile\" />",$txt);
		else
			$txt = preg_replace('/(?<!"):$code:(?!")/', "<img src=\"".config('smilies_url')."/$code.gif\" alt=\":$code:\" title=\":$code:\" class=\"smile\" />", $txt);
	}

	return $txt;
}

function lcml_smilies_by_files($dir, &$txt)
{
		$from = array();
		$to   = array();

        foreach(lcml_smilies_list($dir) as $code)
		{
			$from[] = '/(?<!"):'.preg_quote($code, '/').':/';
			$to[]   = "<img src=\"".config('smilies_url')."/{$code}.gif\" alt=\":{$code}:\" title=\":{$code}:\" class=\"smile\" />";
		}

//		if(config('is_developer')) { print_dd($from); print_dd($to); }

        return preg_replace($from, $to, $txt);
}

function lcml_smilies_list($dir)
{
		$save = config('cache_disabled');
		config_set('cache_disabled', false);
        $cache = new Cache();

//		echo "Get ".get_class($cache)."<br/>";

        if($cache->get('smilies-'.config('lcml_smiles_cache_tag'), $dir))
		{
//			if(is_array($cache->last()))
//			{
//				return $cache->last();
//			}
//			else
//			{
//				echolog("Given smilies array ".print_r($cache->last(), true), 1);
				config_set('cache_disabled', $save);
	            return $cache->last();
//			}
		}

		$list = lcml_smilies_load($dir);

//		echo "Set $cache<br/>";
		$cache->set($list, -30*86400);
		config_set('cache_disabled', $save);
		return $list;
}

function lcml_smilies_load($dir, $prefix="")
{
        $list = array();

        if(is_dir($dir))
        {
            if($dh = opendir($dir)) 
            {
                while(($file = readdir($dh)) !== false) 
                {
                    if(substr($file,-4)=='.gif')
                        $list[] = $prefix.substr($file,0,-4);
                    elseif(filetype("$dir/$file")=='dir' && substr($file,0,1)!='.')
                        $list = array_merge($list, lcml_smilies_load("$dir/$file", "$file/"));
                }
                closedir($dh);
            }
        }

        return $list;
}
