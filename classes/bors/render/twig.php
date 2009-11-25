<?php

class bors_render_twig extends bors_render_page
{
	function page_template_engine() { return 'bors_templates_twig'; }
	function body_template_engine() { return 'bors_templates_twig'; }
}
