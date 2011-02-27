<?php

class bors_lib_bb
{
	static $bb_map_glob = array(
		'a' => array('bb' => 'url', 'main_attr' => 'href'),
		'div' => array('bb' => '', 'before_cr' => true, 'after_cr' => true),
		'embed' => array('bb' => 'html_embed', 'save_attrs' => true),
		'h2' => array('bb' => 'h', 'before_cr' => true, 'after_cr' => true),
		'p' => array('bb' => '', 'before_cr' => true, 'after_cr' => true),
		'source' => array('bb' => 'html_source', 'save_attrs' => true),
		'span' => array('skip_all' => true),
		'video' => array('bb' => 'html_video', 'save_attrs' => true),
	);

	static function from_dom($element, $bbmap = array())
	{
		$tag_name = $element->tagName;
		$bb = array_merge(
			defval(self::$bb_map_glob, $tag_name, array()),
			defval($bbmap, $tag_name, array())
		);

		if(empty($bb))
			debug_hidden_log('lcml-need-append-data', "from_dom: unknown tag ".$tag_name);

		$bb_code = "";
		if(defval($bb, 'before_cr'))
			$bb_code .= "\n";

		if($main_attr = defval($bb, 'main_attr'))
			$attrs = '='.$element->getAttribute($main_attr);
		elseif(defval($bb, 'save_attrs'))
		{
			$attrs = array();
			foreach($element->attributes as $attrName => $attrNode)
				$attrs[] = "$attrName=\"".htmlspecialchars($element->getAttribute($attrName))."\"";
			$attrs = " ".join(" ", $attrs);
		}
		else
			$attrs = '';

		if($bbtag = defval($bb, 'bb'))
			$bb_code .= "[{$bbtag}{$attrs}]";

		$children = $element->childNodes;
		foreach($children as $child)
		{
			if($child instanceof DOMElement)
				$bb_code .= self::from_dom($child, $bbmap);
			else
				$bb_code .= trim($child->nodeValue);
		}

		if($bbtag)
			$bb_code .= "[/$bbtag]";

		if(defval($bb, 'after_cr'))
			$bb_code .= "\n";

		return $bb_code;
	}
}
