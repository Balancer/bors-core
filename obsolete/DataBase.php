<?php

require_once('inc/debug.php');
require_once('engines/bors.php');
require_once('inc/global-data.php');
require_once('inc/texts.php');

class DataBase
{
	protected $dbh;
	protected $result;
	private $db_name;
	private $x1, $x2, $x3;
	private $start_time;

	private $ics;
	private $icst;
	private $dcs;
	private $need_encode;
	private $charset = NULL;

	function reconnect()
	{
		$this->close();
		$db_name = $this->db_name;

		if($this->x2)
		{
			$server   = $this->x1;
			$login    = $this->x2;
			$password = $this->x3;
			$real_db  = $db_name;
		}
		else
		{
			$real_db  = config_mysql('db_real', $db_name);
			$server   = config_mysql('server', $db_name);
			$login    = config_mysql('login', $db_name);
			$password = config_mysql('password', $db_name);
		}

		try {
			$loop = 0;
			do
			{
				if(config('mysql_persistent'))
					$this->dbh = @mysql_pconnect($server, $login, $password, config('mysql_renew_links', true));
				else
					$this->dbh = @mysql_connect($server, $login, $password, config('mysql_renew_links', true));

				if(!$this->dbh && config('mysql_try_reconnect'))
				{
					debug_hidden_log('mysql_try_reconnect', NULL, false);
					sleep(5);
				}

			} while(!$this->dbh && config('mysql_try_reconnect') && $loop++ < config('mysql_try_reconnect'));
		}
		catch(Exception $e)
		{
			bors_throw("Can't connect to mysql: ".blib_exception::factory($e));
			return;
		}

		if(!$this->dbh)
		{
			if(($err_msg_header = config('error_message_header')))
				echo $err_msg_header;

			bors_throw("mysql_connect({$server}, {$login}) to DB '{$db_name} => {$real_db}' failed ".mysql_errno().": ".mysql_error()."<br />");
		}

		set_global_key("DataBaseHandler:{$server}", $db_name, $this->dbh);
		set_global_key("DataBaseStartTime:{$server}", $db_name, $this->start_time = time());

		if(!mysql_select_db($real_db, $this->dbh))
		{
			debug_hidden_log('mysql_error', $msg = "Could not select database ".($real_db ? "'{$db_name}' as '{$real_db}'" : "'{$db_name}'. <br/>\nError ").mysql_errno($this->dbh).": ".mysql_error($this->dbh)." <br />\n");
			bors_throw($msg, 1);
		}

		if($c = config('mysql_set_character_set'))
		{
			debug_timing_start('mysql_set_character_set');
			$this->query("SET CHARACTER SET '$c'");
			debug_timing_stop('mysql_set_character_set');
		}

		if(($c = config('mysql_set_names_charset')) && $this->charset != $c)
		{
			debug_timing_start('mysql_set_names');
			mysql_set_charset($c, $this->dbh);
			$this->query("SET NAMES '$c'");
			$this->charset = $c;
//			echo debug_trace();
			debug_timing_stop('mysql_set_names');
		}
	}

	function __construct($base=NULL, $login=NULL, $password=NULL, $server=NULL) // DataBase
	{
		$this->ics = config('internal_charset');
		$this->dcs = config('db_charset');
		if($this->need_encode = ($this->ics != $this->dcs))
		{
			$this->icsi = $this->ics.'//IGNORE';
			$this->dcsi = $this->dcs.'//IGNORE';
		}

		$real_db = config_mysql('db_real', $base);

		// Если меняли БД, то переустановим charset — это может быть совсем другой сервер.
		if($this->db_name != $base)
			$this->charset = NULL;

		$this->db_name = $base;

		if(!$base)
			bors_throw('MySQL driver error: try to DataBase construct without database name');

		$this->x1 = $server;
		$this->x2 = $login;
		$this->x3 = $password;

		if(!$login)
			$server   = config_mysql('server', $base);

		if(config('mysql_use_pool2')
			&& global_key("DataBaseHandler:$server", $base)
			&& (time() - global_key("DataBaseStartTime:$server", $base) < 7 )
			&& $this->db_name == $base
		)
		{
			debug_count_inc('mysql_resume_connections');

			$this->dbh = global_key("DataBaseHandler:$server", $base);
			$this->start_time = global_key("DataBaseStartTime:$server", $base);

			if(!$this->dbh)
			{
				echolog(__FILE__.':'.__LINE__." Can't init connection to '$base' (".mysql_errno()."): ".mysql_error()."<BR />", 1);
				bors_exit();
			}

			if(!mysql_select_db($real_db, $this->dbh))
			{
				bors_throw(__FILE__.':'.__LINE__." Could not select database '$base' (".mysql_errno($this->dbh)."): ".mysql_error($this->dbh)."<BR />", 1);
			}
		}
		else
		{
			debug_count_inc('mysql_new_connections');
			$this->reconnect();
		}

		if(!$this->dbh)
			debug_exit(" NULL DBH ".mysql_errno().": ".mysql_error()."<BR />");
	}

