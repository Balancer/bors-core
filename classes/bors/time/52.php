<?php

/**
	Попытка сделать унифицированный объектный формат даты и времени
	Вариант на классе DateTime из PHP >= 5.2.0
*/

class bors_time_52 extends bors_object //TODO: придумать название «пустого» класса вместо base_empty
{
	private $_time;

	/**
		Если параметр - NULL, то текущее время
	*/

	function __construct($time = NULL)
	{
		if(is_null($time))
			$this->is_null = true;

		$this->_time = new DateTime($time ? $time : 'now');
		parent::__construct($this->_time->format(DateTime::ATOM)); //TODO: может быть uuid()?
	}

	static function now()
	{
		return new bors_time();
	}

	function before($offset)
	{
		//TODO: возвращать новый объект?
		$this->_time.sub($offset); //TODO: жопа. В 5.2 аналога нет.
		return $this;
	}

//TODO: реализовать:
//	function mysql_time()
//	function interval?
}
