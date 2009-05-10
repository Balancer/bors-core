<?php
require_once('inc/debug.php');
require_once('engines/bors.php');
require_once('inc/global-data.php');
require_once('inc/texts.php');

class DataBase extends base_object
{
	var $dbh;
	var $result;
	var $row;
	var $db_name;
	var $x1, $x2, $x3;
	var $start_time;

	function reconnect()
	{
		$this->close();

		$loop = 0;
		do
		{
			if(config('mysql_persistent'))
				$this->dbh = @mysql_pconnect($this->x1, $this->x2, $this->x3, config('mysql_renew_links'));
			else
				$this->dbh = @mysql_connect($this->x1, $this->x2, $this->x3, config('mysql_renew_links'));

			if(!$this->dbh && config('mysql_try_reconnect'))
			{
				debug_hidden_log('mysql_try_reconnect', NULL, false);
				sleep(5);
			}

		} while(!$this->dbh && config('mysql_try_reconnect') && $loop++ < config('mysql_try_reconnect'));

		if(!$this->dbh)
		{
			echo("mysql_connect({$this->x1}, {$this->x2}) to DB '{$this->db_name}' failed ".mysql_errno().": ".mysql_error()."<BR />");
			bors_exit();
			exit();
		}

		set_global_key("DataBaseHandler:{$this->x1}", $this->db_name, $this->dbh);
		set_global_key("DataBaseStartTime:{$this->x1}", $this->db_name, $this->start_time = time());

		if(!mysql_select_db($this->db_name, $this->dbh))
		{
			echolog(__FILE__.':'.__LINE__." Could not select database '{$this->db_name}' (".mysql_errno($this->dbh)."): ".mysql_error($this->dbh)."<BR />", 1);
			bors_exit();
		}

		if($c = config('mysql_set_character_set'))
		{
			debug_timing_start('mysql_set_character_set');
			$this->query("SET CHARACTER SET '$c'");
			debug_timing_stop('mysql_set_character_set');
		}

		if($c = config('mysql_set_names_charset'))
		{
			debug_timing_start('mysql_set_names');
			mysql_set_charset($c, $this->dbh);
			$this->query("SET NAMES '$c'");
			debug_timing_stop('mysql_set_names');
		}
	}

