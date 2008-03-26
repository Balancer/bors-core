<?
	require_once ("DataBase.php");
	require_once ("engines/bors.php");
	require_once ("inc/global-data.php");

	define('DBHPROTOMASK', '!^([\w/]+)://(.*[^/])/?$!');

class DataBaseHTS
{
		var $dbh;
		var $uri;

		function instance($uri)
		{
			return new DataBaseHTS($uri);
		}

	function DataBaseHTS($data = NULL)
	{
		if($data && is_array($data))
		{
			$this->uri = @$data['uri'];
			$this->dbh = &new DataBase(@$data['db']);
		}
		else
		{
			if(preg_match("!^/!", $data))
				$data = "http://{$_SERVER['HTTP_HOST']}{$data}";
		
			if(preg_match("!^[\w]+://!", $data))
			{
				$this->uri = $data;
				$this->dbh = &new DataBase('HTS');
			}
			else
			{
				$this->uri = NULL;
				if(!$data)
					$data = 'HTS';
					
				$this->dbh = &new DataBase($data);
			}
		}
		
		if (!$this->dbh)
			exit (__FILE__.__LINE." Can't create DataBase class");
	}

	function normalize_uri($uri, $base_page = '')
	{
		if ($base_page)
		{
			if (preg_match("!^http://!", $uri))
				return $this->normalize_uri($uri);

			if (!preg_match("!^/!", $uri))
				$uri = $base_page.$uri;

			return $this->normalize_uri("http://{$_SERVER['HTTP_HOST']}$uri");
		}

		$orig_uri = $uri;

		list ($uri, $params) = split("\?", $uri.'?');

		if (is_global_key('normalize_uri', $uri))
			return global_key('normalize_uri', $uri);

		// Удаляем двойные слеши в адресе (кроме http://)
		$uri = preg_replace("!([^:])//+!", "$1/", $uri);

		// Если последний символ не '/' и оканчивается не на имя файла
		if (!preg_match("!/$!", $uri))
			if (preg_match("!^.*/[^\\.\\\\]+?$!", $uri))
				$uri .= '/';

		$uri = preg_replace("!^{$GLOBALS['DOCUMENT_ROOT']}/+!", "/", $uri);


		if(preg_match("!^/!", $uri))
			$uri = 'http://'.$_SERVER['HTTP_HOST'].$uri;

		$uri = preg_replace("!/index\.\w+$!", "/", $uri);
		$uri = preg_replace("!(/\w+)\.(phtml|hts)$!", "$1.php", $uri);
		$uri = preg_replace("!^http://www\.!i", "http://", $uri);

		$save_log_level = isset ($GLOBALS['log_level']) ? $GLOBALS['log_level'] : NULL;
		$GLOBALS['log_level'] = 0;

		$m = array ();
		if (preg_match("!^http://([^/]+)(/.*?)([^/]*)$!", $uri, $m))
		{
			$host = $m[1];
			$path = $m[2];
			$file = $m[3];

			if ($to_host = $this->dbh->get_value('hts_host_redirect', 'from', $host, 'to'))
				$host = preg_replace("!^$host$!i", "$to_host", $host);

			$uri = "http://$host$path$file";
		}

		if($alias = $this->dbh->get_value('hts_aliases', 'alias', $uri, 'uri'))
			$uri = $alias;

		$GLOBALS['log_level'] = $save_log_level;

//		echolog("Normalize uri '$orig_uri' to '$uri'");

		return set_global_key('normalize_uri', $uri, $uri);
	}

	function clear_data_cache($uri, $key, $default = NULL, $inherit = false, $skip = false)
	{
		clear_global_key("uri_data($uri,$inherit,$skip)", $key);
	}

	function pre_data_check($uri, $key)
	{
//		if (!empty ($_GET['debug']))
//			echo "<small>pre_data_check('$uri', '$key')</small><br/>\n";

		if (empty ($GLOBALS['cms']['data_prehandler'][$key]))
			return false;

		if(is_global_key("uri_data($uri)", $key)) 
			return global_key("uri_data($uri)", $key);

		$m = array ();
		foreach ($GLOBALS['cms']['data_prehandler'][$key] as $regexp => $data)
		{
//			echo "$regexp<br />";
			
			if (preg_match($regexp, $uri, $m))
				if (($res = $data['func'] ($uri, $m, $data['plugin_data'], $key)) != NULL)
				{
//					echo "Pre data ($key, $uri, ".print_r($data, true).") = $res<br/>";
//					if($key == 'nav_name')
//						echo "=$key($uri)=$regexp={$data['func']}= -> $res<br />\n";
					return set_global_key("uri_data($uri)", $key, $res);
				}
		}
		return false;
	}

	function post_data_check($uri, $key)
	{
		if(($ret = $this->_post_data_check($uri, $key, $key)) !== false)
			return $ret;

		return $this->_post_data_check($uri, $key, '*');
	}

	function _post_data_check($uri, $key, $idx)
	{
//		if (!empty ($_GET['debug']))
//			echo "<small>post_data_check('$uri', '$key', '$idx')</small><br/>\n";

		if (empty ($GLOBALS['cms']['data_posthandler'][$idx]))
			return false;

		if(is_global_key("uri_data($uri)", $key)) 
			return global_key("uri_data($uri)", $key);

		$m = array ();
//		print_r($GLOBALS['cms']['data_posthandler'][$idx]);
		foreach ($GLOBALS['cms']['data_posthandler'][$idx] as $regexp => $array)
		{
			foreach($array as $data)
			{
//				echo "<xmp>"; print_r($data); echo "</xmp>";
//			echo "Check post_data_check($uri, $key) for $regexp<br/>\n";
				if (preg_match($regexp, $uri, $m))
				{
//					echo "Match for $key/$uri: {$data['func']}<br />";
					if (($res = $data['func'] ($uri, $m, $data['plugin_data'], $key)) !== NULL)
					{
//						if (!empty ($_GET['debug']))
//							echo "<small>post_data_check return $res</small><br/>\n";
						return set_global_key("uri_data($uri)", $key, $res);
					}
				}
			}
		}
		return false;
	}

