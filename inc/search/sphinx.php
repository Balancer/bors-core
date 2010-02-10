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

	$ranker = SPH_RANK_PROXIMITY_BM25;

	$cl = new SphinxClient();
	$cl->SetServer($host, $port);
	$cl->SetConnectTimeout(1);

	if($weights = defval($params, 'weights'))
		$cl->SetIndexWeights($weights);

	if($is_exact = defval($params, 'exactly'))
		$cl->SetMatchMode (SPH_MATCH_PHRASE);
	else
		$cl->SetMatchMode (SPH_MATCH_ALL);

	$page = defval($params, 'page', 1)-1;
	$per_page = defval($params, 'per_page', 50);
	$cl->SetLimits($page * $per_page, $per_page, 5000);

//	$cl->SetMaxQueryTime(bors()->user() ? 10000 : 3000);

//	$f = $this->f();
//	if($f && $f[0])
//		$cl->SetFilter('forum_id', $f);

//	if($disabled = airbase_forum_forum::disabled_ids_list())
//		$cl->SetFilter('forum_id', $disabled, true);

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

	$cl->SetRankingMode($ranker);
	$cl->SetArrayResult(true);
	$res = $cl->Query(dc($query), $indexes);

	$data = array();

	$data['q'] = $query;
	$data['res'] = &$res;
	$data['total'] = $res['total'];

	if($res === false)
		$data['error'] = $cl->GetLastError();
	else
	{
		if ( $cl->GetLastWarning() )
			$data['warning'] = $cl->GetLastWarning();

		if(empty($res['matches']))
			return false;

		$objects = array();

		foreach($res['matches'] as $x)
		{
			$object = object_load($x['attrs']['class_id'], $x['attrs']['object_id']);
			$objects[] = $object;
		}

		$docs = array();
		$loop = 0;
		foreach($objects as $x)
		{
			$docs[$loop] = dc(strip_tags($x->source()));
			$docs_map[$loop] = $x;
			$loop++;
		}

		if($docs)
		{
			$opts = array (
				'before_match'		=> '<b>',
				'after_match'		=> '</b>',
				'chunk_separator'	=> ' ... ',
				'limit'				=> 300,
				'around'			=> 5,
			);

			$opts['exact_phrase'] = $is_exact;

			$exc = $cl->BuildExcerpts($docs, 'news', dc($query), $opts);

			if (!$exc)
				echo $data['error'] = $cl->GetLastError();
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