	var $last_query_time;
	function query($query, $ignore_error=false, $reenter = false)
	{
		if(($watch = config('mysql.queries_watch_regexp')) && preg_match($watch, $query))
			debug_hidden_log('mysql-queries-log', $query);

		if(config('mysql_trace_show'))
			print_dd($query);

		if(!$query)
			return;

		if(!$this->dbh || time() - $this->start_time > 10)
			$this->reconnect();

		debug_count_inc('mysql_queries');

		if($this->need_encode)
			$query = iconv($this->ics, $this->dcsi, $query);

		$qstart = microtime(true);

		debug_timing_start('mysql_query_main');
		$this->result = !empty($query) ? mysql_query($query,$this->dbh) : false;
		debug_timing_stop('mysql_query_main');

		$qtime = microtime(true) - $qstart;

		if($qtime > config('debug_mysql_slow', 5))
			debug_hidden_log('mysql-queries-slow', "Slow query [{$this->db_name} {$qtime}s]: ".$query);

		if($type = config('debug.trace_queries'))
		{
			echo "q=$query<br/>";
//			if(preg_match('/select.*from.*forums/i', $query)) echo debug_trace();
//			if(preg_match('/select id from posts/i', $query)) echo debug_trace();
		}

		if($cdmql = config('debug_mysql_queries_log'))
		{
			if($cdmql == 'false')
				$cdmql = false;

			debug_hidden_log(
				'mysql-queries',
				"[{$this->db_name}, ".sprintf('%.1f', $qtime*1000.0)."ms]: ".$query,
				$cdmql,
				array('dont_show_user' => true)
			);
		}

		if(config('debug_mysql_trace'))
		{
			$GLOBALS['debug_mysql_trace'][] = array(
				'query' => $query,
				'time' => $qtime,
				'trace' => debug_trace(0, false),
			);
		}

//		if(config('debug_mysql_queries_trace'))
//			@$GLOBALS['debug_mysql_queries_trace'][] = "[{$this->db_name}, ".sprintf('%.1f', $qtime*1000.0)."ms]: ".$query;

		if($this->result)
		{
			$this->last_query_time = microtime(true);

			if(preg_match("!^SELECT!", $query))
				return mysql_num_rows($this->result);
			else
				return $this->result;
		}

		if(!$ignore_error)
		{
			$err_msg_header = config('error_message_header');

			bors_throw($err_msg_header.ec("<br/>Ошибка MySQL: ").mysql_error($this->dbh)
				.(config('site.is_dev') ?
					"<pre style=\"color: blue\">DB={$this->db_name}\nquery={$query}</pre>" :
					"<!-- DB={$this->db_name}\nquery={$query} -->"
				)
			);
		}

		$this->last_query_time = microtime(true);
		return false;
	}

	function link() { return $this->dbh; }

	function free()
	{
		mysql_free_result($this->result);
	}

	protected $__current_value;

	function fetch()
	{
		if(!$this->result)
			return $this->__current_value = false;

		if(!($row = mysql_fetch_assoc($this->result)))
			return $this->__current_value = false;

		if(empty($GLOBALS['bors_data']['config']['gpc']))
		{
			if(sizeof($row)==1)
			{
				if($this->need_encode)
				{
					foreach($row as $s)
						$row = iconv($this->dcs, $this->icsi, $s);
				}
				else
				{
					foreach($row as $s) // Фактически это $row = array_pop(array_values($row)). Нужно будет поискать оптимальный вариант.
						$row = $s;
				}
			}
			else
			{
				if($this->need_encode)
				{
//					echo "{$this->dcs} -> {$this->icsi}<br/>";
					$dcs  = $this->dcs;
					$icsi = $this->icsi;
//					Вариант с array_map получается НАМНОГО медленнее цикла.
//					$row = array_map(create_function('$x', 'return iconv("'.$dcs.'", "'.$icsi.'", $x);'), $row);
					foreach($row as $k => $v)
						$row[$k] = @iconv($dcs, $icsi, $v);
				}
			}

			return $this->__current_value = $row;
		}

		// А к этому месту у нас включен magic_quote_gpc. То же, что выше, но с деквотингом.
		if(sizeof($row)==1)
			foreach($row as $s)
			{
				if($this->need_encode)
					$s = iconv($this->dcs, $this->icsi, $s);
				$row = quote_fix($s);
			}
		else
			foreach($row as $k => $v)
			{
				if($this->need_encode)
					$v = iconv($this->dcs, $this->icsi, $v);
				$row[$k] = quote_fix($v);
			}

		return $this->__current_value = $row;
	}

