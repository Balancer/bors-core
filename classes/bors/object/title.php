<?php

class bors_object_title
{
	static function class_title_gen($object) { return lingustics_morphology::case_rus($object->class_title(), 'gen'); }
}
