<?
    function lst_popup($txt)
    {
        if(!trim($txt))
            return "";
        $GLOBALS['pagedata']['forum']=$txt;
        $GLOBALS['forum_tag_found']=1;
        return "<script src=\"http://airbase.ru/js/include.php/inc/show/forum-comments.phtml?id=$txt\"></script>".
        "<noscript><a href=\"http://airbase.ru/inc/show/forum-comments.phtml?id=$txt\">комментарии</a></noscript>";
//        return "<?\$id=\"$txt\";\$"."xpage=\"".(isset($GLOBALS['main_uri'])?$GLOBALS['main_uri']:'')."\";include(\"/home/airbase/html/inc/show/forum-comments.phtml\");
    }
?>
