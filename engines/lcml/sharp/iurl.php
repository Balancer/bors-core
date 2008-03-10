<?
    function lst_iurl($txt)
    {
        if(!trim($txt))
            return "";

        list($txt,$description) = split("\|",$txt.'|');
        list($url,$img,$w,$h,$align,$copyright,$idesc) = split(",",$txt.',,,,,,');
        $align = trim($align);

// #iurl files/kam1-titul300.jpg, files/kam1-titul200.jpg, 200, 303, center
// #iurl ( (1)url, (2)img, (3)iw, (4)ih, (5)align, (6)from, (7)imgDesc | (8)desc \ -- )

        if($w) $w=" width=$w";
        if($h) $h=" height=$h";
        if($url) $url="<a href=\"$url\">";
        if($idesc) $idesc="[{$idesc}]" ;
        $lal = ($align == 'center') ? "<div align=\"center\">" : "";
        $ral = ($align == 'center') ? "</div>" : "";
        $mal = ($align == 'center') ? "" : " align=\"$align\"";
        $hts = <<<XXX
$lal
<table cellPadding="10" cellSpacing="0"$mal>
<tr><td align="center">
<table border="0" cellPadding="0" cellSpacing="0" bgColor="#0066cc">
<tr><td align="center">
<table border="0" cellPadding="2" cellSpacing="1" bgColor="#0066cc">
<tr><td align="center" bgColor="#f0f4f8"$w$h>$url<img src="$img"$w$h border="0" /></a></th></tr>
XXX;
        if($description) $hts.="<tr><td bgColor=\"White\"$w align=\"center\"> $description </td></tr>";
        if($copyright)   $hts.="<tr><td$w align=\"center\" bgColor=\"#F0F0FF\">$copyright</td></tr>";
        if($idesc)       $hts.="<tr><td$w align=\"center\" bgColor=\"White\"><font color=\"Brown\">$idesc</font></th></tr>";
        $hts.="</table></th></tr></table></th></tr></table>\n$ral";
        return "$hts";
    
    }
?>