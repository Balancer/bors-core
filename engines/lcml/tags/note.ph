<?
    require_once('funcs/users.php');

    function lp_note($text,$params)
    {
        list($author, $time) = split(' ', $params['orig'].'  ');

        $time = $time ? ", ".strftime("%Y.%m.%d %H:%M",($time)) : '';

        return "<div class=\"box\"><!--note-->$text<br>\n<div align=\"right\">".user_data('nick', $author)."$time</div>\n<!--/note--></div>\n";
    }
?>