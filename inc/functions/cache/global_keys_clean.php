<?php

function global_keys_clean()
{
	unset($GLOBALS['bors_data']['global']);
	unset($GLOBALS['HTS_GLOBAL_DATA']);
}
