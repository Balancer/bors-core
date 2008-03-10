<?
    function lcml_urls_pre_post($txt)
    {
        $n=1000;
        while((
                preg_match("!\[([^\]\s\|]*?/[^\]\s\|]*?)\|(.+?)\]!is", $txt, $m) // "!isu for _utf8_
                ||
                preg_match("!\[([^\]\s]*?/[^\]\s]*?)&#124;(.+?)\]!is", $txt, $m) // "!isu for _utf8_
                ) && $n-->0)
            $txt = str_replace($m[0], "[url {$m[1]}|{$m[2]}]", $txt);

        return $txt;
    }
