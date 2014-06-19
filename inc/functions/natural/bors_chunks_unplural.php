<?php

function bors_chunks_unplural($string, $split='_', $join = '_')
{
	return join($join, array_map(array('blib_grammar', 'singular'), explode($split, $string)));
}
