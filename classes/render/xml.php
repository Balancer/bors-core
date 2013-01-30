<?php

require_once('inc/xml/array2xml.php');

class render_xml extends base_null
{
	function render($object)
	{
		if(!$object->is_loaded() && !$object->can_be_empty())
			return false;

		$object_data = $object->local_template_data_set();
		$object_fields = $object_data['xml_fields'];
		unset($object_data['xml_fields']);
		$xml_data = array();
		foreach($object_data as $key => $value)
		{
			$fields = $object_fields[$key];
			if(is_array($value))
			{
				$res = array();
				foreach($value as $x)
					$res[] = $this->object_fields($x, $fields);

				$xml_data[$key] = $res;
			}
			else
				$xml_data[$key] = $this->object_fields($value, $fields);
		}

		print_d($xml_data);

		$tpl_source = preg_replace('!\.php$!', '.xsl', $object->class_file());

        $template = new DOMDocument();
		$template->load($tpl_source);
		$xp = new XSLTProcessor();
		$xp->importStyleSheet($template);

		$dom = new DOMDocument();
		$x = array2xml($xml_data);
		print_d($x);
		$dom->loadXML($x);

		$result = $xp->transformToXML($dom);

		print_d($result);

		bors_exit();
		return $result;
	}

	function object_fields($object, $fields)
	{
		$result = array();
		foreach(explode(' ', $fields) as $field)
			$result[$field] = $object->$field();

		return $result;
	}
}
