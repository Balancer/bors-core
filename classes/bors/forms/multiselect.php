<?php

class bors_forms_multiselect extends bors_forms_element
{
	function html()
	{
		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		$object = $form->object();

		$params = "";
		foreach(explode(' ', 'size style') as $p)
			if(!empty($$p))
				$params .= " $p=\"{$$p}\"";

		$html = "<select multiple=\"multiple\" name=\"".addslashes($name)."[]\"$params>\n";

		if(!is_array($list))
		{
			if(preg_match("!^(\w+)\->(\w+)$!", $list, $m))
			{
				if($m[1] == 'this')
					$list = $object->$m[2]();
				else
					$list = object_load($m[1])->$m[2]();
			}
			elseif(preg_match("!^(\w+)\->(\w+)\('(.+)'\)!", $list, $m))
			{
				if($m[1] == 'this')
					$list = $object->$m[2]($m[3]);
				else
					$list = object_load($m[1])->$m[2]($m[3]);
			}
			elseif(preg_match("!^\w+$!", $list))
			{
				$list = new $list(@$args);
				$list = $list->named_list();
			}
			else
			{
				eval('$list='.$list);
			}
		}

		$have_null = in_array(NULL, $list);
		$strict = defval($params, 'strict', $have_null);
		$is_int = defval($params, 'is_int');

		if(is_null($is_int) && !$strict)
			$is_int = true;

		$current = $object ? $object->$name() : array();

		if($is_int)
			for($i=0; $i<count($current); $i++)
				$current[$i] = ($have_null && is_null($current[$i])) ?  NULL : intval($current[$i]);

		foreach($list as $id => $iname)
		{
			if(!$id && !empty($params['have_null']))
				$id = NULL;

			if($id !== 'default')
				$html .= "\t\t\t<option value=\"$id\"".(in_array($id, $current, $strict) ? " selected=\"selected\"" : "").">$iname</option>\n";
		}

		$html .= "</select>";

		$vcbs = base_object::template_data('form_checkboxes_list');
		$vcbs[] = $name;
		base_object::add_template_data('form_checkboxes_list', $vcbs);

		return $html;
	}
}
