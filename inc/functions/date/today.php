<?php

function date_today($time = 0)
{
	return strtotime(date('Y-m-d', $time ? $time : time()));
}