	function get($uri, $key = NULL)
	{
		if($key == NULL)
			return $this->get_data($this->uri, $uri);
		else
			return $this->get_data($uri, $key);
	}

	function check_uri_handler($uri)
	{
		if(empty($GLOBALS['bors_data']['hts_uri_bors_handlers']))
			return false;
	
		foreach($GLOBALS['bors_data']['hts_uri_bors_handlers'] as $regexp => $class_name)
			if(preg_match($regexp, $uri))
				return class_uri_load($class_name, $uri);
		
		return false;
	}

	function get_data($uri, $key, $default = NULL, $inherit = false, $skip = false, $fields = '`value`', $search = '`id`')
	{
		if($obj = $this->check_uri_handler($uri))
			if(method_exists($obj, $key))
				return $obj->$key();

		if($key == 'template')
		{
			if($fields == '`value`' && $search == '`id`' && !$skip && is_global_key("uri_data($uri)", $key))
			{
//				echo "*";
				return global_key("uri_data($uri)", $key);
			}
		}
			
//		if(!empty($_GET['debug']))
//		if($key == 'source')
//			echo("<small><tt>Get key '$key' for '$uri'</tt></small><br />");
		global $transmap;

//		print_r($transmap);
//		echo "$uri($key)<br/>";

		if(!preg_match('!^http://!', $uri))
			if(preg_match(DBHPROTOMASK, $uri, $m) && (!empty($transmap[@$m[1]]) || !empty($GLOBALS['bors'])) )
			{
//				echo "transmap for {$m[1]} = {$transmap[@$m[1]]}<br />";
				$ret = $this->get_proto($m[1], $m[2]."/", $key);
				if($ret !== NULL)
					return $ret;
			}

		$m = array ();
		if (preg_match("!^raw:(.+)$!", $key, $m))
		{
			$key = $m[1];
			$raw = true;
		}
		else
			$raw = false;


		$uri = $this->normalize_uri($uri);

		if (!$raw && isset ($GLOBALS['page_data_preset'][$key][$uri]))
			return $GLOBALS['page_data_preset'][$key][$uri];

		$skip_save = $skip;


		if (!$fields && !$search && !$skip && is_global_key("uri_data($uri)", $key)) // && global_key("uri_data($uri,$inherit,$skip_save)",$key)) 
			return global_key("uri_data($uri)", $key);


		if (!$raw && ($res = $this->pre_data_check($uri, $key)) !== false)
			return $res;

		if (!$raw && isset ($GLOBALS['page_data_preset'][$key][$uri]))
			return $GLOBALS['page_data_preset'][$key][$uri];

		$key_table_name = $this->create_data_table($key);

		$loops = 0;
	
		$host = '/';
		if(preg_match("!^(http://[^/]+)!", $uri, $m))
			$host = $m[1].'/';
					
		do
		{
			if (!$skip && $val = $this->dbh->get("SELECT $fields FROM `$key_table_name` WHERE $search='".addslashes($uri)."'", true))
				return set_global_key("uri_data($uri)", $key, $val); //stripslashes(

			if ($inherit)
			{
				$skip = false;

				if ($loops ++ > 10)
					$uri = $host;

				if ($uri == $host)
					break;

				$res = $this->get_data_array($uri, 'parent');

				if ($res)
				{
					sort($res);
					$uri = $res[0];
				}
				else
					$uri = $host;
			}
			else
				break;
		}
		while ($uri && $uri != $host && $loops < 10);

		
//		echo "post $uri($key)<br/>";
		if (!$raw && ($res = $this->post_data_check($uri, $key)) !== false)
			return set_global_key("uri_data($uri)", $key, $res);

		//			include_once("funcs/DataBaseHTS/ipb.php");
		$value = NULL; //dbhts_ipb($uri, $key, $this);

		set_global_key("uri_data($uri)", $key, $value);

//		if(!empty($_GET['debug']))
//			echo("Get key '$key' for '$uri' => '$value'");

		return $value ? $value : $default;
	}

	function get_array($uri, $key = array(), $params = array())
	{
		if(is_array($key))
			return $this->get_data_array($this->uri, $uri, $key);
		else
			return $this->get_data_array($uri, $key, $params);
	}

