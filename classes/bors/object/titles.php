<?php

class bors_object_titles
{
/*
	Русское			Латинское название			Вспомогательные 	Характеризующий вопрос
	название									слова
	----------------------------------------------------------------------------------
	Именительный	Номинатив	(Nominative) 	Есть 				Кто? Что?
	Родительный 	Генитив		(Genitive) 		Нет 				Кого? Чего?
	Дательный 		Датив		(Dative) 		Давать 				Кому? Чему?
	Винительный 	Аккузатив	(Accusative) 	Винить 				Кого? Что?
	Творительный 	Аблатив		(объединение) 	Доволен/Сотворён 	Кем? Чем?
	Предложный 		Препозитив	(Prepositional)	Думать 				О ком? О чём?; В ком? В чём?
*/

	static function class_title_gen($object) { return self::_class_title_helper($object, 'gen'); }
	static function class_title_dat($object) { return self::_class_title_helper($object, 'dat'); }
	static function class_title_acc($object) { return self::_class_title_helper($object, 'acc'); }
	static function class_title_abl($object) { return self::_class_title_helper($object, 'abl'); }
	static function class_title_pre($object) { return self::_class_title_helper($object, 'pre'); }

	// Архив чего? — объектов
	static function class_title_gen_plur($object) { return self::_class_title_helper($object, 'gen,plur'); }
	static function class_title_dat_plur($object) { return self::_class_title_helper($object, 'dat,plur'); }
	static function class_title_abl_plur($object) { return self::_class_title_helper($object, 'abl,plur'); }
	static function class_title_plur($object)
	{
		if(ini_get('default_charset') != 'utf-8')
			bors_throw(ec('phpMorphy работает только в UTF-8'));

		try
		{
			$plur = bors_lower(lingustics_morphology::case_rus(object_property($object, 'class_title'), 'plur'));
		}
		catch(Exception $e) { }

		if(empty($plur))
			$plur = ec('объекты ').@get_class($object);

		return $plur;
	}

//	static function class_title_($object) { return bors_lower(lingustics_morphology::case_rus($object->class_title(), 'gen')); }

	private static function _class_title_helper($object, $case)
	{
		if($title = $object->class_cache_data($cache_key = 'class-title-case-'.$case))
			return $title;

		try
		{
			$title = bors_lower(lingustics_morphology::case_rus($object->class_title(), $case));
		}
		catch(Exception $e) { }

		if(empty($title))
			$title = bors_lower(lingustics_morphology::case_rus(ec('объект'), $case)).' '.@get_class($object);

		$object->set_class_cache_data($cache_key, $title);
		return $title;
	}
}
