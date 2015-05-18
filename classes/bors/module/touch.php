<?php
/*
function template_touch_module($object_uri, $guest_active = false)
{
	bors_page::add_template_data_array('__bors_touch_params', 'modules[]='.urlencode($object_uri));
	bors_page::add_template_data('bors_touch_params', join('&', bors_page::template_data('__bors_touch_params')));
}
*/

class bors_module_touch
{
	static function html($params)
	{
		extract($params);

		$x = bors_load_ex($class, NULL, $params);
		$x->set('is_static', true);
		$uri = $x->internal_uri_ascii();
		echo "<div id=\"bors_touch_".md5($uri)."\" class=\"module-touch\"";
		foreach($params as $name => $value)
			echo " data-$name=\"".htmlspecialchars($value)."\"";
		echo ">";
		echo $x->html();
		echo "</div>";

//		template_jquery();
//		template_touch_module($uri, !empty($guest));
		jquery::on_ready('
modules=[]
$(".module-touch").each(function(){
el=$(this)
modules.push(el.data())
})
$.getScript("/_bors/js/touch?modules="+JSON.stringify(modules)).fail(function(jq,status,ex){console.log(status+": "+ex)})
');
	}
}
