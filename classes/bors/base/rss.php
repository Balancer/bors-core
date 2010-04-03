<?php

class base_rss extends base_page
{
	function render_engine() { return 'base_rss'; }
	function output_charset() { return 'utf-8'; }

	function rss_strip() { return 1024;}
	function rss_source_url() { return '/xxx/';}
	function rss_title() { return $this->title(); }
	function rss_description() { return $this->description(); }

	function render($obj)
	{
		include("feedcreator.class.php"); 

		$rss = &new UniversalFeedCreator(); 
		$rss->useCached("RSS2.0", '/tmp/rss-'.md5($obj->url()).'.xml', config('rss_static_lifetime'));
		$rss->encoding = 'utf-8'; 
		$rss->title = $obj->rss_title();
		$rss->description = $obj->rss_description();
		$rss->link = $obj->rss_url();
		$rss->syndicationURL = $obj->url(); 

/*		$image = new FeedImage(); 
		$image->title = "dailyphp.net logo"; 
		$image->url = "http://www.dailyphp.net/images/logo.gif"; 
		$image->link = "http://www.dailyphp.net"; 
		$image->description = "Feed provided by dailyphp.net. Click to visit."; 
		$rss->image = $image; 
*/
		// get your news items from somewhere, e.g. your database: 

		foreach($obj->rss_items() as $o)
		{
		    $item = new FeedItem();
	    	$item->title = $obj->item_rss_title($o);
		    $item->link = $obj->item_rss_url($o);

//			$item->description = $obj->rss_body($o, $obj->rss_strip());
			if(($desc = $obj->item_rss_body($o, $obj)))
				$item->description = $desc;
			$item->date = intval($o->create_time());
			$item->source = $obj->rss_source_url();
			$owner = $o->owner();
			if($owner)
				$item->author = $owner->title();

			$rss->addItem($item); 
		}

		$result = $rss->createFeed("RSS2.0");
		header("Content-Type: ".$rss->contentType."; charset=".$rss->encoding);
		return $result;
	}

	function item_rss_title($item) { return $item->rss_title(); }
	function item_rss_body($item, $rss) { return $rss->rss_body($item, $rss->rss_strip()); }
	function item_rss_url($item) { return $item->url(); }

	function rss_body($object, $strip = 0)
	{
		if($this->body_template())
		{
			require_once('engines/smarty/assign.php');
			return template_assign_data($this->body_template(), array('this' => $object));
		}

		$html = $object->rss_body();
		if(!$strip || strlen($html) <= $strip)
			return $html;

		include_once("inc/texts.php");
		$html = strip_text($html, $strip);
		$html .= "<br /><br /><a href=\"".$object->url(1).ec("\">Дальше »»»");

		return $html;
	}

	//TODO: Реализовать статическое кеширование файлов, отличных от index.html / text/html
	function cache_static() { return 0; }
	function index_file() { return 'index.xml'; }
	function use_temporary_static_file() { return false; }
}
