<?php

#require_once('bors/vhosts_loader.php');
require_once('bors/names.php');
require_once('bors/messages.php');
require_once('bors/objects_array.php');
require_once('bors/object_loader.php');
require_once __DIR__.'/../inc/bors/cross.php';
require_once __DIR__.'/../engines/smarty/global.php';

/**
 * @param string|bors_object $class_name
 * @param string|integer $object_id
 * @param array $args
 * @return mixed|null
 */
function object_load($class_name, $object_id=NULL, $args=array())
{
	if(is_object($class_name))
		return $class_name;

	// echo "object_load($class_name, $object_id, ".print_r($args, true).")\n";

	if(is_numeric($class_name))
		$class_name = class_id_to_name($class_name);

	if(config('debug.trace_object_load'))
		bors_debug::syslog('objects_load', "$class_name(".print_r($object_id, true).")", config('debug_trace_object_load_trace'));

	if(!$class_name)
		return NULL;

	//TODO: сделать отброс пробельных символов
//	if(!is_object($object_id))
//		$object_id = trim($object_id, "\n\r ");

	if(is_null($object_id) && preg_match('/^([\\\\\w]+)__(\w+)={0,}$/', $class_name, $m))
	{
		$class_name = $m[1];
		$object_id = $m[2];
		if(!is_numeric($m[2]))
		{
			if($object_id[0] == 'x')
				$object_id = substr($object_id, 1);
			$object_id = base64_decode($object_id);
		}
	}

	if(config('debug_objects_create_counting_details'))
	{
		debug_count_inc("bors_load($class_name)");
//		if(preg_match('!matf.aviaport.ru/companies/\d+/edit!', $class))
//			echo bors_debug::trace();
	}

	if($object = class_load($class_name, $object_id, $args))
		return $object;

	if($object = bors_objects_loaders_meta::object_load($class_name, $object_id))
		return $object;

	return NULL;
}

/**
 * @param string $class_name
 * @param integer|string $id
 * @return bors_object
 */
function &object_new($class_name, $id = NULL)
{
    /** @var bors_object $obj */
    $obj = new $class_name($id);

	if($id !== NULL)
	{
		$id = call_user_func(array($class_name, 'id_prepare'), $id);
		$obj->set_id($id);
	}

	$obj->b2_configure();

	return $obj;
}

function &object_new_instance($class, $id = NULL, $db_update = true, $need_check_data = false)
{
	if(is_array($id))
	{
		$data = $id;
		$id = empty($data['id']) ? NULL : $data['id'];
	}
	else
		$data = false;

	if(!class_exists($class))
		bors_throw("Class name '$class' not exists");

//	$id = call_user_func(array($class, 'id_prepare'), $id);
//	if(is_object($id))
//		bors_throw('Непонятно, что делать с id_prepare у новых объектов, когда они возвращают объект');

	$object = &object_new($class, $id);
	$object->data = $data;
	$object->changed_fields = $data;

	$object->set_owner_id(defval($data, 'owner_id', bors()->user_id()), true);
	$object->set_last_editor_id(bors()->user_id());
	$object->set_last_editor_ip(bors()->client()->ip());
	$object->set_last_editor_ua(bors()->client()->agent());

	$replace = popval($data, '*replace', false);

	if(!$object->set_fields($data, true, NULL, $need_check_data) && $need_check_data)
	{
		bors()->drop_changed_object($object);
		$object = NULL;
		return $object;
	}

	$object->set_attr('__replace_on_new_instance', $replace);

	$object->new_instance();
	$object->b2_configure();
	$object->set_is_loaded(true);
	return $object;
}

/**
 * @param bors_object $object
 */
function bors_object_new_instance_db(&$object)
{
	$tab = $object->table_name();
	if(!$tab)
		debug_exit("Try to get new db instance with empty main table");

	if(!$object->create_time(true))
		$object->set_create_time(time());

	if(!$object->modify_time(true))
		$object->set_modify_time(time());

	$object->set('owner_id', bors()->user_id());
	$object->set('owner_ip', bors()->client()->ip());
	$object->set('last_editor_id', bors()->user_id());

	$object->storage()->create($object);
	$object->changed_fields = array();
}

/**
 * @param bors_object $object
 */
function bors_db_fields_init($object)
{
	foreach($object->fields() as $db => $tables)
		foreach($tables as $tables => $fields)
			foreach($fields as $property => $db_field)
				$object->data[is_numeric($property) ? $db_field : $property] = NULL;
}

/** @var bors_global $GLOBALS */
$GLOBALS['bors_global'] = NULL;

/**
 * @return bors_global
 */
function bors()
{
	if(is_null(@$GLOBALS['bors_global']))
		if(class_exists('bors_global'))
			$GLOBALS['bors_global'] = new bors_global(NULL);
		else
			$GLOBALS['bors_global'] = false;

	return $GLOBALS['bors_global'];
}

