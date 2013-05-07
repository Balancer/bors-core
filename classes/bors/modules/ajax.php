<?php

class bors_modules_ajax extends bors_module
{
	static function static_html($params)
	{
		extract($params);

		$x = bors_load($class, NULL);
		$x->set_attr('static', true);
		$x->set_mode('static');
		$uri = $x->internal_uri_ascii();
		$dom_id = "bors_mod_ajax_".md5($uri);
		echo "<div id=\"$dom_id\">";
		echo $x->html();
		echo "</div>";

		jquery::load();

		if($f = $x->get('ajax_effect'))
		{
			jquery::on_ready("\$('#$dom_id').hide(); \$.get('/_bors/mod-ajax?".http_build_query($params)."',"
				."function(html) { \$('#$dom_id').html(html); $('#$dom_id')$f})");
		}
		else
		{
			jquery::on_ready("\$.get('/_bors/mod-ajax?".http_build_query($params)."',"
				."function(html) { \$('#$dom_id').html(html)})");
		}
	}

	function content()
	{
		$params = bors()->request()->data();
		$class = $params['class'];
		$x = bors_load($class, NULL);
		$x->set_attr('ajax', true);
		$x->set_mode('ajax');
		return $x->html();
	}
}
