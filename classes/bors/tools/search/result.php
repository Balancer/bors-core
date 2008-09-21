<?php

// Использование поисковой системы Sphinx
// http://www.sphinxsearch.com/

require_once('sphinx/sphinxapi.php');
// config_set('do_not_exit', true);

class bors_tools_search_result extends bors_tools_search
{
	function title() { return ec('Поиск по запросу «').$this->q().ec('»'); }
	function nav_name() { return $this->q(); }

	function parents() { return array('/tools/search/'); }
	
	function q() { return urldecode(@$_GET['q']); }
	function s() { return empty($_GET['s']) ? 't' : $_GET['s']; }
	function t() { return @$_GET['t']; }
	function u() { return @$_GET['u']; }
	function x() { return !empty($_GET['x']); }
	function f()
	{
		$f = @$_GET['f'];
		if(!is_array($f))
			$f = explode(',', urldecode($f));

		return $f;
	}
	
//	function parents() { return $this->q() ? array('/tools/search.bas?q=') : array('/tools/'); }
	function can_cached() { return false; }

	private $data = false;
	function pre_show()
	{
		if($this->data !== false)
			return false;
		
		$data = array();
		$this->data = &$data;
		
		if(!$this->q())
			return false;

		$host = "localhost";
		$port = 3312;

		$index = "*";
//		$groupby = "topic_id";
#		$groupsort = "@group desc";
//		$filter = "topic_id";
		$filtervals = array();
		$distinct = "";
#		$sortby = "timestamp";

		$ranker = SPH_RANK_PROXIMITY_BM25;

		$cl = new SphinxClient ();
		$cl->SetServer ( $host, $port );
		$cl->SetConnectTimeout ( 1 );
//		$cl->SetWeights ( array ( 100, 1 ) );
		$cl->SetIndexWeights ( array ( 'topics' => 500, 'forum_2' => 100) );

		if($this->x())
			$cl->SetMatchMode (SPH_MATCH_PHRASE);
		else
			$cl->SetMatchMode (SPH_MATCH_ALL);
		
		if ( count($filtervals) )
			$cl->SetFilter ( $filter, $filtervals );
		if ( @$groupby )
			$cl->SetGroupBy ( $groupby, SPH_GROUPBY_ATTR, @$groupsort );

		if ( @$distinct )
			$cl->SetGroupDistinct ( $distinct );

		$cl->SetLimits($this->items_offset(), $this->items_per_page());
		$cl->SetMaxQueryTime(bors()->user() ? 10000 : 3000);

		$f = $this->f();
		if($f && $f[0])
			$cl->SetFilter('forum_id', $f);

		if($disabled = airbase_forum_forum::disabled_ids_list())
			$cl->SetFilter('forum_id', $disabled, true);

		if($this->u())
		{
			$user = objects_first('forum_user', array('username' => $this->u()));
			if($user)
			$cl->SetFilter('owner_id', array($user->id()));
		}

		if($this->t())
			$cl->SetFilter('topic_id', array(intval($this->t())));
		
		switch($this->s())
		{
			case 'c':
				$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'create_time' );
				break;
			case 'co':
				$cl->SetSortMode (SPH_SORT_ATTR_ASC, 'create_time' );
				break;
			case 'r':
				$cl->SetSortMode(SPH_SORT_RELEVANCE);
			case 't':
			default:
				$cl->SetSortMode(SPH_SORT_TIME_SEGMENTS, 'create_time');
				break;
		}
		
		$cl->SetRankingMode ( $ranker );
		$cl->SetArrayResult ( true );
		$res = $cl->Query ( $this->q(), $index );

		if($res === false)
			$data['error'] = $cl->GetLastError();
		else
		{
			if ( $cl->GetLastWarning() )
				$data['warning'] = $cl->GetLastWarning();

			$data['q'] = $this->q();
			$data['res'] = &$res;
			
//			print_d($res);
			
			$opts = array
			(
				'before_match'		=> '<b>',
				'after_match'		=> '</b>',
				'chunk_separator'	=> ' ... ',
				'limit'				=> 300,
				'around'			=> 5,
			);

			$opts['exact_phrase'] = $this->x();

			if(empty($res['matches']))
				return false;

			$post_ids = array();
			$topic_ids = array();
			for($i=0; $i<count($res['matches']); $i++)
			{
				$x = &$res['matches'][$i];
				if(empty($x['attrs']['class_name']))
					$topic_ids[] = $x['id'];
				else
					$post_ids[] = $x['id'];
			}

			$this->data['posts'] = $posts = objects_array('forum_post', array('id IN' => $post_ids, 'by_id' => true));
			$this->data['topics'] = objects_array('forum_topic', array('id IN' => $topic_ids, 'by_id' => true));
			$docs = array();
			
			$loop = 0;
			foreach($posts as $pid => $p)
				$docs[$loop++] = strip_tags($p->source());

			$exc = $cl->BuildExcerpts($docs, 'forum_2', $this->q(), $opts);
			if (!$exc)
				echo $data['error'] = $cl->GetLastError();
			else
			{
				$loop = 0;
				foreach($posts as $pid => $p)
					$posts[$pid]->set_body($exc[$loop++], false);
			}
		}
		
		return false;
	}

	function local_template_data_set()
	{
		return $this->data;
	}
	
	function total_items() { return $this->data['res']['total']; }
	function id() { return true; }
	
	private function gets($list)
	{
		$result = array();
		foreach($list as $key => $val)
			if(!empty($val))
				$result[] = $key.'='.urlencode(is_array($val) ? join(',', $val) : $val);
		
		return $result ? '?'.join('&', $result) : '';
	}
	
	private function get_clear($enabled)
	{
		$enabled = explode(' ', $enabled);
		foreach($_GET as $key => $val)
			if(!in_array($key, $enabled))
				unset($_GET[$key]);
	}
	
	function url($page = NULL, $get = false)
	{
		return '/tools/search/result/'.($get ? $this->gets(array(
			'q' => $this->q(),
			'f' => $this->f(),
			's' => $this->s(),
			't' => $this->t(),
			'u' => $this->u(),
			'x' => $this->x(),
			'p' => $page > 1 ? $page : NULL
		)) : '');
	}

	function page() { return max(1, @$_GET['p']); }

	function set_x($value) { $_GET['x'] = $value; }
}