	function fetch_row()
	{
		return mysql_fetch_assoc($this->result);
	}

	function fetch1()
	{
		if(!$this->result)
			return false;

		if(!($row = mysql_fetch_assoc($this->result)))
			return false;

		if(empty($GLOBALS['bors_data']['config']['gpc']))
		{
			foreach($row as $s)
				$row = $s;

			return $row;
		}

		foreach($row as $s)
			$row = quote_fix($s);

		return $row;
	}

	function get($query, $ignore_error=false, $cached=false)
	{
		$ch = NULL;
		if($cached !== false)
		{
			$ch = new Cache();
			if($ch->get("DataBaseQuery:{$this->db_name}", $query) !== NULL)
				return unserialize($ch->last());
		}

		$this->query($query, $ignore_error);
		$row = $this->fetch();
		$this->free();

		if($ch)
			$ch->set(serialize($row), $cached);

		return $row;//  set_global_key("db_get", $query, $row);
	}

	function get1($query, $ignore_error=false)
	{
		$this->query($query, $ignore_error);
		$row = $this->fetch1();
		$this->free();

		return $row;
	}

	function get_value($table, $key_search, $value, $key_res)
	{
		if(is_global_key("get_value($table,$key_search,$value)",$key_res))
		return global_key("get_value($table,$key_search,$value)",$key_res);

		return set_global_key("get_value($table,$key_search,$value)",$key_res,
		$this->get("SELECT `$key_res` FROM `$table` WHERE `$key_search`='".addslashes($value)."'"));
	}

	function loop($func, $query)
	{
		$this->query($query);

		while(($row = $this->fetch()) !== false)
			$func($row);

		$this->free();
	}

	function get_array($query, $ignore_error=false, $cached=false, $index_field = NULL)
	{
		include_once("classes/Cache.php");
		$ch = NULL;
		if($cached !== false && class_exists('Cache'))
		{
			$ch = new Cache();
			if($ch->get("DataBaseQuery:{$this->db_name}-v2", $query))
				return $ch->last();
		}

		$res=array();

		$this->query($query, $ignore_error);

		if($index_field)
		{
			while(($row = $this->fetch()) !== false)
			{
				$idx = popval($row, $index_field);
				if(count($row) == 1)
					$row = array_pop($row);

				$res[$idx] = $row;
			}
		}
		else
		{
			while(($row = $this->fetch()) !== false)
				$res[] = $row;
		}

		$this->free();

		if($ch)
			$ch->set($res, $cached);

		return $res;
	}

	function make_string_values($array, $with_keys = true)
	{
		$values = array();
		if($with_keys)
		{
			$keys = array();
			foreach($array as $k => $v)
			{
				$this->normkeyval($k, $v);
				$keys[] = $k;
				$values[] = $v;
			}

			return " (".join(",", $keys).") VALUES (".join(",", $values).") ";
		}
		else
		{
			foreach($array as $k => $v)
			{
				$this->normkeyval($k, $v);
				$values[] = $v;
			}

			return " (".join(",", $values).") ";
		}
	}

	function make_string_set($array)
	{
		$set = array();

		foreach($array as $k => $v)
		{
			$this->normkeyval($k, $v);
			$set[] = "$k = $v";
		}
		return " SET ".join(",", $set)." ";
	}

	function normkeyval(&$key, &$value)
	{
		if($key[0] == 'i' && preg_match('!^int (.+)$!', $key, $m))
		{
			$key = ($m[1][0] == '`') ? $m[1] : '`'.$m[1].'`';
			return;
		}

		if(preg_match('/^(\S+) (.+?)$/', $key, $m))
		{
			$type = $m[1];
			$key = $m[2];
		}
		else
		{
			$value = is_null($value) ? "NULL" : "'".mysql_real_escape_string($value, $this->dbh)."'";

			if($key[0] != '`')
				$key = "`$key`";

			return;
		}

		if($value === NULL)
			$value = "NULL";
		else
		{
			switch($type)
			{
				case 'raw':
					break;
				case 'float':
					$value = str_replace(',', '.', floatval(str_replace(',', '.', $value)));
					break;
				default:
					$value = "'".mysql_real_escape_string($value, $this->dbh)."'";
			}
		}

		if($key[0] != '`')
			$key = "`$key`";
	}

