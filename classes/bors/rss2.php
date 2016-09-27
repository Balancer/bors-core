<?php

use \Suin\RSSWriter\Feed;
use \Suin\RSSWriter\Channel;
use \Suin\RSSWriter\Item;

if(!class_exists('\\Suin\\RSSWriter\\Feed'))
	throw new Exception("Not installed \Suin\RSSWriter. Try to use: <br/>\ncomposer require suin/php-rss-writer");

class bors_rss2 extends bors_rss
{
	function render_engine() { return 'bors_rss2'; }

	function render($rss)
	{
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

		if($feed_image_square = $rss->get('image_square'))
			$channel->yandex_logo_square($feed_image_square);

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
				->guid($rss->item_guid($o), true);

//			$item->description = $rss->rss_body($o, $rss->rss_strip());
			if(($desc = $rss->item_body($o, $rss)))
				$item->description($desc);

//			$item->descriptionHtmlSyndicated = true;
			$item->pubDate(intval($o->create_time()));//$rss->item_rss_gmtime($o);
//			$item->source = $rss->rss_source_url();

			$author = $rss->item_author($o);
			if($author)
				$item->author($author);

			foreach($rss->item_categories($o) as $cat)
				$item->category($cat);

//			if($add = $rss->item_additional($o, $rss))
//				$item->additionalElements = $add;

			if($e = $rss->item_enclosure($o))
	 			$item->enclosure($e['url'], intval(@$e['size']), $e['type']);
			elseif($image = $rss->item_image($o))
			{
				$thumb = $image->thumbnail('300x300');
				// <link rel="enclosure" type="image/jpeg" href="image_url_here" />
	 			$item->enclosure($thumb->url(), $thumb->size(), $image->mime_type());
			}

			if($rss->get('is_yandex'))
				$item->yandex_full($o->html());

			if($g = $o->get('rss_yandex_genre'))
				$item->yandex_genre($g);

			$item->appendTo($channel);
		}

		$result = (string)$feed;
		@header("Content-Type: text/xml; charset=utf-8");
		return $result;
	}

	function item_image($object) { return $object->get('image'); }

	function item_author($object)
	{
		$a = $object->get('rss_author');
		if($a)
		{
			if(is_object($a))
				return $a->title();

			return $a;
		}

		return NULL;
	}

	function item_categories($item)
	{
		$cats = $item->get('rss_categories', []);
		return array_merge($cats, array_map('trim', explode(",", $this->item_keywords_string($item))));
	}
}

class bal_suin_rsswriter_channel extends Channel
{
	private $image = NULL;
	private $yandex_logo = NULL;
	private $yandex_logo_square = NULL;

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

	function yandex_logo_square($image)
	{
		$this->yandex_logo_square = $image;
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
			$xml->addChild('yandex:logo', $this->yandex_logo->url(), $NS->y);

		if($this->yandex_logo_square)
		{
			$xml->addChild('yandex:logo', $this->yandex_logo_square->url(), $NS->y)
				->addAttribute('type', 'square');
		}
		elseif($this->yandex_logo)
		{
			$xml->addChild('yandex:logo', $this->yandex_logo->thumbnail('300x300(up,fillpad)')->url(), $NS->y)
				->addAttribute('type', 'square');
		}

		return $xml;
	}
}

class bal_suin_rsswriter_item extends Item
{
	private $yandex_full = NULL;
	private $yandex_genre = NULL;

	function yandex_full($text)
	{
		$this->yandex_full = $text;
		return $this;
	}

	function yandex_genre($g)
	{
		$this->yandex_genre = $g;
		return $this;
	}

	function asXML()
	{
		$xml = parent::asXML();

		if($this->yandex_full)
		{
			$NS = [
			   'y' => 'http://news.yandex.ru',
			];

			$NS = (object) $NS;

			$xml->addChild('yandex:full-text', $this->yandex_full, $NS->y);
		}

		if($this->yandex_genre)
		{
			$NS = [
			   'y' => 'http://news.yandex.ru',
			];

			$NS = (object) $NS;

			$xml->addChild('yandex:genre', $this->yandex_genre, $NS->y);
		}

		return $xml;
	}
}
