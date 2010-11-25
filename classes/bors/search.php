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

	static function snippet($text, $keywords, $limit, $max_length, $around = array('<b>', '</b>'))
	{
		$result = array();
static $cnt=0;
		$keywords = self::normalize($keywords);
		list($pre, $post) = $around;
//		var_dump($keywords);
		$prev = array();
		$after = false;

//		echo "<xmp>$text</xmp>";
//		if($cnt++<3)
//			echo "<b>after=$after, is_kw=$is_kw, prev=".print_r($prev, true)."</b><br/>";
//print_d($keywords);
		foreach(self::normalize_hash($text) as $word_orig => $word_norm)
		{
//			if($cnt++<100)
//				echo "$word_orig => $word_norm, after=$after, is_kw=$is_kw, prev=".print_r($prev, true)."<br/>";

			$is_kw = false;
//			echo "test '$word_norm'\n";
			if(in_array($word_norm, $keywords))
			{
				$prev[] = $pre.$word_orig.$post;
				$is_kw = true;
			}
			else
				$prev[] = $word_orig;

			if($is_kw)
			{
				if($after)
				{
					$result = array_merge($result, $prev);
					$prev = array();
				}
				else
				{
					$result = array_merge($result, count($prev) > $limit ? array('&#133') : array(), array_slice($prev, -$limit-1));
					$prev = array();
				}

				$after = true;
			}
			else
			{
				if($after && count($prev) >= $limit)
				{
					$after = false;
					$result = array_merge($result, $prev);
					$prev = array();
				}
			}
		}

		if($after)
			$result = array_merge($result, array('&#133'), $prev);

		return strip_text(join(' ', $result).'&#133;', $max_length);
	}

	static function words_split($text)
	{
		return preg_split('/[\s\.,\-"«»\/<>]+/u', $text);
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
