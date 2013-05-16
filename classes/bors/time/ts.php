<?php

/**
	Попытка сделать унифицированный объектный формат даты и времени
	Тупой вариант на базе unix timestamp
*/

class bors_time_ts extends bors_time_meta
{
	/**
		Если параметр - NULL, то текущее время
		Если число — то timestamp
		Если строка — то строковое представление времени для strtotime
	*/

	function __construct($time = NULL)
	{
		if(is_null($time))
		{
			$time = time();
			$this->is_null = true;
		}
		elseif(!is_numeric($time))
			$time = strtotime($time);

		$this->_value = $time;

		parent::__construct(date('c', $time)); //TODO: может быть uuid()?
	}

	static function now() { return new bors_time_ts(time()); }
	static function yesterday() { return new bors_time_ts(strtotime('yesterday')); }
	static function tomorrow() { return new bors_time_ts(strtotime('tomorrow')); }

	function before($offset)
	{
		return new bors_time_ts(strtotime('-'.$offset, $this->_value));
	}

	function date($format) { return date($format, $this->_value); }
	function strftime($format) { return strftime($format, $this->_value); }

	function timestamp() { return $this->is_null ? NULL : $this->_value; }

//TODO: реализовать:
//	function mysql_time()
//	function interval?


	static function __unit_test($suite)
	{
		// Ждём начала секунды, чтобы все проверки сделать в одно время
		$time = time();
		while(time() == $time)
			usleep(1000);

		$time = time();
		$t = new bors_time_ts($time);
		$now = bors_time_ts::now();

		$suite->assertEquals($time, $t->timestamp());
		$suite->assertEquals($time, $now->timestamp());
		$suite->assertEquals($t->dmy(), $now->dmy());
		$suite->assertEquals($t->hm(), $now->hm());
		$suite->assertEquals((string)$t, (string)$now);
	}
}
