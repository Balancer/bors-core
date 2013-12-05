<?php

class blib_exception
{
	var $exception = NULL;

	static function factory(Exception $e)
	{
		$x = new blib_exception;
		$x->exception = $e;
		return $x;
	}

	function message()
	{
		return $this->exception->getMessage();
	}

	function html_short()
	{
		bors_function_include('debug/trace');
		$trace = debug_trace(0, false, -1, $this->exception->getTrace());
		$message = $this->exception->getMessage();
		return "$message<!--\n$trace\n-->";
	}

	function __toString()
	{
		bors_function_include('debug/trace');
		$trace = debug_trace(0, false, -1, $this->exception->getTrace());
		$message = $this->exception->getMessage();
		return "$message:\n$trace";
	}
}
