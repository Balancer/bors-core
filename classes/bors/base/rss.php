<?php

// composer: openpsa/universalfeedcreator

class base_rss extends base_page
{
	function theme_class() { return NULL; } // Иначе у темы более высокий приоритет.

	function render_engine() { return 'bors_rss'; }
	function output_charset() { return 'utf-8'; }

	function rss_strip() { return 1024;}
	function rss_source_url() { return '/xxx/';}
	function rss_title() { return $this->title(); }
	function rss_description() { return $this->description(); }

//	function item_image() { return NULL; }

	function render($rss)
	{
		if(!class_exists('UniversalFeedCreator'))
			bors_throw("Use:<br/>\ncomposer require openpsa/universalfeedcreator=*");

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

		if($xmlns = $rss->get('xmlns_append'))
			$feed->xmlns_append = $xmlns;

		if($img_url = $rss->get('rss_image_src'))
		{
			$image = new FeedImage();
			$image->title = $rss->get('rss_image_title');
			$image->url = $img_url;
			$image->link = $rss->get('rss_image_link', $rss->main_url());
			$image->description = $rss->get('rss_image_description');
			$feed->image = $image; 
		}

		foreach(explode(' ', 'copyright docs language') as $n)
			if($x = $rss->get($n))
				$feed->$n = $x;

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
			$item->date = intval($o->create_time());//$rss->item_rss_gmtime($o);
			$item->source = $rss->rss_source_url();
			$owner = $o->get('owner');
			if($owner)
				$item->author = $owner->title();

			$item->category = $rss->item_rss_keywords_string($o);

			if($add = $rss->item_additional($o, $rss))
				$item->additionalElements = $add;

/*
			if($e = $rss->item_rss_enclosure($o))
			{
	 			$item->enclosure = new EnclosureItem();
				$item->enclosure->url = $e['url'];
				$item->enclosure->type = $e['type'];
				if($s = @$e['size'])
					$item->enclosure->length = $s;
			}
			if($image = $this->item_image($o, $rss))
			{
				print_dd($image);
				// <link rel="enclosure" type="image/jpeg" href="image_url_here" />
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
		@header("Content-Type: ".$feed->contentType."; charset=".$feed->encoding);
		return $result;
	}

	function item_rss_title($item) { return $item->rss_title(); }
	function item_rss_body($item, $rss) { return $rss->rss_body($item, $rss->rss_strip()); }
	function item_rss_full_html($item, $rss) { return $item->html(); }
	function item_rss_url($item) { return $item->url(); }
	function item_rss_guid($item) { return $item->url(); }
	function item_guid($item) { return $item->url(); }
	function item_rss_keywords_string($item) { return object_property($item, 'keywords_string'); }
	function item_keywords_string($item) { return strip_tags(object_property($item, 'keywords_string')); }
//	function item_rss_gmtime($item) { return time_local_to_gmt($item->create_time()); }

	function item_additional($item, $rss)
	{
		if($this->has_yandex_fields())
			return array('yandex:full-text' => htmlspecialchars($this->item_rss_full_html($item, $rss)));
		else
			return array();
	}

	function item_rss_enclosure($item) { return NULL; }
	function item_enclosure($item) { return NULL; }

	function rss_body($object, $strip = 0)
	{
		if(($tpl = $this->body_template()) && !preg_match('!/classes/bors/base/page.html$!', $tpl))
		{
//			require_once('engines/smarty/assign.php');
//			return template_assign_data($tpl, array('this' => $object));
			return bors_templates_smarty::fetch($tpl, array('this' => $object));
		}

		$html = $object->rss_body();
		if(!$strip || bors_strlen($html) <= $strip)
			return $html;

		include_once("inc/texts.php");
		$html = strip_text($html, $strip);
		$html .= "<br /><br /><a href=\"".$object->url().ec("\">Дальше »»»");

		return $html;
	}

	//TODO: Реализовать статическое кеширование файлов, отличных от index.html / text/html
	function cache_static() { return 0; }
	function index_file() { return 'index.xml'; }
	function use_temporary_static_file() { return false; }
	function has_yandex_fields() { return false; }
}
