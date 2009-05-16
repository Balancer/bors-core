<?php

//	Пример использования:
//	{js_if cond="top.me_id > 0"}<a href="/users/report/?object={$p->internal_uri()|urlencode}&cat=1">{/js_if}
//	Сообщить модератору
//	{js_if}</a>{/js_if}


function smarty_block_js_if($params, $content, &$smarty)
{
	if($content == NULL) // Открытие формы
	{
		if(!empty($params['cond']))
			base_object::add_template_data('smarty_block_js_if_cond', $params['cond']);

		return;
	}

	echo "<script  type=\"text/javascript\"><!--\n";
	echo "if(".base_object::template_data('smarty_block_js_if_cond')."){\n";
	foreach(explode("\n", $content) as $s)
		echo " document.writeln(\"".addslashes($s)."\")\n";
	echo "}\n";
	echo "--></script>\n";
	return;
}
