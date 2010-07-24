<?php

require_once('sphinx/sphinxapi.php');

function bors_search_sphinx($query, $params = array())
{
	$query = trim($query);

	if(!$query)
		return false;

	$host = config('search_sphinx_host', 'localhost');
	$port = config('search_sphinx_port', 9312);

	$indexes = defval($params, 'indexes', '*');

	$filtervals = array();
	$distinct = "";

	static $instance = false;

	if(!$instance || empty($params['persistent_instance']))
		$cl = $instance = new SphinxClient();
	else
		$cl = $instance;

	$cl->SetServer($host, $port);
	$cl->SetConnectTimeout(1);

	if($weights = defval($params, 'field_weights'))
		$cl->SetFieldWeights($weights);

	if($weights = defval($params, 'index_weights'))
		$cl->SetIndexWeights($weights);

	if($is_exact = defval($params, 'exactly'))
		$cl->SetMatchMode (SPH_MATCH_PHRASE);
	else
		$cl->SetMatchMode (SPH_MATCH_ALL);

	$page = defval($params, 'page', 1)-1;
	$per_page = defval($params, 'per_page', 50);
	$cl->SetLimits($page * $per_page, $per_page, config('search_sphinx_max_matches', 5000));

//	$cl->SetMaxQueryTime(bors()->user() ? 10000 : 3000);

	if($filter = defval($params, 'filter'))
	{
		foreach($filter as $name => $val)
		{
			if(preg_match('/^(\w+)<>$/', $name, $m))
				$cl->SetFilter($m[1], $val, true);
			else
				$cl->SetFilter($name, $val);
		}
	}

//	if($this->u())
//	{
//		$user = objects_first('forum_user', array('username' => $this->u()));
//		if($user)
//		$cl->SetFilter('owner_id', array($user->id()));
//	}

//	if($this->t())
//		$cl->SetFilter('topic_id', array(intval($this->t())));

	switch(defval($params, 'sort_order'))
	{
		case 'c':
			$cl->SetSortMode(SPH_SORT_ATTR_DESC, 'create_time');
			break;
		case 'co':
			$cl->SetSortMode(SPH_SORT_ATTR_ASC, 'create_time');
			break;
		case 'r':
			$cl->SetSortMode(SPH_SORT_RELEVANCE);
		case 't':
		default:
			$cl->SetSortMode(SPH_SORT_TIME_SEGMENTS, 'create_time');
			break;
	}

//	echo $cl->GetSortMode();

//	$ranker = SPH_RANK_PROXIMITY_BM25;
//	$ranker = SPH_RANK_BM25;
//	$ranker = SPH_RANK_NONE;
//	$cl->SetRankingMode($ranker);

//	$cl->SetArrayResult(true);
	$res = $cl->Query(dc($query), $indexes);

//	var_dump($res);

	$data = array();

	$data['q'] = $query;
	$data['res'] = &$res;
	$data['total'] = $res['total'];

	if($res === false)
		echo $data['error'] = $cl->GetLastError();
	else
	{
		if ( $cl->GetLastWarning() )
			$data['warning'] = $cl->GetLastWarning();

		if(empty($res['matches']))
			return false;

		$objects = array();
		$target_classes = defval($params, 'target_classes', array());

		foreach($res['matches'] as $x)
		{
			if(($object = object_load($x['attrs']['class_id'], $x['attrs']['object_id'])))
			{
				if(!$target_classes || in_array($object->class_name(), $target_classes))
				{
					$object->set_search_weight($x['weight'], false);
					$objects[] = $object;
				}
			}
			else
				debug_hidden_log('search_warning', "Unknown object {$x['attrs']['class_id']}({$x['attrs']['object_id']}) in query {$query}($indexes)");
		}

		if(defval($params, 'only_objects'))
			return $objects;

		$docs = array();
		$loop = 0;
		foreach($objects as $x)
		{
			if(!($source = $x->get('snipped_source')))
				$source = join("<br/>\n", array($x->get('description'), $x->get('source')));

			$docs[$loop] = dc(strip_tags($source));
			$docs_map[$loop] = $x;
			$loop++;
		}

		if($docs)
		{
			$opts = array (
				'before_match'		=> '<b>',
				'after_match'		=> '</b>',
				'chunk_separator'	=> ' ... ',
				'limit'				=> config('search_snippet_length', 500),
				'around'			=> 5,
			);

			$opts['exact_phrase'] = $is_exact;

			$exc = $cl->BuildExcerpts($docs, 'news', dc($query), $opts);

			if (!$exc)
				$data['error'] = $cl->GetLastError();
			else
			{
				$loop = 0;
				foreach($exc as $s)
				{
					$obj = $docs_map[$loop];
					$obj->set_snipped_body(ec($exc[$loop]), false);
					$loop++;
				}
			}
		}

		$data['objects'] = $objects;

		return $data;
	}

	return false;
}

