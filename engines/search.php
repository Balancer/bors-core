<?php

function bors_search_object_index($object, $append = 'ignore', $db = NULL)
{
	if(!$object)
		return $object;
	
	$source	= $object->search_source();
	$title	= $object->title();

	include_once("include/classes/text/Stem_ru-{$GLOBALS['cms']['charset_u']}.php");
			
	if(!$db)
		$db = &new DataBase(config('search_db'));

	$object_id	= intval($object->id());
	$class_name	= intval($object->class_id());
	$object_page	= intval($object->page());

//	exit("$class_name($object_id, $object_page) => <xmp>$source</xmp>");
	
	if($source)
	{
		$words = index_split($source);
		
		$buffer = bors_search_get_word_id_array($words, $db);

		if($append == 'replace')
		{
			for($sub=0; $sub<10; $sub++)
				$db->query("DELETE FROM bors_search_source_{$sub}
							WHERE class_name = {$class_name}
								AND class_id = {$object_id}
								AND class_page = {$object_page}");

//			echo 0/0;
		}

		for($sub=0; $sub<10; $sub++)
		{
			$tab = "bors_search_source_{$sub}";

			if(!empty($buffer[$sub]))
			{
				$db->multi_insert_init($tab);
				foreach($buffer[$sub] as $word_id => $count)
				{
					$db->multi_insert_add($tab, array(
						'int word_id' => $word_id, 
						'int class_id' => $object_id, 
						'int class_name' => $class_name, 
						'int class_page' => $object_page, 
						'int count' => $count, 
						'int object_create_time' => $object->create_time(), 
						'int object_modify_time' => $object->modify_time(),
					));
				}
//				set_loglevel(9);
				if($append == 'replace')
					$db->multi_insert_do($tab);
				else
					$db->multi_insert_ignore($tab);
//				set_loglevel(2);
			}
		}
	}
//	exit("ok");

	if($title)
	{
		$words = index_split($title);
		
		if($append=='replace')
			$db->query("DELETE FROM bors_search_titles 
				WHERE class_name = {$class_name}
					AND class_id = {$object_id}
				");
		
		$doing = array();
		foreach($words as $word)
		{
			if(!$word)
				continue;
			
			$word_id = bors_search_get_word_id($word, $db);
			if(!$word_id || !empty($doing[$word_id]))
				continue;
		
			$doing[$word_id] = true;
			$data = array(
					'int word_id' => $word_id, 
					'int class_id' => $object_id, 
					'int class_name' => $class_name, 
					'int object_create_time' => $object->create_time(), 
					'int object_modify_time' => $object->modify_time(),
			);
		
			if($append == 'replace')
				$db->insert("bors_search_titles", $data);
			else
				$db->insert_ignore("bors_search_titles", $data);
		}
	}
}

function index_split($text)
{
//	return str_word_count($text, 1);
	if($GLOBALS['cms']['charset'] == 'utf-8')
		return preg_split('![ -,\./:-@\[-`\{-~\s¡-¿]+!u', trim($text));

	return preg_split(ec('![^\wа-яА-Я\-]+!'), trim($text));
}

function bors_search_in_titles($query, $params = array())
{

	$limit = defval($params, 'limit', 25);
	$page  = defval($params, 'page' ,  0);

	$sort = "";
	$lim  = "";
	if(empty($params['pages']))
	{
		$sort = "ORDER BY object_modify_time DESC";
		if($page > 0)
			$lim = "LIMIT ".($limit*($page-1)).", ".$limit;
	}

	// +word -word word
		
	$words = index_split($query);
		
	if(!$words)
		return array();

	include_once("include/classes/text/Stem_ru-{$GLOBALS['cms']['charset_u']}.php");
			
	$db = &new DataBase(config('search_db'));

	$Stemmer = &new Lingua_Stem_Ru();
				
	$must = array();
	$none = array();
	$maybe= array();

	foreach($words as $word)
	{
		if(!$word)
			continue;
		
		if($word{0} == '-')
			$none[] = bors_search_get_word_id(substr($word, 1));
//		elseif($word{0} == '+')
//			$must[] = bors_search_get_word_id(substr($word, 1));
		else
			$must[] = bors_search_get_word_id($word);
	}
		
	$cross = array();

	if($must)
	{
		$first = true;
		foreach($must as $w)
		{
			$res = $db->get_array("SELECT DISTINCT CONCAT(`class_name`, '://', `class_id`) FROM bors_search_titles WHERE word_id = $w $sort $lim");

			if($first)
				$cross = $res;
			else
				$cross = array_intersect($cross, $res);

			$first = false;
		}
	}

	if($none)
		$cross = array_diff($cross, $db->get_array("SELECT DISTINCT CONCAT(`class_name`, '://', `class_id`) FROM bors_search_titles WHERE word_id IN (".join(",", $none).")"));

	if(!empty($params['pages']))
		return sizeof($cross);
	
	$result = array();
	
	foreach($cross as $x)
	{
		list($class_name, $object_id) = explode('://', $x);
		$result[] = object_load($class_name, $object_id);
	}
	
	return $result;
}

function bors_search_get_word_id($word, $db = NULL)
{
	$word = trim($word);

	if(!$word)
		return 0;
			
	if(!empty($GLOBALS['bors_search_get_word_id_cache'][$word]))
		return $GLOBALS['bors_search_get_word_id_cache'][$word];
	
	include_once("include/classes/text/Stem_ru-{$GLOBALS['cms']['charset_u']}.php");
		
	$Stemmer = &new Lingua_Stem_Ru();
	$original = $word;
	$word = $Stemmer->stem_word($word);
			
	if(strlen($word) > 16)
		$word = substr($word, 0, 16);

	if(!empty($GLOBALS['bors_search_get_word_id_cache'][$word]))
		return $GLOBALS['bors_search_get_word_id_cache'][$word];

	if(!$db)
		$db = &new DataBase(config('search_db'));

	$word_id = $db->get("SELECT id FROM bors_search_words WHERE word = '".addslashes($word)."'");

	if(!$word_id)
	{
		$db->insert('bors_search_words', array('word' => $word));
		$word_id = $db->last_id();
	}

//	if(strtolower($original) == strtolower($word))
//		echo dc("{$original} => {$word}\n");
		
	return $GLOBALS['bors_search_get_word_id_cache'][$word] = $GLOBALS['bors_search_get_word_id_cache'][$original] = intval($word_id);
}

function bors_search_get_word_id_array($words, $db = NULL)
{
	$buffer = array();

	if(!$db)
		$db = &new DataBase(config('search_db'));

	$list = array_map('addslashes', array_unique($words));
	$list = array_map(create_function('$s', "return \"'\$s'\";"), $list);

	$ids = array();
	
	foreach($db->get_array("SELECT id, word FROM bors_search_words WHERE word IN (".join(",", $list).")") as $x)
		$ids[$x['word']] = $x['id'];

	foreach($words as $word)
	{
		if(empty($ids[$word]))
			$ids[$word] = bors_search_get_word_id($word, $db);

		$word_id = $ids[$word];

		@$buffer[$word_id%10][$word_id]++;
	}
		
	return $buffer;
}

function search_titles_like($title, $limit=20, $forum=0)
{
	$words = preg_split("!\s+!u", trim($query));
		
	if(!$words)
		return array();

	include_once("include/classes/text/Stem_ru-{$GLOBALS['cms']['charset_u']}.php");
			
	$db = &new DataBase(config('search_db'));

	$Stemmer = &new Lingua_Stem_Ru();
				
	$search = array();
	foreach($words as $word)
		$search[] = bors_search_get_word_id($word);
	
	$cross = array();
	if($maybe)
	{
		$first = true;
		foreach($maybe as $w)
		{
			$res = $db->get_array("SELECT DISTINCT class_name, class_id, class_page FROM titles_map WHERE t.word_id = $w");
			if($first)
				$cross = $res;
			else
				$cross = array_intersect($cross, $res);

			$first = false;
		}
	}

	$weights = array();

    if($forum == 0 && !empty($GLOBALS['cur_topic']))
       	$forum = $GLOBALS['cur_topic']['forum_id'];

    foreach($title as $word)
    {
		if(strlen($word) <= 2)
			continue;

        $chkw = new Cache();
       	if($chkw->get("forum_titles_with_key-$ver", $word))
           	$topics = unserialize($chkw->last);
		else
		{
			$topics      = $db->get_array("SELECT id, `num_replies`, `subject`, `forum_id`, 3 as weight FROM topics WHERE subject   LIKE '% ".addslashes($word)." %'  AND `moved_to` IS NULL AND forum_id != 37 ORDER BY `num_replies` DESC LIMIT ".intval($limit));
            if(strlen($word) > 2)
				$topics += $db->get_array("SELECT id, `num_replies`, `subject`, `forum_id`, 2 as weight FROM topics WHERE `subject` LIKE '% ".addslashes($word)."%'   AND `moved_to` IS NULL AND forum_id != 37 ORDER BY `num_replies` DESC LIMIT ".intval($limit));
            if(strlen($word) > 4)
				$topics += $db->get_array("SELECT id, `num_replies`, `subject`, `forum_id`, 1 as weight FROM topics WHERE `subject` LIKE '%".addslashes($word)."%'    AND `moved_to` IS NULL AND forum_id != 37 ORDER BY `num_replies` DESC LIMIT ".intval($limit));

        	$chkw->set(serialize($topics));
	 	}

	 	$n=1;
		foreach($topics as $t)
		{
		    if(empty($weights[$t['id']]))
		    	$weights[$t['id']] = 0;

		    $w = $t['weight'] * log($t['num_replies']+1) / ($n++ + sqrt(sizeof($topics))) + 1;
		    if($forum == $t['forum_id'])
			    $w *= 2;

			$weights[$t['id']] += intval($w*1000);
			$topics_info[$t['id']] = $t;
		}
	}

	arsort($weights);

	if(!empty($weights) && !empty($topics))
    {
//		$out .= "<dl class=\"box\"><dt>Похожие темы форума</dt>\n<dd>\n";
//		$out .= "<b>Похожие заголовки форума</b><br />\n";
		$n = 0;
        foreach($weights as $tid => $w)
        {
            if($n<$limit)
			{
	            $t = $topics_info[$tid];
	            if(!preg_match("!^From:!", $t['subject']))
	            {
	                $n++;
					$sub = $t['id'] % 1000;
					$out .= "<a href=\"http://balancer.ru/forum/topic/$sub/{$t['id']}/\" title=\"[{$w}]\"><img src=\"http://airbase.ru/img/design/icons/topic-9x10.png\" width=\"9\" heght=\"10\" border=\"0\" align=\"absmiddle\">&nbsp;{$t['subject']}</a><br />\n";//&nbsp;&#183;&nbsp;
	    		}
			}
		}
//		$out .= "</dd></dl>\n";
	}

	return $ch->set($out, 86400+rand(0,86400));
}

function bors_search_in_bodies($query)
{
	// +word -word word
		
	$words = index_split($query);
		
	if(!$words)
		return array();

	include_once("include/classes/text/Stem_ru-{$GLOBALS['cms']['charset_u']}.php");
			
	$db = &new DataBase(config('search_db'));

	$Stemmer = &new Lingua_Stem_Ru();
				
	$must = array();
	$none = array();
	$maybe= array();

	foreach($words as $word)
	{
		if(!$word)
			continue;

//		if($word{0} == '+')
//			$must[] = bors_search_get_word_id(substr($word, 1));
		if(preg_match("!^\-(.+)$!", $word, $m))
			$none[] = bors_search_get_word_id($m[1]);
		else
			$maybe[] = bors_search_get_word_id($word);
	}
	
	$cross = array();
	if($maybe)
	{
		$first = true;
		foreach($maybe as $w)
		{
			$res = $db->get_array("SELECT DISTINCT class_name, class_id, class_page FROM bors_search_source_".($w%10)." WHERE word_id = $w ORDER BY object_modify_time DESC");
			if($first)
				$cross = $res;
			else
				$cross = array_intersect($cross, $res);

			$first = false;
		}
	}

	if($none)
		foreach($none as $w)
			$cross = array_diff($cross, $db->get_array("SELECT DISTINCT class_name, class_id, class_page FROM bors_search_source_".($w%10)." WHERE word_id = {$w}"));

	$result = array();
	
	foreach($cross as $x)
		$result[] = object_load($x['class_name'], $x['class_id'], $x['class_page']);

	return $result;
}
