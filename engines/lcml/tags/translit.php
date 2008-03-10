<?
function lp_tr($s)
{
    $s=str_replace("&#39;","'",$s);
    $s=str_replace("&quot;","\"",$s);

    $s=str_replace("ts'a","тся",$s);
    $s=str_replace("tsa","тся",$s);
    $s=str_replace("m'a","мья",$s);

    $s=str_replace("tsya","тся",$s);

    $s=str_replace("etot","этот",$s);
    $s=preg_replace("!(\b)eto!","$1это",$s);

    $s=str_replace("otst","отст",$s);
    $s=str_replace("schiy","щий",$s);

    $s=str_replace("shch","щ",$s);
    $s=str_replace("Shch","Щ",$s);
    $s=str_replace("SHCH","Щ",$s);
    $s=str_replace("jo","ё",$s);
    $s=str_replace("Jo","Ё",$s);
    $s=str_replace("JO","Ё",$s);
    $s=str_replace("yo","ё",$s);
    $s=str_replace("Yo","Ё",$s);
    $s=str_replace("YO","Ё",$s);
    $s=str_replace("yu","ю",$s);
    $s=str_replace("Yu","Ю",$s);
    $s=str_replace("YU","Ю",$s);
    $s=str_replace("ya","я",$s);
    $s=str_replace("Ya","Я",$s);
    $s=str_replace("YA","Я",$s);

    $s=str_replace("ju","ю",$s);
    $s=str_replace("Ju","Ю",$s);
    $s=str_replace("JU","Ю",$s);
    $s=str_replace("ja","я",$s);
    $s=str_replace("Ja","Я",$s);
    $s=str_replace("JA","Я",$s);

    $s=str_replace("iy","ий",$s);
    $s=str_replace("ay","ай",$s);
    $s=str_replace("oy","ой",$s);
    $s=str_replace("ey","ей",$s);
    $s=str_replace("yy","ый",$s);

    $s=str_replace("zh","ж",$s);
    $s=str_replace("Zh","Ж",$s);
    $s=str_replace("ZH","Ж",$s);
    $s=str_replace("kh","х",$s);
    $s=str_replace("Kh","Х",$s);
    $s=str_replace("KH","Х",$s);

    $s=str_replace("Ts","Ц",$s);
    $s=str_replace("ts","ц",$s);
    $s=str_replace("TS","Ц",$s);

    $s=str_replace("ch","ч",$s);
    $s=str_replace("Ch","Ч",$s);
    $s=str_replace("CH","Ч",$s);
    $s=str_replace("sh","ш",$s);
    $s=str_replace("Sh","Ш",$s);
    $s=str_replace("SH","Ш",$s);
    $s=str_replace("e'","э",$s);
    $s=str_replace("E'","Э",$s);

    $from="abwvgdezijklmnoprstufhc'yABWVGDEZIJKLMNOPRSTUFhC'Y";
      $to="абввгдезийклмнопрстуфхцьыАБВВГДЕЗИЙКЛМНОПРСТУФхЦЬЫ";

    for($i=0;$i<strlen($from);$i++)
        $s=str_replace($from[$i],$to[$i*2].$to[$i*2+1],$s);

    $s=preg_replace("!([а-яА-Я])'!","$1ь",$s);

    $s=str_replace("'","&#39;",$s);
    $s=str_replace("\"","&quot;",$s);

    return $s;
}
?>