	function get_data_array($uri, $key, $params = array())
	{
		if(substr($uri, 0, 7) != 'http://')
		{
		
			if(!preg_match(DBHPROTOMASK, $uri, $m))
				return array();


			if(!function_exists('class_load'))
				return array();
				
			$obj = class_load($m[1], $m[2]."/");
			if(method_exists($obj, $key))
				return $obj->$key();
			else
				return array();
		}
	
		if(empty($params['fields']))
			 $params['fields'] = "`value`";

		if(empty($params['where']))
			$params['where'] = "`id`";
	
		$fields = $params['fields'];
		$search = $params['where'];

		$ignore_error = !empty($params['ignore_error']);

		$order = "";
		if(!empty($params['order']))
		{
			$order = array();
			foreach(split(',', $params['order']) as $ord)
			{
				$ord = trim($ord);
				if($ord{0} == "+")
					$ord = substr($ord, 1);
				if($ord{0} == "-")
					$ord = substr($ord, 1).' DESC';

				$order[] = $ord;
			}
			$order = " ORDER BY ".join(', ', $order);
		}
			
//		echolog("Get keys array '$key' for '$uri' (fields=$fields, search=$search)");

//		$uri = $this->normalize_uri($uri);

		if (($res = $this->pre_data_check($uri, $key)) !== false)
			return $res;

		$key_table_name = $this->create_data_table($key);

		$res = $this->dbh->get_array("SELECT $fields FROM `$key_table_name` WHERE $search='".addslashes($uri)."' $order", $ignore_error);

		if ($res)
			return $res;

		if (($res = $this->post_data_check($uri, $key)) !== false)
			return $res;

		return array ();
	}

	function get_data_array_size($uri, $key)
	{
//		echolog("Get keys array size '$key' for '$uri' (fields=$fields, search=id)");

		$uri = $this->normalize_uri($uri);

		if (($res = $this->pre_data_check($uri, $key)) !== false)
			return sizeof($res);

		$key_table_name = $this->create_data_table($key);

		$res = $this->dbh->get("SELECT COUNT(*) FROM `$key_table_name` WHERE id='".addslashes($uri)."'");

		if ($res)
			return $res;

		if (($res = $this->post_data_check($uri, $key)) !== false)
			return sizeof($res);

		return 0;
	}

	function data_exists($uri, $key, $value)
	{
		foreach ($this->get_data_array($uri, $key, array('ignore_error'=>true)) as $val)
		{
			if ($val == $value)
				return true;
		}

		return false;
	}

	function set($uri, $key, $value)
	{
		return $this->set_data($uri, $key, $value);
	}

	function set_data($uri, $key, $value, $params = array (), $append = false)
	{
		if($obj = $this->check_uri_handler($uri))
		{
			$method = "set_$key";
			if(method_exists($obj, $method))
				return $obj->$method($value);
		}
		
		global $transmap;

		if(!preg_match('!^http://!', $uri))
			if(preg_match('!^(\w+)://(.+[^/])$!', $uri, $m) && !empty($transmap[$m[1]]))
				return $this->set_proto($m[1], $m[2], $key, $value);
		
		if (!is_null($value) && is_global_key("uri_data($uri)", $key) && global_key("uri_data($uri)", $key) == $value)
			return;

		$uri = $this->normalize_uri($uri);

		$key_table_name = $this->create_data_table($key);

		if (is_null($value))
			$this->dbh->query("DELETE FROM $key_table_name WHERE `id`='".addslashes($uri)."'");
		else
			$this->dbh->store($key_table_name, "`id`='".addslashes($uri)."'", array ('id' => $uri, 'value' => $value) + $params, $append);

		set_global_key("uri_data($uri)", $key, $value);

		return $value;
	}

	function update_data($uri, $key, $fields, $search = "`id`")
	{
		echolog("Set for '$uri' as '$key' $fields => $search");

		$uri = $this->normalize_uri($uri);

		$key_table_name = $this->create_data_table($key);

		//			$GLOBALS['log_level'] = 9;
		$this->dbh->store($key_table_name, "$search='".addslashes($uri)."'", $fields);
		//			$GLOBALS['log_level'] = 2;

	}

	function append_data($uri, $key, $value, $params = array ())
	{
		echolog("Append for '$uri' as '$key'='$value'");

		$uri = $this->normalize_uri($uri);

		$key_table_name = $this->create_data_table($key);

		$this->dbh->store($key_table_name, "`id`='".addslashes($uri)."'", array ('id' => $uri, 'value' => $value) + $params, true);
		//			$this->dbh->query("DELETE FROM `$key_table_name` WHERE `id`=$page_id AND `value`='$value'");
		//			$this->dbh->query("INSERT INTO `$key_table_name` (`id`,`value`) VALUES ($page_id,'".mysql_real_escape_string($value,$this->dbh->dbh)."')");
		//			echo 'charset='.$this->dbh->get("SELECT @@character_set_client");
	}

	function remove_data($uri, $key, $value = NULL)
	{
		$uri = $this->normalize_uri($uri);

		$key_table_name = $this->create_data_table($key);

		if (is_null($value))
			$this->dbh->query("DELETE FROM `$key_table_name` WHERE `id`='".addslashes($uri)."'");
		else
			$this->dbh->query("DELETE FROM `$key_table_name` WHERE `id`='".addslashes($uri)."' AND `value`='".addslashes($value)."'");
	}

	function nav_link($iparent, $ichild)
	{
		//			echo "try linked $iparent, $ichild<br>";
		$parent = $this->normalize_uri($iparent);
		$child = $this->normalize_uri($ichild);

		if (!$parent || !$child)
		{
			debug(__FILE__.':'.__LINE__." Can't nav pair: $iparent-$ichild to $parent-$child", 1);
			return;
		}

		$GLOBALS['tmp_dbhts_nav_check_count'] = 0;

		if (!$this->parent_check($parent, $child))
		{
			debug(__FILE__.':'.__LINE__." Try to cycle parents-link: $child to $parent", 1);
			return;
		}

		$this->append_data($parent, 'child', $child);
		$this->append_data($child, 'parent', $parent);
	}

