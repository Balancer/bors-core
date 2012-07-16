<?php

class lingustics_morphology
{
	function case_rus($word, $case)
	{
		static $case_rus = array(
			'nom' => 'ИМ',
			'gen' => 'РД',
			'dat' => 'ДТ',
			'acc' => 'ВН',
			'ins' => 'ТВ',
			'pre' => 'ПР',
		);

		$m = lingustics_phpmorphy::factory();
		$g = $m->find_word('Объект');
		$w = $m->word_form_by_grammems($g, $case_rus[$case]);
		return $w ? $w : $word;
	}
}
