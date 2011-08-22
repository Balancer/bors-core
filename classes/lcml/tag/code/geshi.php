<?php

if(!config('geshi_include'))
	return;

include_once(config('geshi_include'));

class lcml_tag_code_geshi extends base_empty
{
	function render($code, $params)
	{
		$inline = strpos($code, "\n") === false;
		// Страшный костыль. Но иначе иногда рвёт после вставки кривых строк из под винды.
		if(str_replace('�', '?', $code) != $code)
			return "<div class=\"box error\">Ошибка: некорректные символы в коде</div>";

		$code = preg_replace('/^\s*?\n|\s*?\n$/','',$code);
		$lang1 = bors_lower(empty($params['language']) ? 'text' : $params['language']);

		$geshi = new GeSHi($code, NULL);
		$lang2 = $geshi->get_language_name_from_extension($lang1);
		$geshi->set_language($lang = ($lang2 ? $lang2 : $lang1));
		$geshi->set_encoding('UTF-8');
		$geshi->enable_classes();
		$geshi->set_header_type(GESHI_HEADER_NONE);
		$geshi->enable_line_numbers($inline ? GESHI_NO_LINE_NUMBERS : GESHI_FANCY_LINE_NUMBERS);
		$geshi->set_overall_class('code');
		$geshi->set_use_language_tab_width(true);

		$highlighted_code = $geshi->parse_code();

		if($geshi->error())
			return false;

		template_css('/_bors/css/bors/code-geshi.css');

		if(empty($params['description']))
			$title = "code $lang";
		else
			$title = $params['description'];

		if($inline)
			$html = "<span class=\"code inline-code\">$highlighted_code</span>";
		else
			$html = "<div class=\"code-head\">{$title}</div>$highlighted_code";

		return $html;
	}
}