	function add_child($parent, $child)
	{
		$parent = $this->normalize_uri($parent);
		$child = $this->normalize_uri($child);

		if (!$parent || !$child)
		{
			debug(__FILE__.':'.__LINE__." Can't add child link: $parent-$child", 1);
			return;
		}

		$GLOBALS['tmp_dbhts_nav_check_count'] = 0;

		if (!$this->parent_check($parent, $child))
		{
			debug(__FILE__.':'.__LINE__." Try to cycle parents-link: $child to $parent", 1);
			return;
		}

		//			echo "Add $child as child for $parent";
		$this->append_data($parent, 'child', $child);
	}

	function parent_add($uri, $parent)
	{
		$uri = $this->normalize_uri($uri);
		$parent = $this->normalize_uri($parent);

		if (!$parent || !$uri)
		{
			debug(__FILE__.':'.__LINE__." Can't add parent link $uri-$parent", 1);
			return;
		}

		if (!$this->parent_check($parent, $uri))
		{
			debug(__FILE__.':'.__LINE__." Try to cycle parents-link: $parent to $uri", 1);
			return;
		}

		//			echo "Add $child as child for $parent";
		$this->append_data($uri, 'parent', $parent);
	}

	function parent_check($page, $parent_check)
	{
		if ($page == $parent_check)
			return false;

		if (empty ($GLOBALS['tmp_dbhts_nav_check_count']))
			$GLOBALS['tmp_dbhts_nav_check_count'] = 0;

		if ($GLOBALS['tmp_dbhts_nav_check_count']++ > 10)
		{
			debug(__FILE__.':'.__LINE__." Cycled parents-link: $page to $parent_check", 1);
			return false;
		}

		$no_circuit = true;
		foreach ($this->get_data_array($page, 'parent') as $p)
			$no_circuit = $no_circuit && $this->parent_check($p, $parent_check);

		return $no_circuit;
	}

	function remove_nav_link($iparent, $ichild = NULL)
	{
		$parent = $this->normalize_uri($iparent);
		if ($ichild)
		{
			$child = $this->normalize_uri($ichild);
			$t_c = $t_p = array ();
			if ($parent)
			{
				$t_c[] = "`id`	= '".addslashes($parent)."'";
				$t_p[] = "`value` = '".addslashes($parent)."'";
			}
			if ($child)
			{
				$t_c[] = "`value` = '".addslashes($child)."'";
				$t_p[] = "`id`	= '".addslashes($child)."'";
			}
			if ($parent || $child)
			{
				$t_c = join(' AND ', $t_c);
				$t_p = join(' AND ', $t_p);
				if ($t_c)
					$this->dbh->query("DELETE FROM `hts_data_child`  WHERE $t_c");
				if ($t_p)
					$this->dbh->query("DELETE FROM `hts_data_parent` WHERE $t_p");
			}
		}
		else
		{
			$this->dbh->query("DELETE FROM `hts_data_child`  WHERE `id` = '".addslashes($parent)."' OR `value` = '".addslashes($parent)."'");
			$this->dbh->query("DELETE FROM `hts_data_parent` WHERE `id` = '".addslashes($parent)."' OR `value` = '".addslashes($parent)."'");
		}
	}

	function child_remove($uri, $child)
	{
		if (!$uri || !$child)
		{
			debug(__FILE__.':'.__LINE__." Can't remove child link: $uri-$child", 1);
			return;
		}
		$uri = $this->normalize_uri($uri);
		$child = $this->normalize_uri($child);
		$this->dbh->query("DELETE FROM `hts_data_child`  WHERE `id`	= '".addslashes($uri)."' AND `value` = '".addslashes($child)."'");
	}

	function parent_remove($uri, $parent)
	{
		if (!$parent || !$uri)
		{
			debug(__FILE__.':'.__LINE__." Can't remove parent link: $uri-$parent", 1);
			return;
		}
		$uri = $this->normalize_uri($uri);
		$parent = $this->normalize_uri($parent);
		$this->dbh->query("DELETE FROM `hts_data_parent`  WHERE `id` = '".addslashes($uri)."' AND `value` = '".addslashes($parent)."'");
	}

	function page_uri_by_value($key, $value)
	{
		$key_table_name = $this->create_data_table($key);
		return $this->dbh->get("SELECT `id` FROM `$key_table_name` WHERE `value`='".addslashes($value)."'");
	}

	function uri_array_by_value($key, $value)
	{
		$key_table_name = $this->create_data_table($key);
		return $this->dbh->get_array("SELECT `id` FROM `$key_table_name` WHERE `value` = '".addslashes($value)."'");
	}

	function uri_array_by_condition($key, $condition)
	{
		$key_table_name = $this->create_data_table($key);
		return $this->dbh->get_array("SELECT `id` FROM `$key_table_name` WHERE $condition");
	}

