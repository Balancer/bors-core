<?
    foreach(split(" ","b big i s strong sub sup small u xmp") as $tag)
		eval("function lp_$tag(\$txt){return '<$tag>'.lcml(\$txt).'</$tag>';}");

    foreach(split(" ","br hr") as $tag)
		eval("function lt_$tag(){return '<$tag />';}");
?>
