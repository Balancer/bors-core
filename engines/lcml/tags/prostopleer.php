<?php

function lp_prostopleer($id, &$params)
{
	return "<object width=\"411\" height=\"28\"><param name=\"movie\" value=\"http://embed.prostopleer.com/track?id={$id}\"></param><embed src=\"http://embed.prostopleer.com/track?id={$id}\" type=\"application/x-shockwave-flash\" width=\"411\" height=\"28\"></embed></object><br/><a href=\"http://prostopleer.com/tracks/{$id}\" class=\"transgray\">прямой линк на prostopleer.ru</a>";
}
