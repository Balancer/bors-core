<?php
function smarty_modifier_pun_lcml($string)
{
	require_once('other/punbb-modified-forum/include/pun_bal.php');
    return pun_lcml($string);
}
