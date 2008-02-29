<?php

function form_save()
{
//	print_r($_GET);
	$form = class_load($_GET['class_name'], @$_GET['id']);
//	echo $_GET['class_name']; exit();

	require_once('classes/inc/access.php');
	if(method_exists($form, 'acl_edit_sections') && !bors_check_access($form, $form->acl_edit_sections()))
		return true;

	if(method_exists($form, 'preAction'))
	{
		$processed = $form->preAction($_GET);
		if($processed === true)
			return true;
	}

	if(!$form->id())
		$form->new_instance();

//	$processed2 = $form->preParseProcess();
//	if($processed2 === true)
//		return true;
								   			
	if(empty($_GET['subaction']))
		$method = 'onAction';
	else
		$method = 'onAction_'.$_GET['subaction'];

	global $bors;
//	print_r($form); exit();
				
	if(method_exists($form, $method))
	{
		$result = $form->$method($_GET);
		if($result === true)
			return true;
	}
	else
	{
		$form->set_fields($_GET, true);
				
		$bors->changed_save();

		foreach($_GET as $key => $val)
		{
			if(!$val || !preg_match("!^file_(\w+)_delete_do$!", $key, $m))
				continue;
						
			$method = "remove_{$m[1]}_file";
			if(method_exists($form, $method))
				$form->$method(true);
		}
				
		if(!empty($_FILES))
		{
			foreach($_FILES as $file => $params)
			{
				$method = "upload_{$file}_file";
				if(method_exists($form, $method))
					$form->$method($params, true);
			}
		}
	}

	$bors->changed_save();

//	exit("Saved");

	if(!empty($_GET['go']))
	{
		if($_GET['go'] == "newpage")
			return go($form->url(1));
					
		$_GET['go'] = str_replace('%OBJECT_ID%', $form->id(), $_GET['go']);
		require_once('funcs/navigation/go.php');
		return go($_GET['go']);
	}
}

