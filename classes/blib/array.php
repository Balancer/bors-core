<?php

/*
	Полезные ссылки:

	Индексный доступ к Multibyte-строкам на PHP или изучение ООП на практике
	http://habrahabr.ru/post/165107/
*/

class blib_array extends blib_object implements ArrayAccess
{
	function __construct($init_value = NULL)
	{
		if(is_array($init_value))
			return $this->_value = $init_value;

		$this->_value = array();
	}

	static function factory($array = NULL) { return new blib_array($array); }

	function map($function)
	{
		$this->_value = array_map($function, $this->_value);
		return $this;
	}

	function filter()
	{
		$this->_value = array_filter($this->_value);
		return $this;
	}

	function pgrep($regexp)
	{
		$this->_value = array_filter($this->_value, function($x) use ($regexp) { return preg_match($regexp, $x); } );
		return $this;
	}

	function unique()
	{
		$this->_value = array_unique($this->_value);
		return $this;
	}

	function each($function)
	{
		foreach($this->_value as &$x)
			$function($x);

		return $this;
	}

	function nshift($n)
	{
		$x = array_slice($this->_value, 0, $n);
		$this->_value = array_slice($this->_value, $n);

		return self::factory($x);
	}

	function nslice($n)
	{
		$result = array();
		while(($x = $this->nshift($n)) && !$x->is_empty())
			$result[] = $x;

		return self::factory($result);
	}

	function range($start, $stop = NULL, $step = 1)
	{
		if(is_null($stop))
		{
			$stop = $start - 1;
			$start = 0;
		}

		$this->_value = range($start, $stop, $step);
		return $this;
	}

	function __toString()
	{
		return blib_string::factory(print_r($this->_value, true))->__toString();
	}

	function join($delimiter)
	{
		return blib_string::factory(join($delimiter, $this->_value));
	}

	function json()
	{
		return json_encode($this->_value);
	}

	/* Реализация методов интерфейса ArrayAccess */
	public function offsetExists($key) { return array_key_exists($key, $this->_value); }

	public function offsetSet($key, $value)
	{
		if(is_null($key))
			$this->_value[] = $value;
		else
			$this->_value[$key] = $value;
	}

	public function offsetUnset($key) { unset($this->_value[$key]); }

	public function offsetGet($key) { return array_key_exists($key, $this->_value) ? $this->_value[$key] : NULL; }
	/* Конец реализации методов интерфейса ArrayAccess */

	static function __unit_test($suite)
	{
		$x = blib_array::factory(array(1, 2, 3));
		$x->map(create_function('$x', 'return $x*$x;'));
		$x->each(create_function('&$x', '$x = $x*$x;'));
		$suite->assertEquals('1 16 81', $x->join(' '));
		$suite->assertEquals('[1,16,81]', $x->json());

		/* Тестирование интерфейса ArrayAccess */
		$suite->assertEquals(1, $x[0]);
		$suite->assertEquals(16, $x[1]);
		$suite->assertEquals(81, $x[2]);
		$suite->assertNull(@$x[3]);
		$suite->assertTrue($x->offsetExists(2));
		$suite->assertFalse($x->offsetExists(3));
	}
}
