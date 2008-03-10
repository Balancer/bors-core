<?
    function lst_addurl($txt)
    {
        list($url,$tag,$author,$date,$name,$desc)=split("\|",$txt."||||||");
        return "<table><tr><td><b><a href=$url>$name</a></b>, <small> $author, $date <br> $desc </td></tr></table>\n";
        #\s+(.*?)\|(.*?)\|(.*?)\|(.*?)\|(.*?)\|(.*)~"<table id=addurl><caption>".($1?"<a href=$1>":"")."$4 $5".($1?"</a>":"")."</caption><tr><td>$6<div align=right>".($1?"/<a href=$1>קנש×ר...</a>/":"")."<br>¨נקüץ¸×רû: $3</div></td></tr></table>\n"~ge;
    }
?>