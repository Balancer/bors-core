<?php

/**
	Типовой класс для раздельного view объектов
*/

class bors_view extends bors_page
{
	function can_be_empty() { return false; }
	function loaded() { return (bool) $this->target(); }

	function _class_title_rp_def() { return $this->object()->class_title_rp(); }

	// Класс отображаемого объекта
	function main_class()
	{
		bors_function_include('natural/bors_unplural');

		if($this->class_name() == 'bors_view')
			return $this->arg('class_name');

		// ucrm_companies_groups_view -> ucrm_companies_groups
		$main_class = preg_replace('/_view$/', '', $this->extends_class_name());
		// ucrm_companies_groups -> ucrm_company_group
		$main_class = join('_', array_map('bors_unplural', explode('_', $main_class)));

		if(class_include($main_class))
			return $main_class;

		$main_class_up = bors_unplural($main_class);
		if(class_include($main_class_up))
			return $main_class_up;

		$main_class = preg_replace('/^(\w+)_admin_(\w+)$/', '$1_$2', $main_class);

		if(class_include($main_class))
			return $main_class;

		$main_class_up = bors_unplural($main_class);
		if(class_include($main_class_up))
			return $main_class_up;

		bors_throw(ec('Не определён главный класс для представления ').$this->class_name());
	}

	function item_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->main_class());
	}

	function referent_class() { return $this->main_class(); }

	function object() { return $this->target(); } // Для совместимости

	function title() { return $this->object()->title(); }
	function nav_name() { return $this->object()->nav_name(); }
	function description() { return $this->object()->description(); }
	function create_time($exact = false) { return $this->object()->create_time($exact); }
	function modify_time($exact = false) { return $this->object()->modify_time($exact); }

	function _image_def() { return $this->object()->get('image'); }


	function target_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->main_class());
	}

	function auto_targets()
	{
		$data = array(
			'target' => 'main_class(id)',
			$this->target_name() => 'main_class(id)',
		);

		return array_merge(parent::auto_targets(), $data);
	}

	function body_data()
	{
		$target = $this->object();
		$data = array(
			$this->item_name() => $target,
			'target' => $target,
			'view' => $this,
			'self' => $this,
		);

		return array_merge(parent::body_data(), $data, $this->target()->data);
	}

	function url($page = NULL) { return $this->target()->url($page); }
	function admin_url() { return $this->target()->get('admin_url'); }
	function object_type() { return $this->target()->object_type(); }

	function _owner_id_def() { return object_property($this->object(), 'owner_id'); }

	function _project_name_def() { return bors_core_object_defaults::project_name($this); }
	function _section_name_def() { return object_property($this->object(), 'section_name'); }
	function _config_class_def() { return bors_core_object_defaults::config_class($this); }
	function _is_deleted_def() { return $this->object()->get('is_deleted'); }

	function html_auto()
	{
		$target = $this->target(); // Выводимый объект
		$out = array();
		foreach(bors_lib_orm::main_fields($target) as $idx => $args)
		{
			if(@$args['is_editable'] === false)
				continue;

			$value = $target->get($args['property']);
//			var_dump($args, $value);
			if($args['type'] == 'image') // http://ucrm.wrk.ru/persons/4/
			{
//				var_dump($value);
				if($image = bors_load('bors_image', $value))
					array_unshift($out, $image->thumbnail('200x')->html(array('append' => 'class="float-right" hspace="10"')));

				continue;
			}
			elseif($args['type'] == 'bool') // http://matf.aviaport.ru/projects/1/
				$value = $value ? ec('да') : ec('нет');
			elseif(!empty($args['class']))
				$value = object_property(bors_load($args['class'], $value), 'titled_link');
			elseif(!empty($args['named_list']))
				$value = object_property(bors_load($args['named_list'], $value), 'title');
			elseif($args['type'] == 'freedate')
				$value = bors_lower(bors_lib_date::part($value, true));
			elseif($args['type'] == 'bbcode')
				$value = lcml_bbh($value);
			else
			{
//				var_dump($args, $value);
				$value = htmlspecialchars($value);
			}

			if($value && ($title = $args['title']))
				$out[] = "<li><b>{$title}</b>: ".$value."</li>\n";
		}

		$html .= join("", $out);

		return $html;
	}

	function access()
	{
		$access = $this->target()->access();
		$access->set_attr('view', $this);
		return $access;
	}
}
