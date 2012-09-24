<?php

function bors_entity_decode($string)
{
	return html_entity_decode($string, ENT_COMPAT, config('internal_charset'));
}
