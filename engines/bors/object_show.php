<?php

	function bors_object_show($obj)
	{
//		echo "Bors class=".get_class($obj); exit();

		if(!$obj)
			return false;

	    header("Status: 200 OK");

		$processed = $obj->preParseProcess($_GET);
		if($processed === true)
			return true;

		if(!empty($_GET['act']))
		{
			if(!$obj->access()->can_action($_GET['act']))
				return bors_message(ec("Извините, Вы не можете производить операции с этим ресурсом (class=".get_class($obj).", access=".get_class($obj->access()).")"));

			if(method_exists($obj, $method = "on_action_{$_GET['act']}"))
			{
				$result = $obj->$method($_GET);
				if($result === true)
					return true;
			}
		}

		if(!empty($_GET['class_name']))
		{
//			print_d($_GET); exit();
			$form = object_load($_GET['class_name'], @$_GET['id']);
//			echo $_GET['class_name']; exit();
//			print_d($form);

			$processed = $form->pre_action($_GET);
			if($processed === true)
				return true;

			if(method_exists($form, 'preAction'))
			{
				$processed = $form->preAction($_GET);
				if($processed === true)
					return true;
			}

			if(!$form->access()->can_action())
				return bors_message(ec("Извините, Вы не можете производить операции с этим ресурсом (class=".get_class($form).", access=".get_class($form->access()).")"));

			if(empty($_GET['subaction']))
				$method = 'onAction';
			else
				$method = 'onAction_'.$_GET['subaction'];

			if(method_exists($form, $method))
			{
				$result = $form->$method($_GET);
				if($result === true)
					return true;
			}
			else
			{
				$data = array_merge($_FILES, $_GET);
			
				if($form->check_data($data) === true)
					return true;

				if(!$form->id())
					$form->new_instance();

				if(!$form->id())
					debug_exit('Empty id for '.$form->class_name());

				foreach($data as $key => $val)
				{
					if(!$val || !preg_match("!^file_(\w+)_delete_do$!", $key, $m))
						continue;
						
					$method = "remove_{$m[1]}_file";
//					if(method_exists($form, $method))
						$form->$method($data);
				}

				if(!empty($_FILES))
				{
					foreach($_FILES as $file => $params)
					{
						if($params)
						{
							$method = "upload_{$file}_file";
//							if(method_exists($form, $method))
								$form->$method($params, $data);
						}
					}
				}

				if(!$form->set_fields($data, true))
					return true;

				$form->set_modify_time(time(), true);
				
				$form->post_set();
			}

			bors()->changed_save();

			if(!empty($_GET['go']))
			{
				if($_GET['go'] == "newpage")
					return go($form->url(1));
					
				$_GET['go'] = str_replace('%OBJECT_ID%', $form->id(), $_GET['go']);
				require_once('funcs/navigation/go.php');
				return go($_GET['go']);
			}
		}
		
		
		$processed = $obj->preShowProcess();
		if($processed === true)
			return true;

		$page = $obj->page();
//		exit($obj->url($page) .'!='. $obj->called_url());
		if($obj->called_url() && !preg_match('!\Q'.$obj->url($page).'\E$!', $obj->called_url()))
			return go($obj->url($page), true);

		if($processed === false)
		{
			bors()->set_main_object($obj);

			if(empty($GLOBALS['main_uri']))
				$GLOBALS['main_uri'] = $obj->url();
			
			$my_user = bors()->user();
			if($my_user && $my_user->id())
				base_page::add_template_data('my_user', $my_user);
	
			if($render_engine = $obj->render_engine())
			{
				$re = object_load($render_engine);
				if(!$re)
					debug_exit("Can't load render engine {$render_engine} for class {$obj}");
				$content = $re->render($obj);
//				echo "Bors class content of ".get_class($obj)." with render engine ". $obj->render_engine() . " = " . $content; exit();
			}
			else
			{
			    require_once('funcs/templates/bors.php');
				$obj->template_data_fill();
				$content = template_assign_bors_object($obj);
			}

		}
		else
			$content = $processed;


		if($content === false)
			return false;

		$access_object = $obj->access();
		if(!$access_object)
			debug_exit("Can't load access_engine ({$obj->access_engine()}?) for class {$obj}");
			
		if(!$access_object->can_read())
			return empty($GLOBALS['cms']['error_show']) ? bors_message(ec("Извините, у Вас не доступа к этому ресурсу")) : true;
		
		$last_modify = gmdate('D, d M Y H:i:s', $obj->modify_time()).' GMT';
		header('Last-Modified: '.$last_modify);
	   
		if((!empty($GLOBALS['cms']['cache_static']) || $obj->cache_static()) && (empty($_SERVER['QUERY_STRING']) || $_SERVER['QUERY_STRING']=='del' || @$_GET['act'] == 'del'))
		{
//			echo "url={$obj->url_engine()}<br />";
			$sf = &new CacheStaticFile($obj->url($page));
			$sf->save($content, $obj->modify_time(), $obj->cache_static());

			foreach(split(' ', $obj->cache_groups()) as $group)
				if($group)
				{
					$group = class_load('cache_group', $group);
					$group->register($obj);
				}
				
		    header("X-Bors: static cache maden");

			
//			require_once('funcs/navigation/go.php');
//			return go($obj->url($page), true, 0, false);
		}

	
		echo $content;
		return true;
	}
