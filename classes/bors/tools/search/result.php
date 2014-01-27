<?php

// Использование поисковой системы Sphinx
// http://www.sphinxsearch.com/

require_once(config('sphinx-search.include'));
// echo config('sphinx-search.include');
// config_set('do_not_exit', true);

class bors_tools_search_result extends bors_tools_search
{
	function title() { return ec('Поиск по запросу «').$this->q().ec('»'); }
	function nav_name() { return $this->q(); }

	function parents() { return array('/tools/search/'); }

	function q() { return urldecode(@$_GET['q']); }
	function s()
	{
		$s = empty($_GET['s']) ? 't' : $_GET['s'];
		if(in_array($this->w(), array('a', 'b')) && $s == 'u')
			$s = 'c';

		return $s;
	}

	function t() { return @$_GET['t']; }
	function y() { return @$_GET['y']; }
	function u() { return urldecode(@$_GET['u']); }
	function x() { return @$_GET['x']; }
	function w() { return urldecode(@$_GET['w']); }
	function f()
	{
		$f = @$_GET['f'];
		if(!is_array($f))
			$f = explode(',', urldecode($f));

		return $f;
	}

//	function parents() { return $this->q() ? array('/tools/search.bas?q=') : array('/tools/'); }
	function can_cached() { return false; }

	private $_data = false;
	function pre_show()
	{
		$this->set('page' , max(1, @$_GET['p']), false);

		if($this->_data !== false)
			return false;

		$data = array();
		$this->_data = &$data;

		if(!$this->q())
			return false;

		$host = "localhost";
		$port = 3312;
//echo $this->w();

		$weights = NULL;

		switch($this->w())
		{
			case 'a':
			case '1':
				$index = "blog_titles,blog_keywords,blog_sources,blog_sources_delta,posts,posts_delta,livestreet_topics";
				break;
			case 'b':
				$index = "blog_titles,blog_keywords,blog_sources,blog_sources_delta,livestreet_topics";
				$weights = array ('blog_titles' => 100 , 'blog_keywords' => 1000, 'blog_sources' => 10);
				break;
			case 't':
if(config('is_developer'))
				$index = "topic_titles,livestreet_topic_titles,hbr_titles";
else
				$index = "topic_titles,livestreet_topic_titles";
//				$weights = array ('topic_titles' => 100);
				break;
			default:
				$index = "topic_titles,topic_descriptions,topic_keywords,livestreet_topic_titles";
				$weights = array (
					'livestreet_topic_titles' => 5000,
					'topic_titles' => 1000 ,
					'topic_descriptions' => 100,
					'topic_keywords' => 10,
				);
				break;
		}
//		$groupby = "topic_id";
#		$groupsort = "@group desc";
//		$filter = "topic_id";
		$filtervals = array();
		$distinct = "";
#		$sortby = "timestamp";
//echo $index;
		$ranker = false; // SPH_RANK_PROXIMITY_BM25;

		$cl = new SphinxClient ();
		$cl->SetServer ( $host, $port );
		$cl->SetConnectTimeout ( 1 );

		if($weights)
			$cl->SetIndexWeights ( $weights );

		switch($this->x())
		{
			case 'e':
			case '1':
				$cl->SetMatchMode(SPH_MATCH_PHRASE);
				break;
			case 'b':
				$cl->SetMatchMode(SPH_MATCH_BOOLEAN);
				break;
			case 'x':
				$cl->SetMatchMode(SPH_MATCH_EXTENDED);
				break;
			case 'a':
				$cl->SetMatchMode(SPH_MATCH_ANY);
				break;
			default:
				$cl->SetMatchMode(SPH_MATCH_ALL);
				break;
		}

		if( count($filtervals) )
			$cl->SetFilter ( $filter, $filtervals );
		if( @$groupby )
			$cl->SetGroupBy ( $groupby, SPH_GROUPBY_ATTR, @$groupsort );

		if( @$distinct )
			$cl->SetGroupDistinct ( $distinct );

		$cl->SetLimits($this->items_offset(), $this->items_per_page());
		$cl->SetMaxQueryTime(bors()->user() ? 10000 : 3000);

		$f = $this->f();
		if($f && $f[0])
			$cl->SetFilter('forum_id', $f);

		if($disabled = airbase_forum_forum::disabled_ids_list())
			$cl->SetFilter('forum_id', array_merge($disabled, array(191)), true);
		else
			$cl->SetFilter('forum_id', array(191), true);

		if($this->u())
		{
			$user = bors_find_first('balancer_board_user', array('username' => $this->u()));
			if($user)
			$cl->SetFilter('owner_id', array($user->id()));
		}

		if($this->t())
			$cl->SetFilter('topic_id', array(intval($this->t())));

		if($y = $this->y())
		{
			if(preg_match('/^(\d+)\-(\d+)$/', trim($y), $m))
			{
				$time_begin = strtotime("{$m[1]}-01-01 00:00:00");
				$time_end   = strtotime("{$m[2]}-12-31 23:59:59");
			}
			else
			{
				$y = intval($y);
				$time_begin = strtotime("$y-01-01 00:00:00");
				$time_end   = strtotime("$y-12-31 23:59:59");
			}
			$cl->SetFilterRange('create_time', $time_begin, $time_end);
		}

		switch($this->s())
		{
			case 'c':
				$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'create_time' );
				break;
			case 'u':
				$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'modify_time' );
				break;
			case 'co':
				$cl->SetSortMode (SPH_SORT_ATTR_ASC, 'create_time' );
				break;
			case 'r':
				$cl->SetSortMode(SPH_SORT_RELEVANCE);
			case 't':
			default:
				$cl->SetSortMode(SPH_SORT_TIME_SEGMENTS, 'timestamp');
				break;
		}

		$user = bors()->user();
		if(!$user || !$user->is_coordinator())
			$cl->SetFilter('is_public', array(0), true);

		if($ranker)
			$cl->SetRankingMode ( $ranker );

		$cl->SetArrayResult ( true );
		$res = $cl->Query ( $this->q(), $index );
