<?php

$GLOBALS['month_names'] = explode(' ', 'Январь Февраль Март Апрель Май Июнь Июль Август Сентябрь Октябрь Ноябрь Декабрь');

function month_name($m) { return ec($GLOBALS['month_names'][$m-1]); }
