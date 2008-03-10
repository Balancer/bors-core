<?
    function lcml_auto_images($txt)
    {
        $txt = preg_replace("!\[img\](.+?)\[/img\]!i", "[img $1 ]'", $txt);
        $txt = preg_replace("!\[img\s*src=(.+?\.(jpg|png|gif|jpeg))\]!i", "[img $1 ]", $txt);
        $txt = preg_replace("!\[([^\|\]\s]+?\.(jpg|png|gif|jpeg))\|([^\]]+?)\]!is", "[img $1 468x468 left noflow| $3 ]", $txt);
        $txt = preg_replace("!\[([^\|\]]+?\.(jpg|png|gif|jpeg))\]!i", "[img $1 468x468 left noflow]", $txt);

        return $txt;
    }
