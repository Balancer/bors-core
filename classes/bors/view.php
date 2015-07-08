<?php

/**
	Типовой класс для раздельного view объектов
*/

class bors_view extends bors_page
{
	function can_be_empty() { return false; }
	function is_loaded() { return (bool) $this->model(); }

	function _class_title_rp_def() { return $this->model()->class_title_rp(); }

	function data_load()
	{
		$loaded = parent::data_load();
		if(is_object($x = $this->id()))
			$this->set_model($x);
		else
			if(!$this->model())
				return false;

		$this->set_attr($this->target_name(), $this->model());
		return $loaded;
	}

	// Класс отображаемого объекта
	function main_class()
	{
		if($this->class_name() == 'bors_view')
			return $this->arg('class_name');

		// ucrm_companies_groups_view -> ucrm_companies_groups
		$main_class = preg_replace('/_view$/', '', $this->extends_class_name());
		// ucrm_companies_groups -> ucrm_company_group
		$main_class = join('_', array_map(array('blib_grammar', 'singular'), explode('_', $main_class)));

		if(class_include($main_class))
			return $main_class;

		$main_class_up = blib_grammar::singular($main_class);
		if(class_include($main_class_up))
			return $main_class_up;

		$main_class = preg_replace('/^(\w+)_admin_(\w+)$/', '$1_$2', $main_class);

		if(class_include($main_class))
			return $main_class;

		$main_class_up = blib_grammar::singular($main_class);
		if(class_include($main_class_up))
			return $main_class_up;

		bors_throw(ec('Не определён главный класс (model_class()) для представления ').$this->class_name());
	}

	function item_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->model_class());
	}

	function referent_class() { return $this->model_class(); }

	function object() { return $this->model(); } // Для совместимости
	function target() { return $this->model(); } // Для совместимости

	function _title_def() { return $this->model()->title(); }
	function _nav_name_def()
	{
		if($nav = $this->model()->nav_name())
			return $nav;

		return $this->model()->title();
	}

	function _description_def() { return $this->model()->description(); }

	function create_time() { return $this->model()->create_time(); }
	function modify_time() { return $this->model()->modify_time(); }

	function _image_def() { return $this->model()->get('image'); }

	function target_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->model_class());
	}

	function _model_class_def() { return $this->main_class(); }

	function auto_targets()
	{
		static $entered = false;
		if($entered)
			return parent::auto_targets();

		$entered = true;
		$target_name = $this->target_name();
		$entered = false;

		$data = array(
			'model' => 'model_class(id)',
			$target_name => 'model_class(id)',
		);

		return array_merge(parent::auto_targets(), $data);
	}

	function body_data()
	{
		$model = $this->model();
		$data = array(
			$this->item_name() => $model,
			'target' => $model,
			'model' => $model,
			'view' => $this,
			'self' => $this,
		);

		return array_merge(parent::body_data(), $data, $this->model()->data);
	}

	function url() { return $this->model()->url(); }
	function url_ex($page) { return $this->model()->url_ex($page); }
	function admin_url() { return $this->model()->get('admin_url'); }
	function object_type() { return $this->model()->object_type(); }

	function self_class_bors_object_type() { return 'view'; }

	function _owner_id_def() { return object_property($this->model(), 'owner_id'); }

	function _project_name_def() { return bors_core_object_defaults::project_name($this); }
	function _section_name_def() { return object_property($this->model(), 'section_name'); }
	function _config_class_def() { return bors_core_object_defaults::config_class($this); }
	function _is_deleted_def() { return $this->model()->get('is_deleted'); }

	function html_auto()
	{
		$target = $this->model(); // Выводимый объект
		$out = array();
		foreach(bors_lib_orm::main_fields($target) as $idx => $args)
		{
			if(@$args['is_editable'] === false)
				continue;

			$value = $target->get($args['property']);
//			echo '<xmp>'; var_dump($idx, $args, $value); echo '</xmp>';
			if($args['type'] == 'image') // http://ucrm.wrk.ru/persons/4/
			{
				if(preg_match('/^(\w+)_id$/', $args['name'], $m))
					$image = $target->get($m[1]);

				if(!$image && !empty($args['class']))
					$image = bors_load($args['class'], $value);

				if(!$image)
					$image = bors_load('bors_image', $value);

				if($image)
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

		$html = join("", $out);

		return $html;
	}

	function _access_def()
	{
		$access = $this->model()->access();

		if($access)
			$access->set_attr('view', $this);

		return $access;
	}

	/**
		Добавляем модель (выводимый объект) в список объектов, от которых
		зависит наше представление, чтобы при изменениях в модели
		сбрасывать кеши представления
	*/
	function cache_parents()
	{
		return array_merge(parent::cache_parents(), array($this->model()));
	}

	function _html_def() { return $this->body(); }

	function set_model($model)
	{
		$this->set('model', $model, false);
		return $this;
	}

	function suffix($suffix)
	{
		$this->set_attr('body_template_suffix', $suffix);
		return $this;
	}
}
