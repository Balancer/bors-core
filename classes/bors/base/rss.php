<?php

class base_rss extends base_page
{
	function render_engine() { return 'base_rss'; }
	
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
		$rss->title = dc($obj->rss_title());
		$rss->description = dc($obj->rss_description());
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
		    $item = &new FeedItem();
	    	$item->title = dc($o->rss_title());
		    $item->link = $o->url();
			
			$item->description = dc($obj->rss_body($o, $obj->rss_strip()));
			$item->date = $o->create_time(); 
			$item->source = $obj->rss_source_url();
			$owner = $o->owner();
			if($owner)
				$item->author = dc($owner->title());
							     
			$rss->addItem($item); 
		} 
								
		$result = $rss->createFeed("RSS2.0");
		header("Content-Type: ".$rss->contentType."; charset=".$rss->encoding);
		return $result;
	}
	
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
}
