<?php

class blib_string extends blib_object
{
	function __construct($init_value = NULL)
	{
		$this->_value = (string) $init_value;
	}

	function __toString() { return $this->_value; }

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
			var_dump($regexp, $str, $matches);
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
	}

	static function __benchmark()
	{
		$str = str_repeat("однажды в студёную зимнюю пору я из лесу вышел, был сильный мороз", 100);
		blib_benchmark::run('blib_string::_mb_wordwrap1', array($str, 200, "\n", true));
	}
}
