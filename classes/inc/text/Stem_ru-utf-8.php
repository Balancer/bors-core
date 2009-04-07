<?php
	// Источник: http://forum.dklab.ru/php/advises/HeuristicWithoutTheDictionaryExtractionOfARootFromRussianWord.html

	class Lingua_Stem_Ru 
	{
        var $VERSION = "0.02";
        var $Stem_Caching = 0;
        var $Stem_Cache = array();
        var $VOWEL = '/аеиоуыэюя/';
		// Совершенные
        var $PERFECTIVEGROUND = '/((ив|ивши|ившись|ыв|ывши|ывшись)|((?<=[ая])(в|вши|вшись)))$/';
		// возвратные
        var $REFLEXIVE = '/(с[яь])$/';
		// Прилагательные
        var $ADJECTIVE = '/(ее|ие|ые|ое|ими|ыми|ей|ий|ый|ой|ем|им|их|ым|ых|ом|его|ему|о|ого|еых|ую|юю|ая|яя|ою|ею)$/';
		// причастия и деепричастия
        var $PARTICIPLE = '/((ивш|ывш|ующ)|((?<=[ая])(ем|нн|вш|ющ|щ)))$/';
		// глагол
        var $VERB = '/((ит|ила|ыла|ена|ейте|уйте|ите|или|ыли|ей|уй|ил|ыл|им|ым|ены|ить|ыть|ишь|ую|уют|ю)|((?<=[ая])(ла|на|ете|йте|ли|й|л|ем|н|ло|но|ет|ют|ны|ть|ешь|нно)))$/';
		// имя существительное
        var $NOUN = '/(а|ев|ов|ие|ье|е|иями|ями|ях|ам|ах|ами|еи|ии|и|ией|ей|ой|ий|й|и|у|ы|ь|ию|ью|ю|ия|ья|я|ок)$/';
        var $RVRE = '/^(.*?[аеиоуыэюя])(.*)$/';
        var $DERIVATIONAL = '/[^аеиоуыэюя][аеиоуыэюя]+[^аеиоуыэюя]+[аеиоуыэюя].*(?<=о)сть?$/';

        function s(&$s, $re, $to)
        {
            $orig = $s;
            $s = preg_replace($re.'u', $to, $s);
            return $orig !== $s;
        }

        function m($s, $re)
        {
			return preg_match($re.'u', $s);
        }

        function stem_word($word) 
        {
                $word = strtolower($word);
                $word = str_replace('ё', 'е', $word);
                # Check against cache of stemmed words
                if ($this->Stem_Caching && isset($this->Stem_Cache[$word])) {
                        return $this->Stem_Cache[$word];
                }
                $stem = $word;
                do {
                  if (!preg_match($this->RVRE.'u', $word, $p)) break;
                  $start = $p[1];
                  $RV = $p[2];
                  if (!$RV) break;

                  # Step 1
                  if (!$this->s($RV, $this->PERFECTIVEGROUND, '')) {
                          $this->s($RV, $this->REFLEXIVE, '');

                          if ($this->s($RV, $this->ADJECTIVE, '')) {
                                  $this->s($RV, $this->PARTICIPLE, '');
                          } else {
                                  if (!$this->s($RV, $this->VERB, ''))
                                          $this->s($RV, $this->NOUN, '');
                          }
                  }

                  # Step 2
                  $this->s($RV, '/и$/', '');

                  # Step 3
                  if ($this->m($RV, $this->DERIVATIONAL))
                          $this->s($RV, '/ость?$/', '');

                  # Step 4
                  if (!$this->s($RV, '/ь$/', '')) {
                          $this->s($RV, '/ейше?/', '');
                          $this->s($RV, '/нн$/', 'н'); 
                  }

                  $stem = $start.$RV;
                } while(false);
                if ($this->Stem_Caching) $this->Stem_Cache[$word] = $stem;
                return $stem;
        }

        function stem_caching($parm_ref) 
        {
                $caching_level = @$parm_ref['-level'];
                if ($caching_level) {
                        if (!$this->m($caching_level, '/^[012]$/')) {
                                die(__CLASS__ . "::stem_caching() - Legal values are '0','1' or '2'. '$caching_level' is not a legal value");
                        }
                        $this->Stem_Caching = $caching_level;
                }
                return $this->Stem_Caching;
        }

        function clear_stem_cache() 
        {
                $this->Stem_Cache = array();
        }
	}
