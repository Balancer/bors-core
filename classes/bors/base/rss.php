<?php

class base_rss extends base_page
{
	function render_engine() { return 'base_rss'; }
	function output_charset() { return 'utf-8'; }

	function rss_strip() { return 1024;}
	function rss_source_url() { return '/xxx/';}
	function rss_title() { return $this->title(); }
	function rss_description() { return $this->description(); }

	function render($rss)
	{
		require_once(config('feedcreator_include'));

//		$type = "ATOM1.0";
		$type = "RSS2.0";

		$feed = new UniversalFeedCreator(); 
		$feed->useCached($type, '/tmp/bors-rss-'.md5($rss->url()).'.xml', config('rss_static_lifetime'));
		$feed->encoding = 'UTF-8';
		$feed->title = $rss->rss_title();
		$feed->description = $rss->rss_description();
		$feed->link = $rss->main_url();
		$feed->syndicationURL = $rss->url();

//		$feed->descriptionTruncSize = 500;
		$feed->descriptionHtmlSyndicated = true;

/*		$image = new FeedImage(); 
		$image->title = "dailyphp.net logo"; 
		$image->url = "http://www.dailyphp.net/images/logo.gif"; 
		$image->link = "http://www.dailyphp.net"; 
		$image->description = "Feed provided by dailyphp.net. Click to visit."; 
		$feed->image = $image; 
*/
		// get your news items from somewhere, e.g. your database: 

		foreach($rss->rss_items() as $o)
		{
		    $item = new FeedItem();
	    	$item->title = $rss->item_rss_title($o);
		    $item->link = $rss->item_rss_url($o);
		    $item->guid = $rss->item_rss_guid($o);

//			$item->description = $rss->rss_body($o, $rss->rss_strip());
			if(($desc = $rss->item_rss_body($o, $rss)))
				$item->description = $desc;
			$item->descriptionHtmlSyndicated = true;
			$item->date = intval($o->create_time());
			$item->source = $rss->rss_source_url();
			$owner = $o->get('owner');
			if($owner)
				$item->author = $owner->title();

			$item->category = $rss->item_rss_keywords_string($o);
/*
			if($image = object_property($o, 'image'))
			{
	 			$item->enclosure = new EnclosureItem();
	 			$thumb = $image->thumbnail('300x300');
				$item->enclosure->url = $thumb->url();
				$item->enclosure->length = $thumb->size();
				$item->enclosure->type = $image->mime_type();
			}
*/
			$feed->addItem($item); 
		}

		$result = $feed->createFeed($type);
		header("Content-Type: ".$feed->contentType."; charset=".$feed->encoding);
		return $result;
	}

	function item_rss_title($item) { return $item->rss_title(); }
	function item_rss_body($item, $rss) { return $rss->rss_body($item, $rss->rss_strip()); }
	function item_rss_url($item) { return $item->url(); }
	function item_rss_guid($item) { return $item->url(); }
	function item_rss_keywords_string($item) { return object_property($item, 'keywords_string'); }

	function rss_body($object, $strip = 0)
	{
		if(($tpl = $this->body_template()) && !preg_match('!/classes/bors/base/page.html$!', $tpl))
		{
			require_once('engines/smarty/assign.php');
			return template_assign_data($tpl, array('this' => $object));
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
