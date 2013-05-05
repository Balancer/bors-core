<?php

class bors_image_generated extends base_page_db
{
	function main_table() { return 'bors_image_generated'; }
	function can_be_empty() { return true; }

	function main_table_fields()
	{
		return array(
			'id',
			'hash_id',
			'generator_class_name',
			'generator_data',
			'description',
			'base_name',
			'image_url',
			'dir',
			'width',
			'height',
			'visits',
			'first_visit_time',
			'last_visit_time',
			'create_time',
			'modify_time',
			'owner_id',
			'last_editor_id',
		);
	}

function hash_id() { return @$this->data['hash_id']; }
function set_hash_id($v, $dbup) { return $this->set('hash_id', $v, $dbup); }
function generator_class_name() { return @$this->data['generator_class_name']; }
function set_generator_class_name($v, $dbup) { return $this->set('generator_class_name', $v, $dbup); }
function generator_data() { return @$this->data['generator_data']; }
function set_generator_data($v, $dbup) { return $this->set('generator_data', $v, $dbup); }
function base_name() { return @$this->data['base_name']; }
function set_base_name($v, $dbup) { return $this->set('base_name', $v, $dbup); }
function image_url() { return @$this->data['image_url']; }
function set_image_url($v, $dbup) { return $this->set('image_url', $v, $dbup); }
function set_dir($v, $dbup) { return $this->set('dir', $v, $dbup); }
function width() { return @$this->data['width']; }
function set_width($v, $dbup) { return $this->set('width', $v, $dbup); }
function height() { return @$this->data['height']; }
function set_height($v, $dbup) { return $this->set('height', $v, $dbup); }
function visits() { return @$this->data['visits']; }
function set_visits($v, $dbup) { return $this->set('visits', $v, $dbup); }
function first_visit_time() { return @$this->data['first_visit_time']; }
function set_first_visit_time($v, $dbup) { return $this->set('first_visit_time', $v, $dbup); }
function last_visit_time() { return @$this->data['last_visit_time']; }
function set_last_visit_time($v, $dbup) { return $this->set('last_visit_time', $v, $dbup); }
function owner_id() { return @$this->data['owner_id']; }
function set_owner_id($v, $dbup) { return $this->set('owner_id', $v, $dbup); }
function last_editor_id() { return @$this->data['last_editor_id']; }
function set_last_editor_id($v, $dbup = true) { return $this->set('last_editor_id', $v, $dbup); }

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array('generator' => 'generator_class_name(generator_data)'));
	}

	function visits_counting() { return true; }

	function render_engine() { return 'bors_image_generated'; }
	function render($object)
	{
		$object->views_inc();
		return $object->generator()->image();
	}

	function url($page=NULL) { return $this->image_url(); }

	function html() { return $this->html_code(); }
	function html_code()
	{
		$params = unserialize($this->id());
//		if(!($g = $this->generator()))
		{
			$g = object_new($params['class_name'], $params);

			$x = bors_find_first(__CLASS__, array('hash_id' => md5($this->id())));
			if(!$x)
			{
				$x = object_new_instance('bors_image_generated', array(
					'hash_id' => md5($this->id()),
					'generator_class_name' => $params['class_name'],
					'generator_data' => serialize($params['data']),
					'base_name' => $g->base_name(),
					'image_url' => $g->url(),
					'dir' => $g->dir(),
					'width' => $g->width(),
					'height' => $g->height(),
					'description' => $description = $g->description(),
				));
			}

			mkpath($g->dir());
			$image = $g->image();

			if($params['crop'])
			{
				list($top, $right, $bottom, $left) = explode(',', $params['crop']);
				$src = imagecreatefromstring($image);
				$w = imagesx($src) - $right - $left;
				$h = imagesy($src) - $top - $bottom;
				$dest = imagecreatetruecolor($w, $h);
				imagecopy($dest, $src, 0, 0, $top, $left, $w, $h);
				ob_start();
				imagepng($dest);
				$image = ob_get_contents();
				ob_clean();
			}
			else
			{
				$w = $g->width();
				$h = $g->height();
				$description = $g->description();
			}

			file_put_contents($g->file_path(), $image);
		}


		if(!empty($params['show_description']))
			$description = "<br/>{$description}<div class=\"clear\">&nbsp;</div>";
		else
			$description = '';

		$width  = $w ? ' width="' .$w.'"' : '';
		$height = $h ? ' height="'.$h.'"' : '';
		return "<img src=\"{$x->url()}\"$w$h />{$description}";
	}
}
