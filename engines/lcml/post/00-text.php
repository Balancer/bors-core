<?
    function lcml_text($txt)
    { 
//        return $txt = "'''$txt";

        $txt=preg_replace("!(^|\s|\"|:|')\*(\S.*?\S)\*(?=(\s|\.|,|\"|'|:|$|\?))!","$1<b>$2</b>",$txt);
        $txt=preg_replace("!(^|\s|\"|:|')_(\S.*?\S)_(?=(\s|\.\s|,\s|\"|:|'\s|$|\?))!","$1<i>$2</i>",$txt);
        $txt=preg_replace("!^(//\s+.+)$!m","<small>$1 </small>",$txt);

#        $txt=preg_replace("!^( +)!em","str_repeat('&nbsp;',strlen('$1'))",$txt);
        $txt=preg_replace("!\^([\d\.]+)!","<sup>$1</sup>",$txt);

        $txt = preg_replace("!<<!", "&#171;", $txt);
        $txt = preg_replace("!>>!", "&#187;", $txt);

        return $txt;
    }
