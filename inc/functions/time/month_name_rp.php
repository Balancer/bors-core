<?php

$GLOBALS['month_names_rp'] = explode(' ', 'Января Февраля Марта Апреля Мая Июня Июля Августа Сентября Октября Ноября Декабря');

function month_name_rp($m)
{
	return ec($GLOBALS['month_names_rp'][$m-1]);
}
