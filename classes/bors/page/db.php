<?php

class bors_page_db extends base_object_db
{
	function _renderer_class_def() { return 'bors_renderers_page'; }
	function _body_def() { return bors_lib_page::body($this); }
	function _body_class_def() { return 'bors_bodies_page'; }

	function _browser_title_def() { return $this->page_title(); }
	function _page_title_def() { return $this->title(); }

	function _is_smart_def() { return true; }
	function _body_template_ext_def() { return 'html'; }

	function bors_di_classes()
	{
		return array_merge(parent::bors_di_classes(), array(
			'bors_di_page',
		));
	}

	function body_template_class()
	{
		return config('body_template_class', $this->page_template_class());
	}

	function _body_template_def()
	{
		if($this->is_smart())
		{
			bors_lib_page::smart_body_template_check($this);
			if(!empty($this->attr['body_template']))
				return $this->attr['body_template'];
		}

		$tpl = bors_lib_page::body_template($this);

		if(!$tpl)
			$tpl = 'xfile:'.BORS_CORE.'/classes/bors/base/page.html';

		return $tpl;
	}

	function page_template()
	{
		return $this->template(); // Пока, для совместимости, используем старый API.
	}


	function _page_template_class_def()
	{
		if($class_name = config('templates_page_engine'))
		{
			if(strpos($class_name, '_'))
				return $class_name;
			return 'bors_templates_'.$class_name;
		}

		return config('page_template_class', 'bors_templates_smarty');
	}


	function pre_show()
	{
		if($this->is_smart())
		{
			$class_file_base = str_replace('.php', '', $this->class_file());
			if(file_exists($f="$class_file_base.inc.css"))
				$this->add_template_data_array('style', ec(file_get_contents($f)));
			if(file_exists($f="$class_file_base.inc.js"))
				$this->add_template_data_array('javascript', ec(file_get_contents($f)));
			if(file_exists($f="$class_file_base.inc.post.js"))
				$this->add_template_data_array('javascript_post', ec(file_get_contents($f)));
		}

		return parent::pre_show();
	}

	function body_data()
	{
		if($bdc = $this->get('body_data_engine'))
		{
			$bde = bors_load($bdc, $this);
			return $bde->body_data($data);
		}

		return array();
	}

	function page_data() { return array(); }

	// под снос. Но пока используется широко а ля bors-core/classes/bors/renderers/page.php:77
	function me() { return bors()->user(); }
	function me_id() { return bors()->user_id(); }

	function _parser_type_def() { return 'lcml'; }
	function _html_def()
	{
		switch($this->parser_type())
		{
			case 'lcml_bbh':
				return lcml_bbh($this->source());

			default:
				return lcml($this->source());
		}
	}

	function compiled_source() { return bors_lcml::lcml($this->source(), array('container' => $this)); }

	function _layout_class_def() { return 'bors_layouts_bors'; }

	function _layout_def()
	{
		$class_name = $this->layout_class();
		$layout = new $class_name($this);
		$this->set_attr('layout', $layout);
		return $layout;
	}
}
