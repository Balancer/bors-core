<?
    function lst_link($txt)
    {
        if(!trim($txt))
            return "";

        list($url,$image,$title,$description,$author,$date) = split("\|",$txt.'|||||');

        if($image)
            $image="[img $image nohref 200x left]";

        $txt = <<<__EOT__
[box]<!--link-->[{$url}|{$image}[b]{$title}[/b]]<!--/link--><br />
[small]{$description}[/small]<br />
#a {$author}, $date
[/box]
__EOT__;
        return lcml($txt);

// #link http://www.kazanhelicopters.com/|kazanhelicopters.jpg|Joint Stock company "Kazan Helicopters"| ? ???? . [eng]|kron|16.07.2002 13:13
    
    }
?>