	function insert($table, $fields, $ignore_error = false)
	{
		if(!empty($fields['*DELAYED']))
		{
			unset($fields['*DELAYED']);
			$DELAYED="DELAYED ";
		}
		else
			$DELAYED="";

		$this->query("INSERT {$DELAYED}INTO $table ".$this->make_string_values($fields), $ignore_error);
	}

	function insert_ignore($table, $fields)
	{
		$this->query("INSERT IGNORE $table ".$this->make_string_values($fields));
	}

	function replace($table, $fields)
	{
		$this->query("REPLACE $table ".$this->make_string_values($fields));
	}

	var $insert_buffer;

	function multi_insert_init($table) { $this->insert_buffer[$table] = array(); }

	function multi_insert_add($table, $fields)
	{
		if(empty($this->insert_buffer[$table]))
			$this->insert_buffer[$table][] = $this->make_string_values($fields);
		else
			$this->insert_buffer[$table][] = $this->make_string_values($fields, false);
	}

	function multi_insert_do($table)
	{
		if(!empty($this->insert_buffer[$table]))
			$this->query("INSERT INTO $table ".join(",", $this->insert_buffer[$table]));

		unset($this->insert_buffer[$table]);
	}

	function multi_insert_ignore($table)
	{
		if(!empty($this->insert_buffer[$table]))
		$this->query("INSERT IGNORE $table ".join(",", $this->insert_buffer[$table]));

		unset($this->insert_buffer[$table]);
	}
	function multi_insert_replace($table)
	{
		if(!empty($this->insert_buffer[$table]))
		$this->query("REPLACE $table ".join(",", $this->insert_buffer[$table]));

		unset($this->insert_buffer[$table]);
	}

	//TODO: Change 'where' to array-type
	function store($table, $where, $fields, $append=false)
	{
		if(!$append)
		$n = $this->query("SELECT * FROM `".addslashes($table)."` WHERE $where");

		if(!$append && $n>0)
			$res = $this->query("UPDATE `".addslashes($table)."` ".$this->make_string_set($fields)." WHERE $where");
		else
			$res = $this->query("REPLACE INTO `".addslashes($table)."` ".$this->make_string_values($fields));

		if($res === false)
		{
			#				mysql_query ("REPAIR TABLE `$table`");
			echo("Invalid query: " . mysql_error($this->dbh) ." ");
			//				die(__FILE__.":".__LINE__." Error and try autorepair ('$table','$where','$fields').");
		}
	}

	function update($table, $where, $fields)
	{
		$res = $this->query("UPDATE `".addslashes($table)."` ".$this->make_string_set($fields)." WHERE $where");
	}

	function update_low_priority($table, $where, $fields)
	{
		$res = $this->query("UPDATE LOW_PRIORITY `".addslashes($table)."` ".$this->make_string_set($fields)." WHERE $where");
	}

	//TODO: Change 'where' to array-type
	function store_array($table, $where, $fields_array)
	{
		$n=$this->query("SELECT * FROM `".addslashes($table)."` WHERE $where LIMIT 1");

		if($n>0)
		{
			$q="DELETE FROM `".addslashes($table)."` WHERE $where";
			$this->query($q);
		}

		foreach($fields_array as $fields)
		{
			$q="INSERT INTO `".addslashes($table)."` ".$this->make_string_values($fields);
			$res=$this->query($q);
			if($res === false)
			die(__FILE__.':'.__LINE__." Invalid query '$q': " . mysql_error($this->dbh));
		}
	}

	function last_id()
	{
		return mysql_insert_id($this->dbh);
	}

	static function instance($db = NULL) { return new DataBase($db); }
	function close()
	{
		if(!$this->dbh)
			return;

//		mysql_close($this->dbh);
		$this->dbh = NULL; 
	}

/*	public function __sleep()
	{
		if(!$this->dbh)
			return;

//		mysql_close($this->dbh);
		debug_hidden_log("SerializeOfDataBase");
		return array_keys(get_object_vars($this));
	}
*/
	function can_cached() { return false; }
}