function bors_search_sphinx_find_links($object, $delete_old = false)
{
	bors_synonym::add_object($object, array('is_auto' => true));
	bors()->changed_save();

	if(!$object)
		return;

	if($delete_old)
		bors_link::drop_auto($object);

	static $loop_count = 0;
	$verbose = 0;

	$log = "<br/>\nSynonyms for {$object}: ".join(', ', $object->all_names());
	$synonyms = bors_synonym::synonyms($object, array('is_disabled' => 0));
	$log .= " [".join(', ', bors_field_array_extract($synonyms, 'norm_title'))."]";

	foreach($synonyms as $synonym)
	{
		$is_exactly = $synonym->is_exactly();
		$synonym->set_norm_title($is_exactly ? bors_lower($synonym->title()) : bors_text_clear($synonym->title(), false), true);
		$norm_title = $synonym->norm_title();
		$ch = new Cache();
//		$quoted_norm_title = '!\b'.preg_quote($norm_title, '\b!').'!';
//		echo "q='$quoted_norm_title'<br/>";
//		continue;

		$objects = bors_search_sphinx($synonym->title(), array(
			'indexes' => 'news,news_delta,digest,digest_delta',
			'only_objects' => true,
			'page' => 1,
			'per_page' => $verbose ? 10 : 5000,
			'persistent_instance' => true,
			'exactly' => true,
		));

		if(!$objects)
		{
			$log .= "<br/>\nTotal found for ".("{$synonym->title()}/{$norm_title}").": NULL";
			continue;
		}

		$log .= "<br/>\nTotal found for ".("'{$synonym->title()}'/'{$norm_title}'").": ".count($objects).": ";

		foreach($objects as $x)
		{
//			echo ".";
//			if($x->id() == 28880)
//				exit("!");
			$found = false;
			foreach($x->all_text_fields() as $k => $type_id)
			{
				$text = $x->$k();
				if($ch->get('text-clear-2-'.$is_exactly, $text))
				{
					$v = $ch->last();
				}
				else
				{
					if($is_exactly)
						$v = bors_lower($x->$k());
					else
						$v = bors_text_clear($x->$k(), false);

					$ch->set($v, rand(86400*30, 86400*90));
				}

				if(!$v)
					continue;

//				if($x->id() == 28880)
//					echo "$v<hr/>";

				if(strpos($v, $norm_title) !== false)
				{
//					echo str_replace($norm_title, "<b>".$quoted_norm_title."</b>", $v)."<hr/>";
//					echo preg_replace($quoted_norm_title, "<b>".$quoted_norm_title."</b>", $v)."<hr/>";
//					return '';
//					echo 'pm='.preg_match($quoted_norm_title, $v);
//					if($is_exactly && !preg_match($quoted_norm_title, $v))
//						continue;

//					$log .= $verbose ?
//						"Found ".dc($x->title())." [{$x->url()}] as $k($type_id)<br/>\n"
//						: "+";

					@$total[$type_id][$x->internal_uri_ascii()] = 1;
					bors_link::link_objects($x, $object, array(
						'owner_id' => '-1002151031', 
						'comment' => "autolink {$object->internal_uri_ascii()}/{$norm_title}[{$is_exactly}]",
						'type_id' => $type_id,
					));

					$found = true;
					break;
				}

			}

			if(!$found)
				$log .= $verbose ? "--- Not found ".($x->title())." [{$x->url()}]<br/>\n"
				: "-";

			bors()->changed_save();
			bors_object_caches_drop();
		}
	}

	$log .=  "<br/>\nmention: ".@count(@$total[2]).", about: ".@count(@$total[3])."<br/>\n";
//	if(++$loop_count >= 5)
//		bors_exit();

//	echo $log;
	return $log;
}