	function __construct($base=NULL, $login=NULL, $password=NULL, $server=NULL) // DataBase
	{
		$this->db_name = $base;
			
		if(!$base)
			debug_exit('Empty database construct');

		if(empty($login))	$login		= @$GLOBALS['cms']['mysql'][$base]['login'];
		if(empty($password)) $password	= @$GLOBALS['cms']['mysql'][$base]['password'];
		if(empty($server))   $server	= @$GLOBALS['cms']['mysql'][$base]['server'];

		if(empty($login))	$login		= @$GLOBALS['cms']['mysql_login'];
		if(empty($password)) $password	= @$GLOBALS['cms']['mysql_pw'];
		if(empty($server))   $server	= @$GLOBALS['cms']['mysql_server'];

		if(empty($server))   $server	= 'localhost';

		$this->x1 = $server;
		$this->x2 = $login;
		$this->x3 = $password;
			
		if(config('mysql_use_pool2') 
			&& is_global_key("DataBaseHandler:$server", $base) 
			&& (time() - global_key("DataBaseStartTime:$server", $base) < 7 )
			&& $this->db_name == $base
		)
		{
			
			debug_count_inc('mysql_resume_connections');

			$this->dbh = global_key("DataBaseHandler:$server", $base);
			$this->start_time = global_key("DataBaseStartTime:$server", $base);

			if(!mysql_select_db($base, $this->dbh))
			{
				echolog(__FILE__.':'.__LINE__." Could not select database '$base' (".mysql_errno($this->dbh)."): ".mysql_error($this->dbh)."<BR />", 1);
				bors_exit();
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
	function query($query, $ignore_error=false)
	{
		if(!$query)
			return;

		if(!$this->dbh || time() - $this->start_time > 10)
			$this->reconnect();

		debug_count_inc('mysql_queries');

		$ics = config('internal_charset');
		$dcs = config('db_charset');

		if($ics != $dcs)
			$query = iconv($ics, $dcs.'//IGNORE', $query);

		$qstart = microtime(true);
			
		debug_timing_start('mysql_query_main');
		$this->result = !empty($query) ? @mysql_query($query,$this->dbh) : false;
		debug_timing_stop('mysql_query_main');

		$qtime = microtime(true) - $qstart;

		if($qtime > config('debug_mysql_slow', 5))
			debug_hidden_log('mysql-slow', "Slow query [{$qtime}s]: ".$query);
			
		if(config('debug_mysql_queries_log'))
			debug_hidden_log('mysql-queries', "[{$this->db_name}, ".sprintf('%.1f', $qtime*1000.0)."ms]: ".$query);
			
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
			echo debug_trace();
			bors_exit("MySQL Error: driver class=".get_class($this)."<br>\n"
				."now=".date('r')."<br>\n"
				."dbh={$this->dbh}; <br/>\n"
				."query=<span style=\"color:blue\">{$query}</span><br/>\n"
				."error=<span style=\"color:red\">".mysql_error($this->dbh)."</span><br/>\n"
				."db name={$this->db_name}<br/>\n"
				.'time from last query: '.((microtime(true) - $this->last_query_time)/1000000)."<br/>\n"
				.'was connected at '.date('r', $this->start_time)." ({$this->start_time})");
		}

		$this->last_query_time = microtime(true);
		return false;
	}

	function free()
	{
		@mysql_free_result($this->result);
	}

	function fetch()
	{
		$ics = config('internal_charset');
		$dcs = config('db_charset');

		if(!$this->result)
			return false;

		if(!($row = mysql_fetch_assoc($this->result)))
			return false;

		if(empty($GLOBALS['bors_data']['config']['gpc']))
		{
			if(sizeof($row)==1)
			{
				foreach($row as $s)
				{
					if($ics != $dcs)
						$s = iconv($dcs, $ics.'//IGNORE', $s);
					$row = $s;
				}
			}
			else
			{
				foreach($row as $k => $v)
				{
					if($ics != $dcs)
						$v = iconv($dcs, $ics.'//IGNORE', $v);
					$row[$k] = $v;
				}
			}

			return $row;
		}

		if(sizeof($row)==1)
			foreach($row as $s)
			{
				if($ics != $dcs)
					$s = iconv($dcs, $ics.'//IGNORE', $s);
				$row = quote_fix($s);
			}
		else
			foreach($row as $k => $v)
			{
				if($ics != $dcs)
					$v = iconv($dcs, $ics.'//IGNORE', $v);
				$row[$k] = quote_fix($v);
			}
			
		return $row;
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
			$ch = &new Cache();
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

	function get_array($query, $ignore_error=false, $cached=false)
	{
		include_once("classes/Cache.php");
		$ch = NULL;
		if($cached !== false && class_exists('Cache'))
		{
			$ch = &new Cache();
			if($ch->get("DataBaseQuery:{$this->db_name}-v2", $query))
				return $ch->last();
		}

		$res=array();
		//			$found = false;
			
		$this->query($query, $ignore_error);
			
		while(($row = $this->fetch()) !== false)
			$res[]=$row;

		$this->free();

		if($ch/* && $found*/)
			$ch->set($res, $cached);

		return $res;
	}

	function make_string_values($array, $with_keys = true)
	{
		$keys=array();
		$values=array();
		foreach($array as $k => $v)
		{
			$this->normkeyval($k, $v);
			$keys[] = $k;
			$values[] = $v;
		}
			
		if($with_keys)
		return " (".join(",", $keys).") VALUES (".join(",", $values).") ";
		else
		return " (".join(",", $values).") ";
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
		//			if(preg_match("!^\s+(.+)$!", $key, $m))
		//				$key = $m[1];

		//			if(preg_match("!^(.+)\s+$!", $key, $m))
		//				$key = $m[1];

		if(preg_match('!^int (.+)$!', $key, $m) && is_numeric($value))
		{
			$key = preg_match('!^`!', $m[1]) ? $m[1] : '`'.$m[1].'`';
			return;
		}

		@list($type, $key) = split(' ', $key);
		if(empty($key))
		{
			$key = $type;
			$type = 'default';
		}

		if($value === NULL)
		$value = "NULL";
		else
		switch($type)
		{
			case 'raw':
				break;
				//					case 'int':
				//						if(!preg_match('!^0x[\da-fA-F]+$!', $value))
				//							if(!preg_match('!^\d+$!', $value))
				//								$value = intval($value);
				//						break;
			case 'float':
				$value = str_replace(',', '.', floatval($value));
				break;
			default:
				$value = "'".addslashes($value)."'"; // mysql_real_escape_string
		}
			
		if(!preg_match('!^`!', $key))
		$key = "`$key`";
	}

	function insert($table, $fields, $ignore_error = false)
	{
		$this->query("INSERT INTO $table ".$this->make_string_values($fields), $ignore_error);
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
