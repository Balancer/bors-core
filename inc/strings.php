<?
function remove_last_slash($s)
{
    return $s[strlen($s)-1]=='/' ? substr($s,0,-1) : $s;
}

function utf8_win($s)
{
    $s=str_replace("\xD0\xB0","à",$s);  $s=str_replace("\xD0\x90","À",$s);
    $s=str_replace("\xD0\xB1","á",$s);  $s=str_replace("\xD0\x91","Á",$s);
    $s=str_replace("\xD0\xB2","â",$s);  $s=str_replace("\xD0\x92","Â",$s);
    $s=str_replace("\xD0\xB3","ã",$s);  $s=str_replace("\xD0\x93","Ã",$s);
    $s=str_replace("\xD0\xB4","ä",$s);  $s=str_replace("\xD0\x94","Ä",$s);
    $s=str_replace("\xD0\xB5","å",$s);  $s=str_replace("\xD0\x95","Å",$s);
    $s=str_replace("\xD1\x91","¸",$s);  $s=str_replace("\xD0\x81","¨",$s);
    $s=str_replace("\xD0\xB6","æ",$s);  $s=str_replace("\xD0\x96","Æ",$s);
    $s=str_replace("\xD0\xB7","ç",$s);  $s=str_replace("\xD0\x97","Ç",$s);
    $s=str_replace("\xD0\xB8","è",$s);  $s=str_replace("\xD0\x98","È",$s);
    $s=str_replace("\xD0\xB9","é",$s);  $s=str_replace("\xD0\x99","É",$s);
    $s=str_replace("\xD0\xBA","ê",$s);  $s=str_replace("\xD0\x9A","Ê",$s);
    $s=str_replace("\xD0\xBB","ë",$s);  $s=str_replace("\xD0\x9B","Ë",$s);
    $s=str_replace("\xD0\xBC","ì",$s);  $s=str_replace("\xD0\x9C","Ì",$s);
    $s=str_replace("\xD0\xBD","í",$s);  $s=str_replace("\xD0\x9D","Í",$s);
    $s=str_replace("\xD0\xBE","î",$s);  $s=str_replace("\xD0\x9E","Î",$s);
    $s=str_replace("\xD0\xBF","ï",$s);  $s=str_replace("\xD0\x9F","Ï",$s);
    $s=str_replace("\xD1\x80","ð",$s);  $s=str_replace("\xD0\xA0","Ð",$s);
    $s=str_replace("\xD1\x81","ñ",$s);  $s=str_replace("\xD0\xA1","Ñ",$s);
    $s=str_replace("\xD1\x82","ò",$s);  $s=str_replace("\xD0\xA2","Ò",$s);
    $s=str_replace("\xD1\x83","ó",$s);  $s=str_replace("\xD0\xA3","Ó",$s);
    $s=str_replace("\xD1\x84","ô",$s);  $s=str_replace("\xD0\xA4","Ô",$s);
    $s=str_replace("\xD1\x85","õ",$s);  $s=str_replace("\xD0\xA5","Õ",$s);
    $s=str_replace("\xD1\x86","ö",$s);  $s=str_replace("\xD0\xA6","Ö",$s);
    $s=str_replace("\xD1\x87","÷",$s);  $s=str_replace("\xD0\xA7","×",$s);
    $s=str_replace("\xD1\x88","ø",$s);  $s=str_replace("\xD0\xA8","Ø",$s);
    $s=str_replace("\xD1\x89","ù",$s);  $s=str_replace("\xD0\xA9","Ù",$s);
    $s=str_replace("\xD1\x8A","ú",$s);  $s=str_replace("\xD0\xAA","Ú",$s);
    $s=str_replace("\xD1\x8B","û",$s);  $s=str_replace("\xD0\xAB","Û",$s);
    $s=str_replace("\xD1\x8C","ü",$s);  $s=str_replace("\xD0\xAC","Ü",$s);
    $s=str_replace("\xD1\x8D","ý",$s);  $s=str_replace("\xD0\xAD","Ý",$s);
    $s=str_replace("\xD1\x8E","þ",$s);  $s=str_replace("\xD0\xAE","Þ",$s);
    $s=str_replace("\xD1\x8F","ÿ",$s);  $s=str_replace("\xD0\xAF","ß",$s);
    return $s;
}

	function sklon($n, $s1, $s2, $s5) // 1 Ð½Ð¾Ð¶ 2 Ð½Ð¾Ð¶Ð° 5 Ð½Ð¾Ð¶ÐµÐ¹
	{
    	$ns=intval(substr($n,-1));
 		$n2=intval(substr($n,-2));

	    if($n2>=10 && $n2<=19) return $s5;
    	if($ns==1) return $s1;
	    if($ns>=2&&$ns<=4) return $s2;
    	if($ns==0 || $ns>=5) return $s5;
		return "ÐÐµÐ¸Ð·Ð²ÐµÑÑ‚Ð½Ð°Ñ Ð¿Ð°Ñ€Ð° '$n $s1'! ÐŸÐ¾Ð¶Ð°Ð»ÑƒÐ¹ÑÑ‚Ð°, ÑÐ¾Ð¾Ð±Ñ‰Ð¸ Ð¾Ð± ÑÑ‚Ð¾Ð¹ Ð¾ÑˆÐ¸Ð±ÐºÐµ Ð°Ð´Ð¼Ð¸Ð½Ð¸ÑÑ‚Ñ€Ð°Ñ‚Ð¾Ñ€Ñƒ!";
	}

function truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false)
{
    if ($length == 0)
        return '';

    if (strlen($string) > $length)
	{
        $length -= min($length, strlen($etc));
        if (!$break_words && !$middle)
		{
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
        }
        if(!$middle)
		{
            return substr($string, 0, $length) . $etc;
        }
		else
		{
            return substr($string, 0, $length/2) . $etc . substr($string, -$length/2);
        }
    }
	else
	{
        return $string;
    }
}

function stripq($text) { return str_replace('\\"', '"', $text); }