//if(config('is_developer'))		print_dd($res);

		if($res === false)
			$data['error'] = $cl->GetLastError();
		else
		{
			if ( $cl->GetLastWarning() )
				$data['warning'] = $cl->GetLastWarning();

			$data['q'] = $this->q();
			$data['res'] = &$res;

//			print_dd($res);

			$opts = array (
				'before_match'		=> '<b style="color: brown">',
				'after_match'		=> '</b>',
				'chunk_separator'	=> ' ... ',
				'limit'				=> 500,
				'around'			=> 5,
			);

			$opts['exact_phrase'] = $this->x() == 'e';

//if(config('is_developer')) print_dd($res);

			if(empty($res['matches']))
				return false;

			$post_ids = array();
			$topic_ids = array();

			$titles		= array();
			$contents	= array();

			for($i=0; $i<count($res['matches']); $i++)
			{
				$x = &$res['matches'][$i];
				$id = floor($x['id'] / 1000);

				if($cn = @$x['attrs']['class_name'])
				{
					$cid = $cn;
				}
				else
				{
					$cid = @$x['attrs']['class_id'];
					if($cid == 1)
						$cid = 87;
					if($cid == 2)
						$cid = 89;
					if($cid == 15)
						$cid = 179;
				}
				if(@$x['attrs']['is_content'])
					$contents[$cid][] = $id;
				else
					$titles[$cid][] = $id;
			}

//print_dd($res);
//print_dd($titles);
//var_dump($titles, $contents);

			$this->_data['posts'] = array();
			foreach($contents as $class_id => $ids)
			{
				$objects = bors_find_all($class_id, array('id IN' => array_unique($ids), 'by_id' => true));

				foreach($ids as $id)
					$this->_data['posts'][$id] = $objects[$id];
			}

			$this->_data['topics'] = array();
			foreach($titles as $class_id => $ids)
			{
				if(!$class_id)
				{
					continue;
				}

				$objects = bors_find_all($class_id, array('id IN' => array_unique($ids), 'by_id' => true));
//if(config('is_developer')) { echo $class_id; print_r($ids); print_dd($objects); }
				foreach($ids as $id)
					if(!empty($objects[$id]))
						$this->_data['topics'][$id] = $objects[$id];

			}

			$posts = &$this->_data['posts'];

			$docs = array();

			$loop = 0;
			foreach($posts as $pid => $p)
				$docs[$loop++] = strip_tags($p->body());

			if($contents)
			{
				$exc = $cl->BuildExcerpts($docs, 'posts', $this->q(), $opts);

				if(!$exc)
					echo $data['error'] = $cl->GetLastError();
				else
				{
					$loop = 0;
					foreach($posts as $pid => $p)
						$posts[$pid]->set_attr('body', $exc[$loop++]);
				}
			}
		}

		return false;
	}

	function body_data()
	{
		return $this->_data;
	}

	function total_items() { return $this->_data['res']['total']; }
	function id() { return true; }

	private function gets($list)
	{
		$result = array();
		foreach($list as $key => $val)
			if(!empty($val) && (!is_array($val) || !empty($val[0])))
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

	function url_ex($page = NULL)
	{
		if(!$page)
			$page = $this->args('page');

		return $_SERVER['REQUEST_URI'].$this->gets(array(
			'q' => $this->q(),
			'f' => $this->f(),
			's' => $this->s(),
			't' => $this->t(),
			'u' => $this->u(),
			'x' => $this->x(),
			'w' => $this->w(),
			'y' => $this->y(),
			'p' => $page > 1 ? $page : NULL,
		));
	}

	function set_x($value) { $_GET['x'] = $value; }
}
