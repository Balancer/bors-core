<?php

	function pages_select($page, $current_page, $total_pages)
	{
		$pages = array();
		$total_pages = intval($total_pages);

		$q = "";
		if(!empty($_GET))
			foreach($_GET as $key => $value)
				$q .= (($q=="") ? '?' : '&').urlencode($key)."=".urlencode($value);

		if($total_pages > 1)
		{
			$last = 0;
			for($i=1; $i <= $total_pages; $i++)
			{
				if(!check_page($i, $current_page, $total_pages))
					continue;

				if($last != $i-1)
					$pages[]=' ... ';

				$last = $i;

				if(is_object($page))
					$p = $page->url_ex($i);
				else
				{
					$p = $page;
					if($i > 1)
						$p .= "page$i/";
				}

				$pages[] = "<a href=\"$p$q\" class=\"".(($i==$current_page)?'current_page':'select_page')."\">$i</a>";
			}
		}

		return $pages;
	}

	function pages_start_stop_calculate($current_page, $total_pages, $limit)
	{
		if($total_pages <= $limit) // 1, 2, 3
			return array(2, $total_pages - 1);

		$limit_down = intval(($limit-4.5)/2);
		$limit_up = intval(($limit-4)/2);

		if($current_page < 0 || $current_page - $limit_down >= $total_pages - $limit + 2) // 1 .. 4, 5, |6|, 7, 8, 9
			return array($total_pages - $limit + 3, $total_pages - 1);

		if($current_page + $limit_up <= $limit - 2) // 1 2 3 4 5 .. 9
			return array(2, $limit - 2);

		return array(max(2, $current_page - $limit_down), min($total_pages - 1, $current_page + $limit_up));
	}

	function pages_show($obj, $total_pages, $limit,
		$show_current = true, $current_page_class = 'current_page', $other_page_class = 'select_page',
		$use_items_count = false, $per_page = 0, $total_items = 0)
	{
		$pages = array();
		$total_pages = intval($total_pages);
//		$current_page = $show_current ? $obj->args('page', $show_current ? 1 : NULL) : -1;
		$current_page = $show_current ? $obj->page() : -1;

		if($total_pages < 2)
			return $pages;

		if(!empty($_GET))
			$q = '?'.http_build_query($_GET);
		else
			$q = '';

		list($start, $stop) = pages_start_stop_calculate($current_page, $total_pages, $limit);

		$pages[] = get_page_link($obj, 1, 1==$current_page ? $current_page_class : $other_page_class, $q, $use_items_count, $per_page, $total_items, 1==$current_page);

		if(is_object($obj))
		{
			$b = @$obj->attr['___pagination_item_before_current'];
			$a = @$obj->attr['___pagination_item_after'];
		}

		if($start > 2)
			$pages[] = @$b."<span class=\"skip\">".ec('…')."</span>".@$a;

		for($i = $start; $i <= $stop; $i++)
			$pages[] = get_page_link($obj, $i, $i==$current_page ? $current_page_class : $other_page_class, $q, $use_items_count, $per_page, $total_items, $i==$current_page);

		if($stop < $total_pages - 1)
			$pages[] = @$b."<span class=\"skip\">".ec('…')."</span>".@$a;

		$pages[] = get_page_link($obj, $total_pages, $total_pages==$current_page ? $current_page_class : $other_page_class, $q, $use_items_count, $per_page, $total_items, $total_pages==$current_page);

//		for($i = $total_pages - intval($limit/2) + 1; $i <= $total_pages; $i++)
//			$pages[] = get_page_link($obj, $i, $i==$current_page ? $current_page_class : $other_page_class, $q);

//		print_r($pages);

		return $pages;
	}

	function get_page_link($obj, $page_num, $class="", $q = "", $use_items_count = false, $per_page = 0, $total_items = 0, $is_current=false)
	{
		if(is_object($obj))
		{
			$p = $obj->url_ex($page_num);
			if(preg_match("!\?!", $p))
				$q = "";
		}
		else
		{
			$p = $obj;
			if($page_num > 1)
				$p .= "page$page_num/";
		}

		if($use_items_count)
		{
			$start = ($page_num-1)*$per_page + 1;
			$stop  = $start + $per_page - 1;
			if($stop > $total_items)
				$stop = $total_items;

			$title = "{$start}-{$stop}";
		}
		else
			$title = $page_num;

		$link = "<a href=\"$p$q\"".($class? " class=\"$class\"" : "" ).">$title</a>";
		if(is_object($obj))
		{
			if($is_current && ($b = @$obj->attr['___pagination_item_before_current']))
				$link = $b.$link;
			elseif($b = @$obj->attr['___pagination_item_before'])
				$link = $b.$link;

			if($a = @$obj->attr['___pagination_item_after'])
				$link = $link.$a;
		}
		return $link;
	}

	function check_page($p, $current_page, $total_pages)
	{
		if($p < 3)					return true;
		if($p > $total_pages - 2)	return true;
		if(abs($p - $current_page) <= 5)	return true;
		if($p == $current_page-6 && $p == 3) return true;
		if($p <= 14 && $current_page < 10) return true;

		if($p == $current_page+6 && $p == $total_pages-2) return true;
		if($p >= $total_pages-13 && $current_page > $total_pages-9) return true;

		return false;
	}