function bors_clear() { $GLOBALS['bors_global'] = NULL; }

function bors_exit($message = '')
{
	bors_exit_handler($message);

	if(!config('do_not_exit'))
		exit();
}

function bors_exit_handler($message = '')
{
	bors_function_include('debug/trace');
	bors_function_include('debug/hidden_log');
	bors_function_include('fs/file_put_contents_lock');

	if(!empty($GLOBALS['bors_data']['php_cache_content']))
		file_put_contents_lock(config('cache_dir') . '/functions.php', $GLOBALS['bors_data']['php_cache_content']);

	if(!empty($GLOBALS['bors_data']['classes_cache_content_updated']))
	{
		bors_debug::syslog('test', "write cache", false);
		file_put_contents_lock(config('cache_dir') . '/classes.php', $GLOBALS['bors_data']['classes_cache_content']);
	}

	static $bors_exit_doing = false;
	if($bors_exit_doing)
		return true;

	$bors_exit_doing = true;

	if($message)
		echo $message;

	if(config('cache_static') && $message)
		cache_static::drop(bors()->main_object());

	try
	{
		bors()->changed_save();
	}
	catch(Exception $e)
	{
		@header('HTTP/1.1 500 Internal Server Error');
		$error = bors_lib_exception::catch_html_code($e, ec("<div class=\"red_box\">Ошибка сохранения</div>"));
	}

	$error = error_get_last();

    if ($error['type'] == 1)
    {
		@header('HTTP/1.1 500 Internal Server Error');
		if($out_dir = config('debug_hidden_log_dir'))
		{
			@mkdir(config('debug_hidden_log_dir').'/errors');
			if(file_exists(config('debug_hidden_log_dir').'/errors'))
			{
				bors_debug::syslog('errors/'.date('c'), "Handled fatal error:
		errno={$error['type']}
		errstr={$error['message']}
		errfile={$error['file']}
		errline={$error['line']}", -1, ['append' => "stack\n=====\n".debug_trace(0, false)."\n\n_SERVER=".print_r($_SERVER, true)]);
			}

		}
	}

	if(!empty($GLOBALS['bors_data']['shutdown_handlers']))
	{
		foreach($GLOBALS['bors_data']['shutdown_handlers'] as $info)
		{
			if(!empty($info['arg']))
				call_user_func($info['callback'], $info['arg']);
			elseif(!empty($info['args']))
				call_user_func_array($info['callback'], $info['args']);
			else
				call_user_func($info['callback']);
		}
	}

	if(config('debug_mysql_trace'))
	{
		$dir = config('debug_hidden_log_dir').'/mysql-trace';
		@mkdir($dir);
		@chmod($dir, 0777);
		if(file_exists($dir))
			bors_debug::syslog('mysql-trace/'.date('c').'-'.rand(0,999999), "URL={$_SERVER['REQUEST_URI']}\n".print_r(@$GLOBALS['debug_mysql_trace'], true));
	}

	if(!empty($GLOBALS['debugbar_renderer']))
		echo $GLOBALS['debugbar_renderer']->render();

	return true;
}

function bors_parse_internal_uri($uri)
{
	echo "parse $uri<Br/>";
	if(!preg_match('!^(\w+)://(.+)$!', $uri, $m))
		return array(NULL, NULL);

	if(preg_match('!^(\w+)/$!', $m[2], $mm))
		$m[2] = $mm[1];

	return array($m[1], $m[2]);
}

function bors_drop_global_caches()
{
	unset($GLOBALS['bors_data']['global']['present']);
	unset($GLOBALS['HTS_GLOBAL_DATA']);
	unset($GLOBALS['bors_data']['cached_objects4']);
}

function bors_server_var($name, $default = NULL)
{
	$sv = bors_find_first('bors_var_db', array('name' => $name, 'order' => '-create_time'));
	return $sv ? $sv->value() : $default;
}

function bors_set_server_var($name, $value, $keep_alive = -1)
{
	$sv = bors_find_first('bors_var_db', array('name' => $name, 'order' => '-create_time'));

	if(!$sv)
	{
		$sv = object_new_instance('bors_var_db');
		$sv->set_name($name);
	}

	$sv->set_value($value);
	$sv->set_expire_time($keep_alive > 0 ? time() + $keep_alive : NULL);
	$sv->store();
}

function bors_stop_bots()
{
	if(bors()->client()->is_bot())
	{
		@header("HTTP/1.0 404 Not Found");
		return go('http://balancer.ru/forum/', true);
	}

	return false;
}

function bors_throw($message)
{
	static $level = 0;

	@header('HTTP/1.1 500 Internal Server Error');

	if(config('exceptions.kill_on_throw'))
	{
		bors_debug::syslog('exception-kill', $message);
		exit('Error. See in BORS logs');
	}

	if($level++ < 1)
		bors_debug::sepalog('exceptions-unknown', $message);

	throw new Exception($message);
}

/**
 * Возвращает результат применения метода get() к объекту, если он существует.
 * $def = NULL - в противном случае.
 * @param bors_object $object
 * @param string $property
 * @param mixed|null $def
 * @return mixed|null
 */
function object_property($object, $property, $def = NULL)
{
	if(!$object)
		return $def;

	if(is_object($object))
	{
		try
		{
			// Direct call like object_property($topic, 'title');
			if(preg_match('/^\w+$/', $property))
				return $object->get($property, $def);
			// Chain call like object_property($topic, 'time()->dmy()');
			else
			{
				$x = $object;
				foreach(explode('->', $property) as $p)
				{
					if(preg_match('/^(\w+)\(\)$/', $p, $m))
					{
						if(!is_object($x))
							return $def;

						$x = $x->get($m[1], $def);
					}
					else
						throw new \Exception("Unknown property format: '$property'");
				}

                return $x;
			}
		}
		catch(Exception $e)
		{
			return $def;
		}
	}

	return $def;
}

function object_property_args($object, $property, $args = array(), $def = NULL)
{
	if(is_object($object))
		return call_user_func_array(array($object, $property), $args);

	return $def;
}

/**
 * Возвращает истину, если классы объектов и их ID совпадают.
 * @param bors_object $object1
 * @param bors_object $object2
 * @return bool
 */
function bors_eq($object1, $object2)
{
	return $object1->extends_class_name() == $object2->extends_class_name() && $object1->id() == $object2->id();
}

/**
 * Находит и возвращает объект по заданному классу и ID.
 * @param string $class_name
 * @param int|string|null $id
 * @return bors_object|null
 * @throws Exception
 */
function bors_load($class_name, $id = NULL)
{
	$object = object_load($class_name, $id);

    if(!$object && config('orm.is_strict') && !class_include($class_name))
		bors_throw("Not found class '{$class_name}' for load with id='{$id}'");

    return $object;
}

function bors_load_ex($class_name, $id, $attrs)
{
	$memcache_time = popval($attrs, 'memcache');

	if(!array_key_exists('no_load_cache', $attrs) && !$memcache_time)
		$attrs['no_load_cache'] = true;

	if($memcache_time)
	{
//if(config('is_developer')) var_dump($memcache_time);
		if(($memcache_instance = config('memcached_instance')))
		{
			debug_count_inc('memcached bors objects checks');
			unset($attrs['memcache']);
			$hash = 'bors_v'.config('memcached_tag').'_'.$class_name.'://'.$id;
			if($attrs)
				$hash .= '/'.serialize($attrs);

			if($x = unserialize($memcache_instance->get($hash)))
			{
				$updated = bors_class_loader_meta::cache_updated($x);

				if($x->can_cached() && !$updated)
				{
					debug_count_inc('memcached bors objects loads');
					return $x;
				}
			}
		}
	}

	$x = object_load($class_name, $id, $attrs);

	if($memcache_time && $memcache_instance)
		$memcache_instance->set($hash, serialize($x), 0, $memcache_time);

	return $x;
}

/**
 * @param string $uri
 * @return mixed|null
 */
function bors_load_uri($uri)
{
	static $loaded = array();

	if(!empty($loaded[$uri]))
		return $loaded[$uri];

	return $loaded[$uri] = object_load($uri);
}

/**
 * @param string $class_name
 * @param array $where
 * @return bors_object array
 */
function bors_each($class_name, $where)
{
	$storage = bors_foo($class_name)->storage();
	return $storage->each($class_name, $where);
}

function bors_new($class_name, $data = array())
{
	if(!class_exists($class_name))
		throw new Exception("Неизвестный класс ".$class_name);

	if(is_null($data))
		return object_new($class_name); // Пустой объект

	return object_new_instance($class_name, $data); // Создаём объект
}

function bors_delete($class_name, $where)
{
	//TODO: прописать в юниттесты оба варианта
	if(!array_key_exists('limit', $where))
		$where['limit'] = 1;

	if($where['limit'] === false)
		unset($where['limit']);

	foreach(bors_find_all($class_name, $where) as $x)
		$x->delete();
}


function bors_find($class_name)
{
	return new bors_core_find($class_name);
}

/**
 * @param $class_name
 * @return bors_object
 * @throws Exception
 */
function bors_foo($class_name)
{
	require_once BORS_CORE.'/inc/functions/cache/global_key.php';
	require_once BORS_CORE.'/inc/functions/cache/set_global_key.php';

	if($cached_foo = global_key('___foos', $class_name))
		return $cached_foo;

	if(!class_exists($class_name))
		bors_throw("Unknown class $class_name in bors_foo");

	$object = new $class_name(NULL);
	if(method_exists($object, 'b2_configure'))
		$object->b2_configure();
	return set_global_key('___foos', $class_name, $object);
}
