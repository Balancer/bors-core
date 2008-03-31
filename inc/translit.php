<?
function from_translit($s)
{
    $s=str_replace("shch",ec("щ"),$s);
    $s=str_replace("Shch",ec("Щ"),$s);
    $s=str_replace("SHCH",ec("Щ"),$s);
    $s=str_replace("jo",ec("ё"),$s);
    $s=str_replace("Jo",ec("Ё"),$s);
    $s=str_replace("JO",ec("Ё"),$s);
    $s=str_replace("je",ec("е"),$s);
    $s=str_replace("Je",ec("Е"),$s);
    $s=str_replace("JE",ec("Е"),$s);
    $s=str_replace("yo",ec("ё"),$s);
    $s=str_replace("Yo",ec("Ё"),$s);
    $s=str_replace("YO",ec("Ё"),$s);
    $s=str_replace("yu",ec("ю"),$s);
    $s=str_replace("Yu",ec("Ю"),$s);
    $s=str_replace("YU",ec("Ю"),$s);
    $s=str_replace("ya",ec("я"),$s);
    $s=str_replace("Ya",ec("Я"),$s);
    $s=str_replace("YA",ec("Я"),$s);
    $s=str_replace("zh",ec("ж"),$s);
    $s=str_replace("Zh",ec("Ж"),$s);
    $s=str_replace("ZH",ec("Ж"),$s);
    $s=str_replace("kh",ec("х"),$s);
    $s=str_replace("Kh",ec("Х"),$s);
    $s=str_replace("KH",ec("Х"),$s);
    $s=str_replace("ch",ec("ч"),$s);
    $s=str_replace("Ch",ec("Ч"),$s);
    $s=str_replace("CH",ec("Ч"),$s);
    $s=str_replace("sh",ec("ш"),$s);
    $s=str_replace("Sh",ec("Ш"),$s);
    $s=str_replace("SH",ec("Ш"),$s);
    $s=str_replace("e\'","э",$s);
    $s=str_replace("e&#39;","э",$s);
    $s=str_replace("E\'","Э",$s);
    $s=str_replace("E&#39;","Э",$s);
    $s=str_replace("ju",ec("ю"),$s);
    $s=str_replace("Ju",ec("Ю"),$s);
    $s=str_replace("JU",ec("Ю"),$s);
    $s=str_replace("ja",ec("я"),$s);
    $s=str_replace("Ja",ec("Я"),$s);
    $s=str_replace("JA",ec("Я"),$s);
    $s=str_replace("ts",ec("ц"),$s);
    $s=str_replace("Ts",ec("Ц"),$s);
    $s=str_replace("TS",ec("Ц"),$s);

//    $from="abwvgdezijklmnoprstufhc'yABWVGDEZIJKLMNOPRSTUFHC'Y";
//      $to="абввгдезшйклмнопрстуфхцьыАБВВГДЕЗИЙКЛМНОПРСТУФХЦЬЫ";

    $from=array('h','H','w','W','a','b','v','g','d','e','yo','zh','z','i','k','l','m','n','o','p','r','s','t','u','f','kh','ts','c','ch','sh','sch','\'','\'','e','yu','ya','A','B','V','G','D','E','YO','ZH','Z','I','K','L','M','N','O','P','R','S','T','U','F','KH','TS','C','CH','SH','SCH','\'','\'','E','YU','YA');
    $to=array('х','Х','в','В','а','б','в','г','д','е','ё','ж','з','и','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ц','ч','ш','щ','ъ','ь','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ц','Ч','Ш','Щ','Ъ','Ь','Э','Ю','Я');

	for($i=0, $size=sizeof($to); $i<$size; $i++)
		$to[$i] = ec($to[$i]);

    $s =  str_replace($from, $to, $s);

	$s = preg_replace(ec("!([бвгджзклмнпрстфхцчшщ])j\b!u"), ec("$1ь"), $s);
	$s = preg_replace(ec("!([БВГДЖЗКЛМНПРСТФХЦЧШЩ])J\b!u"), ec("$1Ь"), $s);
	$s = preg_replace(ec("!([а-я])4\b!u"), ec("$1ч"), $s);
	$s = preg_replace(ec("!([А-Я])4\b!u"), ec("$1Ч"), $s);
	$s = preg_replace(ec("!([а-я])4([а-я])!u"), ec("$1ч$2"), $s);
	$s = preg_replace(ec("!([А-Я])4([А-Я])!u"), ec("$1Ч$2"), $s);

	$s = str_replace(ec("4то"), ec("что"), $s);
	$s = str_replace(ec("4ТО"), ec("ЧТО"), $s);

	$s = preg_replace(ec("!\By\B!u"), ec("ы"), $s);
	$s = preg_replace(ec("!\BY\B!u"), ec("Ы"), $s);
	$s = preg_replace(ec("!([аеиоуыэюя])y\b!u"), ec("$1й"), $s);
	$s = preg_replace(ec("!([АЕИОУЫЭЮЯ])Y\b!u"), ec("$1Й"), $s);

	$s = str_replace(array('j','J','y','Y'), array(ec('й'), ec('Й'),ec('ы'),ec('Ы')), $s);

    return $s;
}

    function to_translit($s)
    {   
        $from=array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я');
        $to=array('a','b','v','g','d','e','yo','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f','kh','ts','ch','sh','sch','\'','y','\'','e','yu','ya','A','B','V','G','D','E','YO','ZH','Z','I','J','K','L','M','N','O','P','R','S','T','U','F','KH','TS','CH','SH','SCH','\'','Y','\'','E','YU','YA');

		for($i=0, $size=sizeof($from); $i<$size; $i++)
			$from[$i] = ec($from[$i]);

        return str_replace($from, $to, $s);
    }
?>
