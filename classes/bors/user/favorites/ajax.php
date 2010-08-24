<?php

class bors_user_favorites_ajax extends base_jss
{
	function local_data()
	{
		$get = $_GET;
		$object = object_load(urldecode(@$_GET['object']));
		$js = urldecode(@$_GET['js']);
		$op = urldecode(@$_GET['op']);

		$target_uri = $object->internal_uri_ascii();

		if($op == 'add')
		{
			bors_user_favorite::add(bors()->user(), $object);
			$img = '/_bors/i16/favorite.png';
			$aurl = '/_bors/tools/favorites/ajax?op=remove&object='.$target_uri;
		}
		elseif($op == 'remove')
		{
			bors_user_favorite::remove(bors()->user(), $object);
			$img = '/_bors/i16/favorite_gray.png';
			$aurl = '/_bors/tools/favorites/ajax?op=add&object='.$target_uri;
		}

		$id = 'favo_'.$target_uri;

		$script = "
img=jQuery('#{$id}')
img.attr('src', '{$img}')
img.parent().attr('href', '{$aurl}')
";

		return array_merge(parent::local_data(), array(
			'script' => $script,
		));
	}
}