	function create_data_table($key, $create_table = true)
	{
			//			echo "Create table name for $key";

	if (is_global_key('key_table_name', $key) && global_key('key_table_name', $key))
			return global_key('key_table_name', $key);

		$key_table_name = "hts_data_$key";

		set_global_key('key_table_name', $key, $key_table_name);

		if (1 || !$create_table)
			return $key_table_name;

		$res = $this->dbh->get("SELECT * FROM `hts_keys` WHERE `name`='".addslashes($key)."'");
		$type = $res['type'];

		if (!$type)
			return;

		$params_fields = '';
		$params_key = '';

		if ($res['params'])
		{
			foreach (split(",", $res['params']) as $p)
			{
				list ($f, $t) = split("=", $p);
				$params_fields .= "`$f` $t NOT NULL,\n";
				if ($t != 'TEXT' && substr($t, 0, 7) != 'VARCHAR')
					$params_key .= ", `$f`";
			}
		}

		$inc = $res['autoinc_value'] ? ' AUTO_INCREMENT ' : '';
		$index_id = !$res['array'] ? ' PRIMARY KEY `id` (`id`), ' : ' KEY `id` (`id`), ';

		$charset = (substr($type, 0, 3) == 'INT' ? '' : 'CHARACTER SET utf8');
		$index = '';
		$length = '';
		switch (substr($type, 0, 3))
		{
			case 'TEX' :
				$index = 'FULLTEXT KEY `value` (`value`)';
				$length = "(166)";
				break;
			case 'VAR' :
				$index = 'FULLTEXT KEY `value` (`value`)';
				break;
			case 'INT' :
				$index = 'KEY `value` (`value`)';
				break;
		}

		//			$GLOBALS['log_level']=9;
		$q = "
		CREATE TABLE IF NOT EXISTS `$key_table_name` (
			`id` VARCHAR(166) NOT NULL,
			`value` $type $charset NOT NULL $inc,
			$params_fields
			$index_id
			UNIQUE KEY `pair` ( `id` , `value` $length $params_key),
			$index
		);"; //  CHARACTER SET = utf8
		//			echo $q;
		$this->dbh->query($q);

		//			$GLOBALS['log_level']=2;

		/*
		CREATE TABLE `hts_keys` (
		`name` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL ,
		`type` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL ,
		`protected` TINYINT NOT NULL 
		) CHARACTER SET = utf8;
		*/
		return $key_table_name;
	}

	function delete_by_mask($uri)
	{
		foreach ($this->dbh->get_array("SELECT `name` FROM `hts_keys`") as $key)
		{
			$key_table_name = $this->create_data_table($key);
			$this->dbh->query("DELETE FROM `$key_table_name` WHERE `id` LIKE '".addslashes($uri)."'");
		}

		foreach ($this->dbh->get_array("SELECT `name` FROM `hts_keys` WHERE `id_in_value` = 1") as $key)
		{
			$key_table_name = $this->create_data_table($key);
			$this->dbh->query("DELETE FROM `$key_table_name` WHERE `value` LIKE '".addslashes($uri)."'");
		}

		$this->dbh->query("DELETE FROM `hts_aliases` WHERE `alias` LIKE '".addslashes($uri)."' OR `uri` LIKE '".addslashes($uri)."'");
	}

	function delete_page($uri)
	{
		$uri = $this->normalize_uri($uri);

		foreach ($this->dbh->get_array("SELECT `name` FROM `hts_keys`") as $key)
		{
			$key_table_name = $this->create_data_table($key);
			$this->dbh->query("DELETE FROM `$key_table_name` WHERE `id` = '".addslashes($uri)."'");
		}

		foreach ($this->dbh->get_array("SELECT `name` FROM `hts_keys` WHERE `id_in_value` = 1") as $key)
		{
			$key_table_name = $this->create_data_table($key);
			$this->dbh->query("DELETE FROM `$key_table_name` WHERE `value` = '".addslashes($uri)."'");
		}

		$this->dbh->query("DELETE FROM `hts_aliases` WHERE `alias` = '".addslashes($uri)."' OR `uri` = '".addslashes($uri)."'");
	}

	function rename_host($from, $to)
	{
		foreach ($this->dbh->get_array("SELECT `name` FROM `hts_keys`") as $key)
		{
			$key_table_name = $this->create_data_table($key);
			$this->dbh->query("UPDATE IGNORE `$key_table_name` SET `id` = REPLACE(`id`, '".addslashes($from)."', '".addslashes($to)."');");
		}

		foreach ($this->dbh->get_array("SELECT `name` FROM `hts_keys` WHERE `id_in_value` = 1") as $key)
		{
			$key_table_name = $this->create_data_table($key);
			$this->dbh->query("UPDATE `$key_table_name` SET `value` = REPLACE(`value`, '".addslashes($from)."', '".addslashes($to)."');");
		}

		$this->dbh->query("UPDATE `hts_aliases` SET `alias` = REPLACE(`alias`, '".addslashes($from)."', '".addslashes($to)."');");
	}

	function parse_uri($uri)
	{
		$uri = $this->normalize_uri($uri);
		$data = parse_url($uri);

		if (empty ($data['host']))
			$data['host'] = $_SERVER['HTTP_HOST'];

		if (preg_match("!^{$_SERVER['HTTP_HOST']}$!", $data['host']))
			$data['root'] = $_SERVER['DOCUMENT_ROOT'];
		else
			$data['root'] = $this->dbh->get("SELECT `doc_root`  as `root` FROM `hts_hosts` WHERE `host` = '".addslashes($data['host'])."'");

		$data['local'] = !empty ($data['root']);
		$data['local_path'] = $data['root'].str_replace('http://'.$data['host'], '', $uri);
		$data['uri'] = "http://".@ $data['host'].@ $data['path'];
		return $data;
	}

	function base_value($key, $def = NULL)
	{
		if (is_global_key('base_value', $key))
			return global_key('base_value', $key);

		$val = $this->dbh->get("SELECT `$key` FROM `hts_hosts` WHERE `host` LIKE '{$_SERVER['HTTP_HOST']}'", true);

		return set_global_key('base_value', $key, $val ? $val : $def);
	}

