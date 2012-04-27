<?php

bors_function_include('natural/bors_unplural');

function bors_chunks_unplural($string, $split='_', $join = '_')
{
	return join($join, array_map('bors_unplural', explode($split, $string)));
}
