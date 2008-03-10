<?
    function lsp_article($txt)
    {
        $a = lcml_sharp_getset($txt);
        $href  = empty($a['href']  )? "":$a['href'];
        $title = empty($a['title'] )? "":$a['title'];
        $author= empty($a['author'])? "":$a['author'];
        $text  = empty($a['text']  )? "":$a['text'];
        $time  = empty($a['time']  )? "":$a['time'];
        $img   = empty($a['img']   )? "":$a['img'];

        return "#box\n[$href|[$img 128x left nohref]][small]{$author}[/small]<br>[$href|[b]{$title}[/b]]<br>[small][i]{$text}[/i][/small]\n#/box";
    }
?>