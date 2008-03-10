<?
    function lcml_urls_pre_pre($txt)
    {
        $txt = preg_replace("!\[url\](.+?)\[/url\]!is", "[url=$1]$1[/url]", $txt);

        return $txt;
    }