	/*	function move_page($old_name, $new_name)
		{
			$new_name = $this->normalize_uri($new_name);
			$old_name = $this->normalize_uri($old_name);
	
			if (!$new_name || !$old_name)
				return false;
	
			$this->dbh->insert('hts_aliases', array ('alias' => $old_name, 'uri' => $new_name));
			return true;
		}
	*/
	function viewses_inc($uri)
	{
		if (!$this->get_data($uri, 'views_first'))
			$this->set_data($uri, 'views_first', time());

		$this->set_data($uri, 'views_last', time());

		$this->set_data($uri, 'views', $this->get_data($uri, 'views') + 1);
	}

	function sys_var($key, $default = NULL)
	{
		$ret = $this->dbh->get("SELECT `value` FROM `hts_ext_system_data` WHERE `key`='".addslashes($key)."'");

		if (!$ret)
			$ret = $default;

		return $ret;
	}

	function set_flag($uri, $flag)
	{
		global $transmap;

		if(!preg_match('!^http://!', $uri))
			if(preg_match(DBHPROTOMASK, $uri, $m) && !empty($transmap[@$m[1]]))
				return $this->set_proto($m[1], $m[2]."/", $flag, 1);

		$this->append_data($uri, 'flags', $flag);
	}

	function drop_flag($uri, $flag)
	{
		global $transmap;

		if(!preg_match('!^http://!', $uri))
			if(preg_match(DBHPROTOMASK, $uri, $m) && !empty($transmap[@$m[1]]))
				return $this->set_proto($m[1], $m[2]."/", $flag, NULL);

		$this->remove_data($uri, 'flags', $flag);
	}

	function is_flag($uri, $flag)
	{
		global $transmap;

		if(!preg_match('!^http://!', $uri))
			if(preg_match(DBHPROTOMASK, $uri, $m) && !empty($transmap[@$m[1]]))
				return $this->get_proto($m[1], $m[2]."/", $flag) ? true : false;

		return $this->data_exists($uri, 'flags', $flag);
	}

	function pages_with_flag($uri_like, $flag)
	{
		return $this->dbh->get_array("SELECT id FROM hts_data_flags WHERE value LIKE '".addslashes($flag)."' AND id RLIKE '".addslashes($uri_like)."'");
	}

	function export($uri)
	{
		$save = '';
		foreach (split(' ', 'title source') as $i)
			$save .= "$i = ".$this->get_data($uri, $i).";\n\n";
		return $save;
	}

	function getNextId($uri)
	{
		$uri = addslashes($this->normalize_uri($uri));
		$this->dbh->query("INSERT INTO `hts_ids` SET `uri` = '$uri'");
		$id = $this->dbh->get_last_id();
		if (!$id)
			exit ("Unknown error. Can't get next ID for " + $uri);
		return $id;
	}

	function pages_with_parent($uri)
	{
		return $this->dbh->get_array("SELECT id FROM hts_data_parent WHERE value LIKE '".addslashes($uri)."'");
	}

	function get_children_array_ex($parent, $params = array ())
	{
		$start = intval(empty ($params['start']) ? 0 : $params['start']);
		$limit = intval(empty ($params['limit']) ? 20 : $params['limit']);
		$range = intval(empty ($params['range']) ? 86400 : $params['range']);

		$stop_time = time();
		$start_time = $range > 0 ? $stop_time - $range : 0;

		$join = $cond = "";
		$order = "ORDER BY mt.value DESC";

		$tab = 1;

		if(!empty($params['order']))
		{
			list($table, $asc) = preg_split("!\s+!", $params['order']);
			$join .= " LEFT JOIN hts_data_".addslashes($table)." tab$tab ON (c.value = tab$tab.id)\n";
			$order = "ORDER BY tab$tab.value $asc\n";
		}

		if(!empty ($params['closed']))
		{
			$not = $params['closed'] == 'yes' ? "NOT" : "";
		
			$join .= " LEFT JOIN hts_data_flags fc ON (c.value = fc.id AND fc.value='closed')";
			$cond .= " AND fc.id IS $not NULL";
		}
		
		if(empty ($params['hidden']) || $params['hidden'] == 'only')
		{
			$not = @$params['hidden'] == 'only' ? "NOT" : "";
		
			$join .= " LEFT JOIN hts_data_flags fh ON (c.value = fh.id AND fh.value='hidden')";
			$cond .= " AND fh.id IS $not NULL";
		}

		if (empty ($params['deleted']) || $params['deleted'] == 'only')
		{
			$not = @$params['deleted'] == 'only' ? "NOT" : "";
			
			$join .= " LEFT JOIN hts_data_flags fd ON (c.value = fd.id AND fd.value='deleted')";
			$cond .= " AND fd.id IS $not NULL";
		}

		$query = "SELECT c.value as uri
	FROM hts_data_child c
		LEFT JOIN hts_data_modify_time mt ON (c.value = mt.id)
		$join
	WHERE c.id = '".addslashes($parent)."'
		AND mt.value >= $start_time
		AND mt.value <	$stop_time
		$cond
	$order
	LIMIT $start, $limit;";

		return $this->dbh->get_array($query);
	}

