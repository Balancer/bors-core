<?
function remove_last_slash($s)
{
    return $s[strlen($s)-1]=='/' ? substr($s,0,-1) : $s;
}

function utf8_win($s)
{
    $s=str_replace("\xD0\xB0","�",$s);  $s=str_replace("\xD0\x90","�",$s);
    $s=str_replace("\xD0\xB1","�",$s);  $s=str_replace("\xD0\x91","�",$s);
    $s=str_replace("\xD0\xB2","�",$s);  $s=str_replace("\xD0\x92","�",$s);
    $s=str_replace("\xD0\xB3","�",$s);  $s=str_replace("\xD0\x93","�",$s);
    $s=str_replace("\xD0\xB4","�",$s);  $s=str_replace("\xD0\x94","�",$s);
    $s=str_replace("\xD0\xB5","�",$s);  $s=str_replace("\xD0\x95","�",$s);
    $s=str_replace("\xD1\x91","�",$s);  $s=str_replace("\xD0\x81","�",$s);
    $s=str_replace("\xD0\xB6","�",$s);  $s=str_replace("\xD0\x96","�",$s);
    $s=str_replace("\xD0\xB7","�",$s);  $s=str_replace("\xD0\x97","�",$s);
    $s=str_replace("\xD0\xB8","�",$s);  $s=str_replace("\xD0\x98","�",$s);
    $s=str_replace("\xD0\xB9","�",$s);  $s=str_replace("\xD0\x99","�",$s);
    $s=str_replace("\xD0\xBA","�",$s);  $s=str_replace("\xD0\x9A","�",$s);
    $s=str_replace("\xD0\xBB","�",$s);  $s=str_replace("\xD0\x9B","�",$s);
    $s=str_replace("\xD0\xBC","�",$s);  $s=str_replace("\xD0\x9C","�",$s);
    $s=str_replace("\xD0\xBD","�",$s);  $s=str_replace("\xD0\x9D","�",$s);
    $s=str_replace("\xD0\xBE","�",$s);  $s=str_replace("\xD0\x9E","�",$s);
    $s=str_replace("\xD0\xBF","�",$s);  $s=str_replace("\xD0\x9F","�",$s);
    $s=str_replace("\xD1\x80","�",$s);  $s=str_replace("\xD0\xA0","�",$s);
    $s=str_replace("\xD1\x81","�",$s);  $s=str_replace("\xD0\xA1","�",$s);
    $s=str_replace("\xD1\x82","�",$s);  $s=str_replace("\xD0\xA2","�",$s);
    $s=str_replace("\xD1\x83","�",$s);  $s=str_replace("\xD0\xA3","�",$s);
    $s=str_replace("\xD1\x84","�",$s);  $s=str_replace("\xD0\xA4","�",$s);
    $s=str_replace("\xD1\x85","�",$s);  $s=str_replace("\xD0\xA5","�",$s);
    $s=str_replace("\xD1\x86","�",$s);  $s=str_replace("\xD0\xA6","�",$s);
    $s=str_replace("\xD1\x87","�",$s);  $s=str_replace("\xD0\xA7","�",$s);
    $s=str_replace("\xD1\x88","�",$s);  $s=str_replace("\xD0\xA8","�",$s);
    $s=str_replace("\xD1\x89","�",$s);  $s=str_replace("\xD0\xA9","�",$s);
    $s=str_replace("\xD1\x8A","�",$s);  $s=str_replace("\xD0\xAA","�",$s);
    $s=str_replace("\xD1\x8B","�",$s);  $s=str_replace("\xD0\xAB","�",$s);
    $s=str_replace("\xD1\x8C","�",$s);  $s=str_replace("\xD0\xAC","�",$s);
    $s=str_replace("\xD1\x8D","�",$s);  $s=str_replace("\xD0\xAD","�",$s);
    $s=str_replace("\xD1\x8E","�",$s);  $s=str_replace("\xD0\xAE","�",$s);
    $s=str_replace("\xD1\x8F","�",$s);  $s=str_replace("\xD0\xAF","�",$s);
    return $s;
}

	function sklon($n, $s1, $s2, $s5) // 1 нож 2 ножа 5 ножей
	{
    	$ns=intval(substr($n,-1));
 		$n2=intval(substr($n,-2));

	    if($n2>=10 && $n2<=19) return $s5;
    	if($ns==1) return $s1;
	    if($ns>=2&&$ns<=4) return $s2;
    	if($ns==0 || $ns>=5) return $s5;
		return "Неизвестная пара '$n $s1'! Пожалуйста, сообщи об этой ошибке администратору!";
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
