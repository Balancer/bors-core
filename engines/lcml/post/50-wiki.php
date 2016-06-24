<?php
    function lcml_wiki($txt)
    {
        $txt = preg_replace_callback("!\[\[([^\[]+?)\|([^\[]+)\]\]!", function($m) { return lcml_wiki_do($m[1], $m[2]);}, $txt);
        $txt = preg_replace_callback("!\[\[([^\[]+)\]\]!", function($m) { return lcml_wiki_do($m[1]);}, $txt);

        return $txt;
    }

    function lcml_wiki_do($title, $text = NULL)
    {
		bors_debug::syslog('warning-lcml-need-restore-functional', "Call for old disabled HTS-Wiki call: ".$text);
		return $text;

        if(!$text)
            $text = $title;

        $hts = new DataBaseHTS();

		$uri = $hts->page_uri_by_value('title', $title);

        if($uri && strlen($uri) > 0)
		{
			$exists = $hts->get($uri, 'source') ? "" : "_non_exists";
	        return "<a href=\"$uri\" class=\"wiki_int$exists\">$text</a>";
		}

		include_once("inc/urls.php");
		$new_uri = $GLOBALS['main_uri'].strtolower(translite_uri_simple($title)).'/';

		$hts->set_data($new_uri, 'title', $title);
        return "<a href=\"$new_uri\" class=\"wiki_int_non_exists\">$text</a>";
    }
