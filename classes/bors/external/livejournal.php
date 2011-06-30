<?php

class bors_external_livejournal extends bors_object
{
	static function content_extract($url)
	{
		$html = bors_lib_http::get_cached($url, 7200);
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);

		$tags = array();
		foreach($xpath->query("//div[@class='ljtags']/a") as $elem)
			$tags[] = $elem->nodeValue;

		if($els = $xpath->query("//table/tr/td/b[.='Метки данной записи']/../.."))
		{
			$el = $els->item(0);
			$dom2= new DOMDocument('1.0', 'utf-8');
			$dom2->loadXML( "<html></html>" );
			$dom2->documentElement->appendChild($dom2->importNode($el, true));
			$xpath2 = new DOMXPath($dom2);

			foreach($xpath2->query("//a") as $elem)
				$tags[] = $elem->nodeValue;

			if($el && $el->parentNode)
				$el->parentNode->removeChild($el);
		}

		$body = $xpath->query("//div[@class='entry-content']")->item(0);
		if(!$body)
			$body = $xpath->query("//div[@id='content-wrapper']/div")->item(1);

		foreach(array(
			'//p[@class="entry-footer"]',
			"//div[@class='ljtags']",
		) as $query)
			foreach($xpath->query($query) as $node)
				$node->parentNode->removeChild($node);

		$bbcode = trim(bors_lib_bb::from_dom($body, $url, array(
			'img' => array('bb' => 'img', 'main_attr' => 'src', 'urls' => 'src', 'no_ending' => true, 'lcml0_style' => true, 'append_attrs' => '200x150'),
		)));

		// Хаки
		$bbcode = str_replace('</lj-like>', '', $bbcode);

		$bbcode = preg_replace("/^\s+$/m", "", $bbcode);
		$bbcode = preg_replace("/\n{2,}/", "\n\n", $bbcode);
		$len = bors_strlen($bbcode);
		$bbcode = bors_close_bbtags(clause_truncate_ceil($bbcode, 1500));
		if($len >= 1500)
			$bbcode .= "\n\n[url={$url}]".ec('… дальше »»»[/url]');

//		print_dd($bbcode); exit();

		$meta = bors_lib_html::get_meta_data($html);
		$title = $meta['title'];

//var_dump($bbcode); exit();

		$bbshort = "[b][url={$url}]{$title}[/url][/b]

{$bbcode}
// ".ec("Подробнее: ").bors_external_feeds_entry::url_host_link($url);

		return compact('tags', 'bbshort');
	}

	static function post($params)
	{
		$title = defval($params, 'title');
		$html  = defval($params, 'html');
		$keywords = defval($params, 'keywords', array());
		$description = defval($params, 'decription');
		$time = defval($params, 'time', time());
		$user = defval($params, 'user');
		$object = defval($params, 'object');

		$blog = bors_find_first('bors_users_blog', array(
			'bors_user_id' => $user->id(),
			'blog' => 'livejournal.com',
			'active' => true,
		));

		if(empty($blog))
			return;

		if($description)
			$title .= " ($description)";

		$client = new xmlrpc_client("/interface/xmlrpc", "www.livejournal.com", 80);

	    $params = new xmlrpcval( array(
			'username' => new xmlrpcval($blog->login(), 'string'),
			'password' => new xmlrpcval($blog->password(), 'string'),
			'ver' => new xmlrpcval('1', 'string'),
			'lineendings' => new xmlrpcval('unix', 'string'),
			'event' => new xmlrpcval($html, 'string'),
			'subject' => new xmlrpcval($title, 'string'),
			'year' => new xmlrpcval(date('Y', $time), 'int'),
			'mon' => new xmlrpcval(date('m', $time), 'int'),
			'day' => new xmlrpcval(date('d', $time),'int'),
			'hour' => new xmlrpcval(date('H', $time), 'int'),
			'min' => new xmlrpcval(date('i', $time),'int'),
			'props' => new xmlrpcval( array(
//				'opt_backdated' => new xmlrpcval(1, 'boolean'),
				'taglist' => new xmlrpcval(join(', ', $keywords), 'string'),
				'opt_preformatted' => new xmlrpcval(1, 'boolean'),
			), 'struct'),
		),'struct');

		$msg = new xmlrpcmsg('LJ.XMLRPC.postevent');
		$msg->addparam($params);
		$client->setDebug(0);
		$result = $client->send($msg);
		if ($result->faultCode() != 0)
		{
			debug_hidden_log('error_blog', 'Ошибка добавления в ЖЖ: ' . $result->faultString());
			return;
		}

		$v = $result->value();

		$itemid_xml = $v->structMem('itemid');
		$itemid = $itemid_xml->scalarVal();

		bors_new('bors_users_blogs_map', array(
			'target_class_id' => object_property($object, 'class_id'),
			'target_object_id' => object_property($object, 'id'),
			'blog_class_id' => 1,
			'blog_object_id' => $itemid,
		));

		return $itemid;
	}
}
