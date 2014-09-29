<?php

class bors_forms_html extends bors_forms_textarea
{
	function html()
	{
		$html = parent::html();

		assets_codemirror::load('xml,htmlmixed', "'textarea'");
		return $html;
	}
}
