<?php

class blib_design
{
	function paginated_data($current_page, $total_pages, $limit)
	{
		require_once('inc/design/page_split.php');

		$pages = array();
		$total_pages = intval($total_pages);
//		$current_page = $show_current ? $page : -1;

		if($total_pages < 2)
			return $pages;

		list($start, $stop) = pages_start_stop_calculate($current_page, $total_pages, $limit);

		$pages[] = array('page' => 1, 'is_current' => 1==$current_page);

		if($start > 2)
			$pages[] = "…";

		for($i = $start; $i <= $stop; $i++)
			$pages[] = array('page' => $i, 'is_current' => $i==$current_page);

		if($stop < $total_pages - 1)
			$pages[] = "…";

		$pages[] = array('page' => $total_pages, 'is_current' => $total_pages==$current_page);

//		print_dd($pages);

		return $pages;
	}

	function paginated_dula($object, $args = array())
	{
		$html = array();

		$li_current_class = blib_css::mk_class($args, 'li_class_current');

		foreach(self::paginated_data(max(1, $object->page()), $object->total_pages(), $object->items_around_page()) as $x)
		{
			if(!is_array($x))
			{
				$html[] = "<li>{$x}</li>";
				continue;
			}

			$html[] = "<li".($x['is_current'] ? $li_current_class : '')
				."><a href=\"".$object->url($x['page'])."\">{$x['page']}</a></li>";
		}

		if(!$html)
			return '';

		return "<div".blib_css::mk_class($args, 'div_class').">"
			.defval($args, 'before')."<ul>".join('', $html)."</ul></div>";
	}
}
