<?php

function bors_search_object_index($object, $append = 'ignore', $db = NULL)
{
	if(!$object)
		return $object;

//	debug_hidden_log('blocked-code-errors', "Try to index $object");
//	echo('Try to reindex '.$object);
//	return;

	$source	= $object->search_source();
	$title	= $object->title();

//	include_once('include/classes/text/Stem_ru-'.config('charset_u', 'utf8').'.php');

	if(!$db)
		$db = new DataBase(config('search_db'));

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
							WHERE target_class_id = {$class_name}
								AND target_object_id = {$object_id}"
//								AND target_page = {$object_page}"
				);
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
						'int target_class_id' => $class_name, 
						'int target_object_id' => $object_id, 
//						'int class_page' => $object_page, 
						'int count' => $count, 
						'int target_create_time' => $object->create_time(), 
						'int target_modify_time' => $object->modify_time(),
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
				WHERE target_class_id = {$class_name}
					AND target_object_id = {$object_id}
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
					'int target_class_id' => $class_name, 
					'int target_object_id' => $object_id, 
//					'int class_name' => $class_name,
					'int target_create_time' => $object->create_time(), 
					'int target_modify_time' => $object->modify_time(),
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
	if(config('charset', 'utf-8') == 'utf-8')
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

	include_once('include/classes/text/Stem_ru-'.config('charset_u', 'utf8').'.php');

	$db = new DataBase(config('search_db'));

	$Stemmer = new Lingua_Stem_Ru();

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

//	@include_once('classes/inc/text/Stem_ru-'.config('charset_u', 'utf-8').'.php');

	$Stemmer = new Lingua_Stem_Ru();
	$original = $word;
	$word = $Stemmer->stem_word($word);

	if(bors_strlen($word) > 16)
		$word = bors_substr($word, 0, 16);

	if(!empty($GLOBALS['bors_search_get_word_id_cache'][$word]))
		return $GLOBALS['bors_search_get_word_id_cache'][$word];

	if(!$db)
		$db = new driver_mysql(config('search_db'));

	$word_id = $db->get("SELECT id FROM bors_search_words WHERE word = '".addslashes($word)."'");

	if(!$word_id)
	{
//		echo "Insert '$original' => '$word'\n";
		$db->insert('bors_search_words', array('word' => $word));
		$word_id = $db->last_id();
	}

//	if(bors_lower($original) == bors_lower($word))
//		echo dc("{$original} => {$word}\n");

	return $GLOBALS['bors_search_get_word_id_cache'][$word] = $GLOBALS['bors_search_get_word_id_cache'][$original] = intval($word_id);
}

function bors_search_stem($word)
{
	static $Stemmer = NULL;
	if(!$Stemmer)
	{
//		echo "**** New Stemmer ****\n";
//		include_once('include/classes/text/Stem_ru-'.config('charset_u', 'utf8').'.php');
		$Stemmer = new Lingua_Stem_Ru();
	}

	return $Stemmer->stem_word($word);
}

function bors_search_stem_array($words)
{
	$result = array();
	foreach($words as $w)
		$result[$w] = bors_search_stem($w);

	return $result;
}

function bors_search_get_word_id_array($words, $db = NULL)
{
	$buffer = array();

	if(!$db)
		$db = new DataBase(config('search_db'));

	$stemmed_map = bors_search_stem_array($words);
	$list = array_map('addslashes', array_unique(array_values($stemmed_map)));
	$list = array_map(create_function('$s', "return \"'\$s'\";"), $list);

	$ids = array();
	$stem_ids = array();

	foreach($db->get_array("SELECT id, word FROM bors_search_words WHERE word IN (".join(",", $list).")") as $x)
		$stem_ids[$x['word']] = $x['id'];

	foreach($stemmed_map as $word => $stemmed)
	{
		if(empty($ids[$word]))
			$ids[$word] = @$stem_ids[$stemmed];

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

	include_once('include/classes/text/Stem_ru-'.config('charset_u', 'utf8').'.php');

	$db = new DataBase(config('search_db'));

	$Stemmer = new Lingua_Stem_Ru();

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
					$out .= "<a href=\"http://balancer.ru/forum/topic/$sub/{$t['id']}/\" title=\"[{$w}]\"><img src=\"http://airbase.ru/img/design/icons/topic-9x10.png\" width=\"9\" heght=\"10\" align=\"absmiddle\">&nbsp;{$t['subject']}</a><br />\n";//&nbsp;&#183;&nbsp;
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

	$db = new driver_mysql(config('search_db'));

	$Stemmer = new Lingua_Stem_Ru();

	$must = array();
	$none = array();
	$maybe= array();

	foreach($words as $word)
	{
		if(!$word)
			continue;
//		if($word{0} == '+')
//			$must[] = bors_search_get_word_id(substr($word, 1));
//		else
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
			$res = $db->select_array('bors_search_source_'.($w%10),
				'DISTINCT target_class_id, target_object_id', array(
					'word_id' => $w,
					'order' => '-target_modify_time'
				)
			);

			if($first)
				$cross = $res;
			else
			{
				$cross = array_uintersect($cross, $res, create_function('$x, $y', 'return 
					$x["target_object_id"] * 1000 + $x["target_class_id"] -
						($y["target_object_id"] * 1000 + $y["target_class_id"]);'));
			}
			$first = false;
		}
	}

	if($none)
		foreach($none as $w)
			$cross = array_diff($cross, $db->select_array('bors_search_source_'.($w%10),
				'DISTINCT target_class_id, target_object_id', array(
					'word_id' => $w
				)
			));

	$result = array();

	if($cross)
		foreach($cross as $x)
			$result[] = object_load($x['target_class_id'], $x['target_object_id']);

	return $result;
}