	function get_children_array_ex_size($parent, $params = array ())
	{
		$range = intval(empty ($params['range']) ? 86400 : $params['range']);

		$stop_time = time();
		$start_time = $range > 0 ? $stop_time - $range : 0;

		$join = $cond = "";

		$tab = 1;

		if(!empty ($params['closed']))
		{
			$not = $params['closed'] == 'yes' ? "NOT" : "";
		
			$join .= " LEFT JOIN hts_data_flags fc ON (c.value = fc.id AND fc.value='closed')";
			$cond .= " AND fc.id IS $not NULL";
		}
		
		if(empty ($params['hidden']) || $params['hidden'] == 'only')
		{
			$not = @$params['hidden'] == 'only' ? "NOT" : "";
		
			$join .= " LEFT JOIN hts_data_flags fh ON (c.value = fh.id AND fh.value='hidden')";
			$cond .= " AND fh.id IS $not NULL";
		}

		if (empty ($params['deleted']) || $params['deleted'] == 'only')
		{
			$not = @$params['deleted'] == 'only' ? "NOT" : "";
			
			$join .= " LEFT JOIN hts_data_flags fd ON (c.value = fd.id AND fd.value='deleted')";
			$cond .= " AND fd.id IS $not NULL";
		}

		$query = "
		SELECT COUNT(*) FROM hts_data_child c
			LEFT JOIN hts_data_modify_time mt ON (c.value = mt.id)
			$join
		WHERE c.id = '".addslashes($parent)."'
			AND mt.value >= $start_time
			AND mt.value <	$stop_time
			$cond
		";

		return $this->dbh->get($query);
	}

	function get_array_ex($regexp, $table, $params = array ())
	{
		//print_r($params);
		$limit = intval(empty ($params['limit']) ? 20 : $params['limit']);
		$range = intval(empty ($params['range']) ? 86400 : $params['range']);

		$stop_time = intval(empty ($params['stop_time']) ? time() : $params['stop_time']);
		$start_time = intval(empty ($params['start_time']) ? $stop_time - $range : $params['start_time']);

		$join = $cond = "";

		//echo $params['like_type'];
		$like_type = empty ($params['like_type']) ? '=' : addslashes($params['like_type']);

		if(empty ($params['hidden']))
		{
			$join .= " LEFT JOIN hts_data_flags fh ON (ct.value = fh.id AND fh.value='hidden')";
			$cond .= " AND fh.id IS NULL";
		}

		if(empty ($params['deleted']))
		{
			$join .= " LEFT JOIN hts_data_flags fd ON (ct.value = fd.id AND fd.value='deleted')";
			$cond .= " AND fd.id IS NULL";
		}

		$this->add_where($params, $join, $cond);

		$query = "SELECT ct.id as uri
	FROM hts_data_".addslashes($table)." ct
		LEFT JOIN hts_data_modify_time mt ON (ct.id = mt.id)
		$join
	WHERE ct.id $like_type '".addslashes($regexp)."'
		$cond
	ORDER BY mt.value DESC
	LIMIT $limit;";

		$ret = $this->dbh->get_array($query);

		return $ret;
	}

	function add_where($params, &$join, &$cond)
	{
		if(!is_array(@$params['where']))
			return;
		
		$joined = array ();
		$join_cnt = 0;

		$m2 = array ();
		foreach ($params['where'] as $field => $value)
		{
			if(preg_match("!^(.+)\s+(.+?)$!", $field, $m2))
			{
				$field = $m2[1];
				$op = addslashes($m2[2]);
			}
			else
				$op = "=";

			$field = addslashes($field);

			if(empty ($joined[$field]))
			{
				$join_cnt ++;
				$jt = $joined[$field] = "j$join_cnt";
				$join .= " LEFT JOIN hts_data_$field $jt ON (ct.id = $jt.id) ";
			}
			else
				$jt = "j".$joined[$field];

			$cond .= " AND $jt.value $op ".addslashes($value)." ";
		}
	}

	function get_proto($proto, $id, $key)
	{
//		echo "Get proto $proto://$id -> $key()<br />";
		if(function_exists('class_load') && ($obj = class_load($proto, $id)) && method_exists($obj, $key))
			return $obj->$key();
	
		global $transmap;
		$t = &$transmap[$proto];

		if(preg_match('!^http://!', $id) && $t['uri>id'])
			$id = $t['uri>id']['rf']($id);
		
		if(is_array($key))
		{
			$fields = array();
			$join	= array();
			foreach($key as $k)
			{
				$fields[] = $t[$k]['r'];
				if(empty($joined[$t[$k]['join']]))
				{
					$join[] = $t[$k]['join'];
					$joined[$t[$k]['join']] = true;
				}
			}
			
			$fields = join(", ", $fields);
			$join   = join(" ", $join);
		}
		else
		{
			if(@$t[$key]['rf'])
				return $t[$key]['rf']($id);
		
			$fields = @$t[$key]['r'];
			$join = @$t[$key]['join'];
		}
		
		if(!empty($t['db']))
			$db = "`{$t['db']}`.";
		else
			$db = "";

		$table = @$t[is_array($key)?$key[0]:$key]['table'];
		
		if(empty($fields) || empty($table))
			return NULL;

		$r = $this->dbh->get("SELECT $fields FROM $db$table $join WHERE {$t['*']} = '".addslashes($id)."'");

		if(is_array($key))
		{
			foreach($key as $k)
				if($t[$k]['q'])
					$r[$k] = html_entity_decode($r[$k], ENT_COMPAT, 'UTF-8');
		}
		else
			if($t[$key]['q'])
				$r = html_entity_decode($r, ENT_COMPAT, 'UTF-8');

		return $r;
	}

