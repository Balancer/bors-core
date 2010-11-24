<?php

/**
	Библиотеки для поиска
*/

class bors_search extends base_null
{
	/**
		Размечает фрагмет текста $text, подсвечивая ключевые слова
		из массива $keywords с учётом морфологии.
	*/

	static function snippet($text, $keywords, $around = array('<b>', '</b>'))
	{
		$result = array();

		$keywords = self::normalize($keywords);
		list($pre, $post) = $around;
//		var_dump($keywords);
		foreach(self::normalize_hash($text) as $word_orig => $word_norm)
		{
//			echo "test '$word_norm'\n";
			if(in_array($word_norm, $keywords))
				$result[] = $pre.$word_orig.$post;
			else
				$result[] = $word_orig;

		}

		return join(' ', $result);
	}

	static function words_split($text)
	{
		return preg_split('/[\s\.\,\-]+/', $text);
	}

	static function normalize($words)
	{
		$keywords = array();
		$Stemmer = new Lingua_Stem_Ru();

		$words = array_filter(self::words_split(bors_lower($words)));
		foreach($words as $word)
			$keywords[] = $Stemmer->stem_word(bors_lower($word));

		return $keywords;
	}

	static function normalize_hash($words)
	{
		$keywords = array();
		$Stemmer = new Lingua_Stem_Ru();

		$words = array_filter(self::words_split($words));
		foreach($words as $word)
			$keywords[$word] = $Stemmer->stem_word(bors_lower($word));

		return $keywords;
	}
}
