<?php

class blib_grammar
{
	static function singular($s)
	{
		if(preg_match('/rss$/', $s, $m)) // xrss -> xrss. Исключения не трогаем
			return $s;

		if(preg_match('/^(.+)ies$/', $s, $m)) // companies -> company
			return $m[1].'y';

		if(preg_match('/^(.+ase)s$/', $s, $m)) // phases => phase
			return $m[1];

		if(preg_match('/^(.+(o|s|ch|sh))es$/', $s, $m)) // newses -> news, attaches -> attach
			return $m[1];

		if(preg_match('/^(.+)s$/', $s, $m)) // planes -> plane
			return $m[1];

		return $s;
	}

	static function __unit_test($suite)
	{
		foreach(array(
			'rss' => 'rss',
			'companies' => 'company',
			'newses' => 'news',
			'attaches' => 'attach',
			'planes' => 'plane',
			'aerodromes' => 'aerodrome',
			'kisses' => 'kiss',
			'phases' => 'phase',
			'dishes' => 'dish',
			'massages' => 'massage',
			'witches' => 'witch',
			'judges' => 'judge',
			'laps' => 'lap',
			'cats' => 'cat',
			'clocks' => 'clock',
			'cuffs' => 'cuff',
			'deaths' => 'death',
			'boys' => 'boy',
			'girls' => 'girl',
			'chairs' => 'chair',
			'heroes' => 'hero',
			'heros' => 'hero',
			'potatoes' => 'potato',
			'volcanoes' => 'volcano',
			'volcanos' => 'volcano',
			'skies' => 'sky',
		) as $plural => $singular)
			$suite->assertEquals($singular, blib_grammar::singular($plural));
	}
}
