<?php

class lingustics_morphology
{
	static function case_rus($word, $case)
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

		static $case_rus = array(
			'nom' => 'ИМ',
			'gen' => 'РД',
			'dat' => 'ДТ',
			'acc' => 'ВН',
			'ins' => 'ТВ',
			'abl' => 'ТВ',
			'pre' => 'ПР',
			'mult' => 'МН',
			'plur' => 'МН',
			'plural' => 'МН',
		);

		$cases = array();
		foreach(preg_split('/\s*,\s*/', $case) as $c)
			$cases[] = ($c2 = ec(@$case_rus[$c])) ? $c2 : $c;

		if(!($g = $m->find_word($word)))
			return $word;

		$w = $m->word_form_by_grammems($g, $cases);

		return $w ? $w : $word;
	}

	static function __unit_test($suite)
	{
		$suite->assertEquals('москву',       bors_lower(lingustics_morphology::case_rus('москва', 'acc')));
		$suite->assertEquals('компания',     bors_lower(lingustics_morphology::case_rus('компания', 'ИМ')));
		$suite->assertEquals('компанию',     bors_lower(lingustics_morphology::case_rus('компания', 'РД')));
		$suite->assertEquals('компании',     bors_lower(lingustics_morphology::case_rus('компания', 'ДТ')));
		$suite->assertEquals('компанию',     bors_lower(lingustics_morphology::case_rus('компания', 'ВН')));
		$suite->assertEquals('авиакомпанию', bors_lower(lingustics_morphology::case_rus('авиакомпания', 'acc')));
		$suite->assertEquals('авиакомпании', bors_lower(lingustics_morphology::case_rus('авиакомпания', 'plur')));
		$suite->assertEquals('авиакомпаний', bors_lower(lingustics_morphology::case_rus('авиакомпания', 'gen,plur')));
		$suite->assertEquals('авиакомпании', bors_lower(lingustics_morphology::case_rus('авиакомпания', 'nom,plur')));
	}
}
