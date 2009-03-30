<?php

function lcml_icq($txt)
{
	return preg_replace("/ICQ#(\d+)/", "<img src=\"http://wwp.icq.com/scripts/online.dll?icq=$1&img=5\" width=\"18\" height=\"18\" align=\"absmiddle\">$1", $txt);
}
