<?php

class_include('base_list');

class common_sex extends base_list
{
	function named_list()
	{
		return array(
			'' => ec('Не указано'),
			'male' => ec('Муж.'),
			'female' => ec('Жен.'),
		);
	}
}
