<?php

/**
	Попытка сделать унифицированный объектный формат даты и времени
	Тупой вариант на базе unix timestamp
*/

class bors_time_ts extends bors_time_meta
{
	private $_time;

	/**
		Если параметр - NULL, то текущее время
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

		$this->_time = $time;

		parent::__construct(date('c', $time)); //TODO: может быть uuid()?
	}

	static function now() { return new bors_time_ts(); }
	static function yesterday() { return new bors_time_ts(strtotime('yesterday')); }
	static function tomorrow() { return new bors_time_ts(strtotime('tomorrow')); }

	function before($offset)
	{
		return new bors_time_ts(strtotime('-'.$offset, $this->_time));
	}

	function date($format) { return date($format, $this->_time); }
	function strftime($format) { return strftime($format, $this->_time); }

	function __toString() { return $this->date('d.m.Y H:i:s'); }

	function timestamp() { return $this->is_null ? NULL : $this->_time; }

//TODO: реализовать:
//	function mysql_time()
//	function interval?
}
