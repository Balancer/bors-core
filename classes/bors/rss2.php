<?php

use \Suin\RSSWriter\Feed;
use \Suin\RSSWriter\Channel;
use \Suin\RSSWriter\Item;

class bors_rss2 extends bors_rss
{
	function render_engine() { return 'bors_rss2'; }

	function render($rss)
	{
		if(!class_exists('\\Suin\\RSSWriter\\Feed'))
			bors_throw("Use:<br/>\ncomposer require suin/php-rss-writer=*");

//		$type = "ATOM1.0";
		$type = "RSS2.0";

		$feed = new Feed();

		$channel = new bal_suin_rsswriter_channel();
		$channel
			->title($rss->title())
			->description($rss->description())
			->url($rss->main_url())
			->appendTo($feed);

		$xml = $channel->asXML();

//		$feed->useCached($type, '/tmp/bors-rss-'.md5($rss->url()).'.xml', config('rss_static_lifetime'));
//		$feed->encoding = 'UTF-8';
//		$feed->link = $rss->main_url();
//		$feed->syndicationURL = $rss->url();

//		$feed->descriptionTruncSize = 500;
//		$feed->descriptionHtmlSyndicated = true;

//		if($xmlns = $rss->get('xmlns_append'))
//			$feed->xmlns_append = $xmlns;

		if($feed_image = $rss->get('image'))
		{
			$channel->image($feed_image);
			$channel->yandex_logo($feed_image);
		}

/*
		foreach(explode(' ', 'copyright docs language') as $n)
			if($x = $rss->get($n))
				$feed->$n = $x;
*/
		foreach($rss->rss_items() as $o)
		{
			$item = new bal_suin_rsswriter_item();
			$item->title($rss->item_title($o))
				->url($rss->item_url($o))
				->guid($rss->item_guid($o));

//			$item->description = $rss->rss_body($o, $rss->rss_strip());
			if(($desc = $rss->item_body($o, $rss)))
				$item->description($desc);

//			$item->descriptionHtmlSyndicated = true;
			$item->pubDate(intval($o->create_time()));//$rss->item_rss_gmtime($o);
//			$item->source = $rss->rss_source_url();
//			$owner = $o->get('owner');
//			if($owner)
//				$item->author = $owner->title();

			if($kws = $rss->item_keywords_string($o))
				$item->category($kws);

//			if($add = $rss->item_additional($o, $rss))
//				$item->additionalElements = $add;

			if($e = $rss->item_enclosure($o))
	 			$item->enclosure($e['url'], intval(@$e['size']), $e['type']);
			elseif($image = $this->item_image($o))
			{
				$thumb = $image->thumbnail('300x300');
				// <link rel="enclosure" type="image/jpeg" href="image_url_here" />
	 			$item->enclosure($thumb->url(), $thumb->size(), $image->mime_type());
			}

			if($rss->get('is_yandex'))
				$item->yandex_full($o->html());

			$item->appendTo($channel);
		}

		$result = (string)$feed;
		@header("Content-Type: ".$feed->contentType."; charset=".$feed->encoding);
		return $result;
	}

	function item_image($object) { return $object->get('image'); }
}

class bal_suin_rsswriter_channel extends Channel
{
	private $image = NULL;
	private $yandex_logo = NULL;

	function image($image)
	{
		$this->image = $image;
		return $this;
	}

	function yandex_logo($image)
	{
		$this->yandex_logo = $image;
		return $this;
	}

	function asXML()
	{
		$xml = parent::asXML();

		if($this->image)
		{
/*
			<image>
				<url>http://example.com/rss_banner.gif</url>
				<title>Example.com</title>
				<link>http://example.com/</link>
				<width>111</width>
				<height>32</height>
				<description>Example.com features tips, tricks, and bookmarks on web development</description>
			</image>
*/

			$img = $xml->addChild('image');
			$img->addChild('url', $this->image->thumbnail('300x300(up)')->url());

			if($this->title)
				$img->addChild('title', $this->title);

			if($this->url)
				$img->addChild('link', $this->url);

			if($this->description)
				$img->addChild('description', $this->description);
		}

		$NS = array(
		   'g' => 'http://base.google.com/ns/1.0',
		   'y' => 'http://news.yandex.ru',
		);

//		foreach($NS as $prefix => $name)
//			$this->registerXPathNamespace($prefix, $name);

		$NS = (object) $NS;

		if($this->yandex_logo)
		{
			$xml->addChild('yandex:logo', $this->yandex_logo->thumbnail('300x300(up)')->url(), $NS->y);
			$xml->addChild('yandex:logo', $this->yandex_logo->thumbnail('300x300(up,fillpad)')->url(), $NS->y)
				->addAttribute('type', 'square');
		}

		return $xml;
	}
}

class bal_suin_rsswriter_item extends Item
{
	private $yandex_full = NULL;

	function yandex_full($text)
	{
		$this->yandex_full = $text;
		return $this;
	}

	function asXML()
	{
		$xml = parent::asXML();

		if($this->yandex_full)
		{
			$NS = array(
			   'y' => 'http://news.yandex.ru',
			);

			$NS = (object) $NS;

			$xml->addChild('yandex:full-text', $this->yandex_full, $NS->y);
		}

		return $xml;
	}
}
