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

	// Архив чего? — объектов
	static function class_title_gen_plur($object) { return self::_class_title_helper($object, 'gen,plur'); }
	static function class_title_mult($object)
	{
		try
		{
			$mult = bors_lower(lingustics_morphology::case_rus(object_property($object, 'class_title'), 'nom,mult'));
		}
		catch(Exception $e) { }

		if(empty($mult))
			$mult = ec('объекты ').@get_class($object);

		return $mult;
	}

//	static function class_title_($object) { return bors_lower(lingustics_morphology::case_rus($object->class_title(), 'gen')); }

	private static function _class_title_helper($object, $case)
	{
		try
		{
			$title = bors_lower(lingustics_morphology::case_rus($object->class_title(), $case));
		}
		catch(Exception $e) { }

		if(empty($title))
			$title = bors_lower(lingustics_morphology::case_rus(ec('объект'), $case)).' '.@get_class($object);

		return $title;
	}
}
