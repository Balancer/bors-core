<?php

class common_date_months extends base_list
{
	function months_list_rp()
	{
		return array(
			0 => '-----',
			1 => ec('Января'),
			2 => ec('Февраля'),
			3 => ec('Марта'),
			4 => ec('Апреля'),
			5 => ec('Мая'),
			6 => ec('Июня'),
			7 => ec('Июля'),
			8 => ec('Августа'),
			9 => ec('Сентября'),
			10 => ec('Октября'),
			11 => ec('Ноября'),
			12 => ec('Декабря'),
		);
	}

	function named_list($default = 0)
	{
		return array(
			0 => '-----',
			1 => ec('Январь'),
			2 => ec('Февраль'),
			3 => ec('Март'),
			4 => ec('Апрель'),
			5 => ec('Май'),
			6 => ec('Июнь'),
			7 => ec('Июль'),
			8 => ec('Август'),
			9 => ec('Сентябрь'),
			10 => ec('Октябрь'),
			11 => ec('Ноябрь'),
			12 => ec('Декабрь'),
			'default' => $default === NULL ? 0 : (strftime('%m', $default ? $default : time())),
		);
	}

	function named_list_no_current()
	{
		return array(
			0 => '-----',
			1 => ec('Январь'),
			2 => ec('Февраль'),
			3 => ec('Март'),
			4 => ec('Апрель'),
			5 => ec('Май'),
			6 => ec('Июнь'),
			7 => ec('Июль'),
			8 => ec('Август'),
			9 => ec('Сентябрь'),
			10 => ec('Октябрь'),
			11 => ec('Ноябрь'),
			12 => ec('Декабрь'),
		);
	}
}
