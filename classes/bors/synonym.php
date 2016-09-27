<?php

class bors_synonym extends bors_object_db
{
	function _class_title_rp_def() { return ec('синонима'); }
	function class_title_vp() { return ec('синоним'); }
	function table_name() { return config('synonyms_table', 'bors_synonyms'); }

	function table_fields()
	{
		return array(
			'id',
			'title',
			'norm_title',
			'target_class_name',
			'target_object_id',
			'is_disabled',
			'is_exactly',
			'is_auto',
			'owner_id',
			'create_time',
			'modify_time',
		);
	}

	function title() { return $this->data['title']; }

	function post_set(&$data)
	{
		$this->set_norm_title(bors_text_clear($data['title'], true));
	}

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array('target' => 'target_class_name(target_object_id)'));
	}

	// На всякий случай принудительно укажем. Чтобы не терялись пометки о запрете.
	function replace_on_new_instance() { return false; }

	// Однако, надо как-то и с дублями бороться. Так что — игнором.
	function ignore_on_new_instance() { return true; }

	static function add_object($x1, $x2 = array(), $params = array())
	{
		require_once BORS_CORE.'/inc/texts.php';

		if(is_object($x1))
		{
			$object = $x1;
			$params = $x2;

			if(method_exists($object, 'all_names'))
			{
				foreach($object->all_names() as $name)
				{
					if(preg_match('/^!(.+)$/', $name, $m))
						self::add_object($m[1], $object, array_merge($params, array('is_exactly' => true)));
					else
						self::add_object($name, $object, array_merge($params, array('is_exactly' => false)));
				}
			}

			$title = $object->title();
		}
		else
		{
			$title = $x1;
			$object = $x2;
		}

		$is_exactly = defval($params, 'is_exactly');
		$synonyms = defval($params, 'synonyms', []);

		if($is_exactly)
			$norm_title = trim(bors_lower($title));
		else
			$norm_title = bors_text_clear($title, true);

		if($title && $norm_title && empty($synonyms[$title]))
		{
			$x = object_new_instance('bors_synonym', [
				'title' => $title,
				'norm_title' => $norm_title,
				'target_class_name' => $object->extends_class_name(),
				'target_object_id' => $object->id(),
				'is_exactly' => $is_exactly,
				'is_auto' => defval($params, 'is_auto'),
				'is_disabled' => defval($params, 'is_disabled'),
			]);

			$synonyms[$title] = $x;
		}

		return $synonyms;
	}

	static function synonyms($object, $params = array())
	{
		$synonym_class_name = $object->get('synonym_class_name');

		if(!$synonym_class_name)
			$synonym_class_name = 'bors_synonym';

		$where = array(
			'target_class_name' => $object->extends_class_name(),
			'target_object_id' => $object->id(),
//			'order' => 'title',
		);

		if(array_key_exists('is_disabled', $params))
			$where['is_disabled'] = $params['is_disabled'];

		return bors_find_all($synonym_class_name, $where);
	}

	function check_data(&$data)
	{
		if($obj = bors_find_first($this->extends_class_name(), array('title' => $data['title'], 'target_class_name' => $data['target_class_name'], 'target_object_id' => $data['target_object_id'])))
			return bors_message(ec('Такой синоним уже прописан у этого объекта'));

		return parent::check_data($data);
	}
}
