<?php

class bors_lib_bb
{
	static $bb_map_glob = array(
		'a' => array('bb' => 'url', 'main_attr' => 'href', 'urls' => 'href'),
		'img' => array('bb' => 'img', 'main_attr' => 'src', 'urls' => 'src', 'no_ending' => true, 'lcml0_style' => true),
		'br' => array('bb' => '', 'after_cr' => true),
		'button' => array('bb' => ''),
		'div' => array('bb' => '', 'before_cr' => true, 'after_cr' => true),
		'em' => array('bb' => 'i'),
		'font' => array('bb' => 'html_font', 'save_attrs' => true),
		'form' => array('bb' => ''),
		'i' => array('bb' => 'i'),
		'b' => array('bb' => 'b'),
		'center' => array('bb' => 'center'),
		'embed' => array('bb' => 'embed', 'save_attrs' => true, 'urls'=>'src'),
		'h2' => array('bb' => 'h', 'before_cr' => true, 'after_cr' => true),
		'html' => array('bb' => ''),
		'iframe' => array('bb' => 'iframe', 'save_attrs' => true, 'urls'=>'src'),
		'input' => array('bb' => ''),
		'like' => array('bb' => ''),
		'noindex' => array('bb' => ''),
		'p' => array('bb' => '', 'before_cr' => true, 'after_cr' => true),
		'param' => array('bb' => 'param', 'save_attrs' => true),
		'plusone' =>  array('bb' => ''),
		'source' => array('bb' => 'html_source', 'save_attrs' => true, 'urls' => 'src'),
		'span' => array('skip_all' => true),
		'strong' => array('bb' => 'b'),
		'style' => array('bb' => '', 'after_cr' => true),
		'script' => array('bb' => '', 'skip_content' => true),
		'table' => array('bb' => 'table'),
		'td' => array('bb' => 'td'),
		'tr' => array('bb' => 'tabtr'),
		'u' => array('bb' => 'u'),
		'video' => array('bb' => 'html_video', 'save_attrs' => true, 'urls' => 'poster'),
		'wbr' => array('bb' => ''),
	);

	static function from_dom($element, $base_url = NULL, $bbmap = array())
	{
		if(!$element)
		{
			debug_hidden_log('dom2bb', 'Empty element to from_dom()');
			return '';
		}
/*
		$dom= new DOMDocument('1.0', 'utf-8');
		$dom->loadXML( "<html></html>" );
		$dom->documentElement->appendChild($dom->importNode($element, true));

		$xpath = new DOMXPath($dom);

		foreach(array(
			'//script',
			'//style',
		) as $query)
			foreach($xpath->query($query, $element) as $node)
				$node->parentNode->removeChild($node);

		$element = $dom->documentElement;
*/
		$tag_name = $element->tagName;
		$bb = array_merge(
			defval(self::$bb_map_glob, $tag_name, array()),
			defval($bbmap, $tag_name, array())
		);

		if(empty($bb))
		{
			debug_hidden_log('lcml-need-append-data', "from_dom: unknown tag ".$tag_name.' for '.bors_lib_dom::element_html($element));
			echo "unknown tag ".$tag_name."\n";
		}

		$bb_code = "";
		if(defval($bb, 'before_cr'))
			$bb_code .= "\n\n";

		if(($urls = defval($bb, 'urls')) && $base_url)
		{
			$udata = parse_url($base_url);
			$usite = $udata['scheme'].'://'.$udata['host'];

			foreach(explode(',', $urls) as $uattr)
			{
				$u = $element->getAttribute($uattr);
				if(!$u)
				{
					debug_hidden_log('_bb-parse-need-attention', 'Empty uattr for '.$urls);
					continue;
				}

				if($u[0] == '/')
					$u = $usite . $u;
				elseif(!preg_match('!^\w+://!', $u))
					$u = $base_url . $u;
				$element->setAttribute($uattr, $u);
			}
		}

		if($main_attr = defval($bb, 'main_attr'))
		{
			if(defval($bb, 'lcml0_style'))
				$attrs = ' url="'.$element->getAttribute($main_attr).'"';
			else
				$attrs = '='.$element->getAttribute($main_attr);
		}
		elseif(defval($bb, 'save_attrs'))
		{
			$attrs = array();
			foreach($element->attributes as $attrName => $attrNode)
				$attrs[] = "$attrName=\"".$element->getAttribute($attrName)."\"";
			$attrs = " ".join(" ", $attrs);
		}
		else
			$attrs = '';

		if($append = defval($bb, 'append_attrs'))
			$attrs .= ' '.$append;

		if($bbtag = defval($bb, 'bb'))
			if($bbtag != 'url' || $element->getAttribute('href'))
				$bb_code .= " [{$bbtag}{$attrs}]";

		if(!defval($bb, 'skip_content'))
		{
			$children = $element->childNodes;
			foreach($children as $child)
			{
				if($child instanceof DOMElement)
					$bb_code .= self::from_dom($child, $base_url, $bbmap);
				else
					$bb_code .= trim($child->nodeValue);
			}
		}

		if($bbtag && !defval($bb, 'no_ending'))
			if($bbtag != 'url' || $element->getAttribute('href'))
				$bb_code .= "[/$bbtag] ";

		if(defval($bb, 'after_cr'))
			$bb_code .= "\n";

		return $bb_code;
	}
}