	function get_proto_array($proto, $id, $key, $para = array())
	{
		global $transmap;
		$t = &$transmap[$proto];

		if(preg_match('!^http://!', $id))
			$id = $t['uri>id']['rf']($id);

		if(is_array($key))
		{
			$fields = array();
			$join	= array();
			foreach($key as $k)
			{
				$fields[] = $t[$k]['r'];
				if(empty($joined[$t[$k]['join']]))
				{
					$join[] = $t[$k]['join'];
					$joined[$t[$k]['join']] = true;
				}
			}
			$fields = join(", ", $fields);
			$join   = join(" ", $join);
		}
		else
			$fields = $t[$key]['r'];
		
		if(empty($para['like_type']))
			$para['like_type'] = 'like';
	
		if($para['like_type'] == 'like')
			$where = "{$t['*']} LIKE '".addslashes($id)."'";

		$limit = "";
		if(isset($para['start']) && isset($para['limit']))
			$limit = "LIMIT ".intval($para['start']).", ".intval($para['limit']);
			
		$order = "";
		if(isset($para['order']))
			$order = "ORDER BY {$t[$para['order']]['k']}";

		if(!empty($t['db']))
			$db = "`{$t['db']}`.";
		else
			$db = "";

		$table = @$t[is_array($key)?$key[0]:$key]['table'];

		$r = $this->dbh->get_array("SELECT $fields  FROM $db$table $join WHERE $where $order $limit");

		if(is_array($key))
		{
			foreach($key as $k)
				if($t[$k]['q'])
					for($i=0, $stop=sizeof($r); $i<$stop; $i++)
						$r[$i][$k] = html_entity_decode($r[$i][$k], ENT_COMPAT, 'UTF-8');
		}
		else
			if($t[$key]['q'])
				for($i=0, $stop=sizeof($r); $i<$stop; $i++)
					$r[$i] = html_entity_decode($r[$i], ENT_COMPAT, 'UTF-8');

		return $r;
	}

	function get_proto_array_size($proto, $id, $para = array())
	{
		global $transmap;
		$t = &$transmap[$proto];

		if(preg_match('!^http://!', $id))
			$id = $t['uri>id']['rf']($id);
		
		if(empty($para['like_type']))
			$para['like_type'] = 'like';
	
		if($para['like_type'] == 'like')
			$where = "{$t['*']} LIKE '".addslashes($id)."'";

		if(!empty($t['db']))
			$db = "`{$t['db']}`.";
		else
			$db = "";

		return intval($this->dbh->get("SELECT COUNT(*)  FROM $db{$t['table']} WHERE $where"));
	}

	function set_proto($proto, $id, $key, $value)
	{
		global $transmap;
		$t = &$transmap[$proto];
		
		if(preg_match('!^http://!', $id))
			$id = $t['uri>id']['rf']($id);
		
		$w = str_replace('$1', $value, @$t[$key]['w']);
		
		if(@$t[$key]['q'])
			$value = htmlspecialchars($value);
		
		if(!empty($t['db']))
			$db = "`{$t['db']}`.";
		else
			$db = "";

//		exit("$proto://$id/, $key=$value: UPDATE $db{$t[$key]['table']} SET  $w WHERE {$t['*']} = '".addslashes($id)."'");

//		if($value != 'NULL')
			$this->dbh->query("UPDATE $db{$t[$key]['table']} SET  $w WHERE {$t['*']} = '".addslashes($id)."'");
//		else
//			$this->dbh->query("DELETE FROM $db{$t[$key]['table']} WHERE {$t['*']} = '".addslashes($id)."'");
	}
}

	function register_data_translate($proto, $table, $trans)
	{
		global $transmap;
		$t = &$transmap[$proto];

		if(preg_match("!^(\w+)\.(\w+)$!", $table, $m))
		{
			$t['db'] = $m[1];
			$table = $m[2];
		}

		if($table)
			$t['table'] = $table;
	
		if(empty($t['*']))
			$t['*'] = 'id';
		
		foreach($trans as $key => $value)
		{
			if(is_int($key))
				$key = $value;
		
			$t[$key]['table'] = $table;
		
			if(preg_match('!^Q:(.+)$!i', $value, $m))
			{
				$t[$key]['q'] = true;
				$value = $m[1];
			}
			else
				$t[$key]['q'] = false;

			if($key == 'id')
				$t['*'] = $key;

			$t[$key]['join'] = NULL;
			$t[$key]['rf'] = NULL;
			
			if(!preg_match('!^(.+)\|(.+)$!', $value, $m))
			{
				if(preg_match("!^(\S+)\.(\S+?)\s+(\S+)=(\S+)$!", $value, $m))
				{
					$value = "{$m[1]}.`{$m[2]}`";
					$t[$key]['k'] = $value;
					$t[$key]['r'] = "$value AS $key";
					$t[$key]['w'] = "$value = '$1'";
					$t[$key]['join'] = "LEFT JOIN {$m[1]} ON `$table`.`{$m[3]}` = {$m[1]}.`{$m[4]}`";
				}
				else
				{
					if(function_exists($value))
					{
						$t[$key]['rf'] = $value;
					}
					else
					{
						$t[$key]['k'] = $value;
						if(preg_match("!^(\w+)\.(\w+)$!", $value, $m2))
						{
							$value = $m2[2];
							$t[$key]['table'] = $m2[1];
						}
//						$value = split("\.", $value);
//						$value = "`".join("`.`", $value)."`";
						$t[$key]['r'] = "`$value` AS $key";
						$t[$key]['w'] = "`$value` = '$1'";
					}
				}
			}
			else
			{
				$r = trim($m[1]);
				$w = trim($m[2]);
				$t[$key]['k'] = $r;
				$t[$key]['r'] = "$r AS $key";
				$t[$key]['w'] = $w;
			}
		}

	}

	function register_hts_uri_bors_handler($regexp, $class)
	{
		$GLOBALS['bors_data']['hts_uri_bors_handlers'][$regexp] = $class;
	}
