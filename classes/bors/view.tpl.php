<?php

$html = "";

if($view->get('show_edit_link') && $target->access()->can_action())
	$html .= "<div class=\"right\">{$target->admin()->imaged_titled_link('Редактировать')}</div>";

$html .= "<div class=\"yellow_box\"><ul class=\"none\">";
$html .= $view->html_auto();
$html .= "</ul><div class=\"clear\">&nbsp;</div></div>";

if($view->get('show_new_link'))
{
	$x = array(
		'f' => ec('ая'),
		'm' => ec('ый'),
		'n' => ec('ое'),
	);

	$title = ec('Нов').$x[$target->class_title_gender()];

	$html .= "<a href=\"{$view->admin()->urls('new')}\">$title ".bors_lower($target->class_title())."</a>";
}

echo $html;
echo lcml_bb($target->get('source'));
