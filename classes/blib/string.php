<?php

/*
	Полезные ссылки:

	Индексный доступ к Multibyte-строкам на PHP или изучение ООП на практике
	http://habrahabr.ru/post/165107/
*/

class blib_string extends blib_object
{
	function __construct($init_value = NULL)
	{
		$this->_value = (string) $init_value;
	}

	static function factory($string) { $cls = get_called_class(); return new $cls($string); }

	function __toString() { return $this->_value; }
	function to_string() { return $this->_value; }

	function write() { echo $this->_value; return $this; }
	function writeln() { echo $this->_value, PHP_EOL; return $this; }
	function debug_write() { var_dump($this->_value); return $this; }
	function len() { return bors_strlen($this->_value); }
	function length() { return bors_strlen($this->_value); }
	function upper() { $this->_value = bors_upper($this->_value); return $this; }
	function lower() { $this->_value = bors_lower($this->_value); return $this; }

	static function wordwrap($str, $width = 75, $break = "\n", $cut = false)
	{
		return self::_mb_wordwrap3($str, $width, $break, $cut);
	}

	// http://phpforum.ru/index.php?showtopic=37850
	// 9.2
	function _mb_wordwrap1($str, $width = 75, $break = "\n", $cut = false)
	{
		return preg_replace('#([\S]{'.$width.'}'. ($cut ? '' : '\s') .')#u', '$1'. $break , $str);
	}

	// http://milianw.de/code-snippets/utf-8-wordwrap
	// Не работает с cut = false
	function _utf8_wordwrap2($str, $width = 75, $break = "\n", $cut = false)
	{
		if(!$cut)
			$regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){'.$width.',}\b#U';
		else
			$regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){'.$width.'}#';

		if(function_exists('mb_strlen'))
			$str_len = mb_strlen($str,'UTF-8');
		else
			$str_len = preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $var_empty);

		$while_what = ceil($str_len / $width);
		$i = 1;
		$return = '';

		while ($i < $while_what)
		{
			preg_match($regexp, $str, $matches);
//			var_dump($regexp, $str, $matches);
			$string = $matches[0];
			$return .= $string.$break;
			$str = substr($str, strlen($string));
			$i++;
		}

		return $return.$str;
	}

	// http://stackoverflow.com/questions/3825226/multi-byte-safe-wordwrap-function-for-utf-8
	// Вроде, работает
	// Bench: 0.9мс
	function _mb_wordwrap3($str, $width = 75, $break = "\n", $cut = false)
	{
    	$lines = explode($break, $str);
	    foreach ($lines as &$line)
	    {
    	    $line = rtrim($line);
        	if (mb_strlen($line) <= $width)
            	continue;
	        $words = explode(' ', $line);
    	    $line = '';
        	$actual = '';
	        foreach ($words as $word)
	        {
            	if (mb_strlen($actual.$word) <= $width)
                	$actual .= $word.' ';
	            else
	            {
                	if ($actual != '')
                    	$line .= rtrim($actual).$break;
	                $actual = $word;
    	            if ($cut)
    	            {
                    	while (mb_strlen($actual) > $width)
                    	{
                        	$line .= mb_substr($actual, 0, $width).$break;
	                        $actual = mb_substr($actual, $width);
    	                }
        	        }
            	    $actual .= ' ';
	            }
    	    }
        	$line .= trim($actual);
	    }
    	return implode($break, $lines);
	}

	function __unit_test($test)
	{
		$s = "Hello, world!";
		$bs = new blib_string($s);
		$test->assertEquals($s, (string) $bs);
		$test->assertEquals(bors_upper($s), $bs->upper()->value());
		$test->assertEquals(bors_lower($s), $bs->lower()->val());
		$test->assertEquals(13, $bs->len());

		$str = str_repeat('A very long woooooooooooord. ', 100);
		$test->assertEquals('A very=long=wooooooo=ooooord.=A very=lon',  bors_substr(wordwrap($str, 8, '=', true), 0, 40));
		$test->assertEquals('A very=long=wooooooo=ooooord.=A very=lon', bors_substr(blib_string::wordwrap($str, 8, '=', true), 0, 40));
		$test->assertEquals('A very=long=woooooooooooord.=A very=long', bors_substr(wordwrap($str, 8, '='), 0, 40));
		$test->assertEquals('A very=long=woooooooooooord.=A very=long', bors_substr(blib_string::wordwrap($str, 8, '='), 0, 40));

		$str = str_repeat("однажды в студёную зимнюю пору я из лесу вышел, был сильный мороз", 100);
		$test->assertEquals('однажды в студёную=зимнюю пору я из=лесу', bors_substr(blib_string::wordwrap($str, 20, '=', true), 0, 40));
		$test->assertEquals('однажды в студёную=зимнюю пору я из=лесу', bors_substr(blib_string::wordwrap($str, 20, '='), 0, 40));
		$test->assertEquals('однажды=в=студёну=ю=зимнюю=пору я=из лес', bors_substr(blib_string::wordwrap($str, 7, '=', true), 0, 40));
		$test->assertEquals('однажды=в=студёную=зимнюю=пору я=из лесу', bors_substr(blib_string::wordwrap($str, 7, '='), 0, 40));

		$x = blib_string::factory("Test 123");
		$test->assertEquals('Test/123', $x->split(' ')->join('/'));
	}

	static function __benchmark()
	{
		$str = str_repeat("однажды в студёную зимнюю пору я из лесу вышел, был сильный мороз", 100);
		return array(array('blib_string::_mb_wordwrap1', 'blib_string::_mb_wordwrap3', 'blib_string::_utf8_wordwrap2'), array($str, 200, "\n", true));
	}

	static function base64_encode2($str)
	{
		return str_replace(array('/','+'), array('_', '-'), base64_encode($str));
	}

	static function base64_decode2($str)
	{
		return base64_decode(str_replace(array('_','-'), array('/', '+'), $str));
	}

	function split($delimiter)
	{
		return blib_array::factory(explode($delimiter, $this->_value));
	}

	function hyphenate()
	{
		require_once('inc/strings.php');
		$this->_value = bors_hypher($this->_value);
		return $this;

/*
		if(is_global_key('hypher-cache', $this->_value))
		{
			$this->_value = global_key('hypher-cache', $this->_value);
			return $this;
		}
*/
		global $bors_3rd_glob_chypher;
		if(empty($bors_3rd_glob_chypher))
		{
			$path = config('phphypher.path');
			require_once $path.'/hypher.php';
			$bors_3rd_glob_chypher = new phpHypher($path.'/hyph_ru_RU.conf');
		}

//		ini_set('mbstring.internal_encoding', 'windows-1251');
//		ini_set('default_charset', 'windows-1251');
//		$this->_value = $bors_3rd_glob_hypher->hyphenate(dc($this->_value, 'cp1251'), 'CP1251');
		$this->_value = $bors_3rd_glob_chypher->hyphenate($this->_value);
		return $this;
	}

	function __dev()
	{
		require_once('inc/strings.php');
		var_dump(bors_hypher('Привет, мир!'));
		$s = self::factory('Привет, мир!');
		var_dump($s->hyphenate()->value());
	}
}
