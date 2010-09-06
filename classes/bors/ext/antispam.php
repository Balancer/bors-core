<?php

class bors_ext_antispam extends base_object
{
	private $b8;
	private $dbh;

	function __construct()
	{
		config_set('mysql_use_pool2', false);
		$this->dbh = new driver_mysql('punbb');
		$config_b8 = array('storage' => 'mysql');
		$config_database = array('database' => 'punbb', 'connection' => $this->dbh->connection());

		require_once(config('b8_include'));
		$this->b8 = new b8($config_b8, $config_database);
	}

	static function factory() { return new bors_ext_antispam(); }

	function classify($object, $trigger_spam = 1, $trigger_ham = 0)
	{
		$rating = $this->b8->classify($object->source());

		if(is_null($object->is_spam()))
		{
			if($rating > $trigger_spam)
				$object->set_is_spam(true, true);

			if($rating < $trigger_ham)
				$object->set_is_spam(false, true);
		}

		return $rating;
	}

	function rating($object)
	{
		return $this->b8->classify($object->source());
	}

	function learn_spam($object, $force = false)
	{
		if($object->is_spam() && !$force)
			return;

		$this->b8->learn($object->source(), b8::SPAM);
	}

	function learn_ham($object, $force = false)
	{
		if(!$object->is_spam() && !$force)
			return;

		$this->b8->learn($object->source(), b8::HAM);
	}

//	static function unlearn_ham($object, $force = false)
}
