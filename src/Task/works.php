<?php

require __DIR__.'/../config.php';

while(true)
{
	B2\Task\Json::do_works();
	usleep(200000);
}
