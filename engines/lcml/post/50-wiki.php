<?
    require_once("funcs/DataBaseHTS.php");

    function lcml_wiki($txt)
    {
        $txt = preg_replace("!\[\[([^\[]+?)\|([^\[]+)\]\]!e", "lcml_wiki_do('$1','$2')", $txt);
        $txt = preg_replace("!\[\[([^\[]+)\]\]!e", "lcml_wiki_do('$1')", $txt);

        return $txt;
    }

    function lcml_wiki_do($title, $text = NULL)
    {
        if(!$text)
            $text = $title;

        $hts = &new DataBaseHTS();

		$uri = $hts->page_uri_by_value('title', $title);

        if($uri && strlen($uri) > 0)
		{
			$exists = $hts->get($uri, 'source') ? "" : "_non_exists";
	        return "<a href=\"$uri\" class=\"wiki_int$exists\">$text</a>";
		}
		
		include_once("funcs/modules/uri.php");
		$new_uri = $GLOBALS['main_uri'].strtolower(translite_uri_simple($title)).'/';
		
		$hts->set_data($new_uri, 'title', $title);
        return "<a href=\"$new_uri\" class=\"wiki_int_non_exists\">$text</a>";
    }
