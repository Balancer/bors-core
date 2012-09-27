<?php

class lingustics_morphology
{
	function case_rus($word, $case)
	{
		static $m = NULL;
		if(!$m)
			$m = lingustics_phpmorphy::factory();

		$words = preg_split('/\s+/', trim($word));
		if(count($words) > 1)
		{
			$result = array();
			$last = array_pop($words);

			foreach($words as $w)
				$result[] = self::case_rus($w, $case);

			$result[] = $last;
			return join(' ', $result);
		}

		$words = $word[0];

		static $case_rus = array(
			'nom' => 'ИМ',
			'gen' => 'РД',
			'dat' => 'ДТ',
			'acc' => 'ВН',
			'ins' => 'ТВ',
			'pre' => 'ПР',
			'mult' => 'МН',
			'plur' => 'МН',
		);

		$cases = array();
		foreach(preg_split('/\s*,\s*/', $case) as $c)
			$cases[] = ec($case_rus[$c]);

		if(!($g = $m->find_word($word)))
			return $word;

		$w = $m->word_form_by_grammems($g, $cases);
		return $w ? $w : $word;
	}

	static function __unit_test($suite)
	{
		$suite->assertEquals('авиакомпании', bors_lower(lingustics_morphology::case_rus('авиакомпания', 'acc')));
		$suite->assertEquals('авиакомпании', bors_lower(lingustics_morphology::case_rus('авиакомпания', 'plur')));
		$suite->assertEquals('авиакомпаний', bors_lower(lingustics_morphology::case_rus('авиакомпания', 'gen,plur')));
		$suite->assertEquals('авиакомпании', bors_lower(lingustics_morphology::case_rus('авиакомпания', 'nom,plur')));
	}
}
