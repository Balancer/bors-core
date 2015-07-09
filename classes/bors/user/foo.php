<?php

class bors_user_foo extends bors_object
{
	// Чтобы не пыталось сохранять доступ.
	function set_last_visit_time() { return NULL; }
}
