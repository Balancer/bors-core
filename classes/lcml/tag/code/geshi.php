<?php

if(!config('lcml.code.geshi.base_dir'))
	return;

include_once(config('lcml.code.geshi.base_dir').'/geshi.php');

class lcml_tag_code_geshi extends base_empty
{
	function render($code, $params)
	{
		$code = preg_replace('/^\s*?\n|\s*?\n$/','',$code);

		$geshi = new GeSHi($code, strtolower($params['language']));
		$geshi->set_encoding('utf-8');
		$geshi->enable_classes();
		$geshi->set_header_type(GESHI_HEADER_PRE);
		$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
		$geshi->set_overall_class('code');
		$geshi->set_use_language_tab_width(true);

		$highlighted_code = $geshi->parse_code();

		base_object::add_template_data_array('head_append', '<link rel="stylesheet" type="text/css" href="/_bors/css/bors/code-geshi.css" />');

		return $geshi->error() ? false : $highlighted_code;
	}
}
