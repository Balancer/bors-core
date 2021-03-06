<?php

use HtmlObject\Element;
use HtmlObject\Input;

/*
	Тесты и проверки на http://admin.aviaport.wrk.ru/infrastructure/events/new/
*/

class bors_forms_select2_latest extends bors_forms_select2
{
	function html()
	{
//		$this->params['width'] = '50%';

		$parent_html = parent::html();
		$select2_html = Element::div()->appendChild($parent_html)->getChild(0);
		$select2_html->style('width:80%')
//			->addClass('form-control')
		;

//		echo "<xmp>", $select2_html, "</xmp><hr/>";

		$params = $this->params();

		$form = $this->form();

		$field_name = $params['name'];

		$model_class = $form->model_class();
		$field_class_name = $params['main_class'];

//		unset($params['form'], $params['form_params']);
//		echo '<xmp>'; print_r($params); echo '</xmp>';

		$latest = bors_find_all($model_class, array(
			'inner_join' => "$field_class_name ON {$model_class}.{$field_name}={$field_class_name}.id",
			'group' => $field_name,
			'limit' => 4,
		));

		$input_name = '_s2l_'.$field_name;

/*
	<div class="input-group">
      <span class="input-group-addon">
        <input type="radio" aria-label="...">
      </span>
      <input type="text" class="form-control" aria-label="...">
    </div><!-- /input-group -->
*/
		$group = Element::div()->addClass('input-group')
			->nest(Element::span()->addClass('input-group-addon')
				->nest(Input::radio($input_name, 0)));

		$group->appendChild($select2_html);

		$html = Element::div()->nest($group);

		foreach($latest as $x)
		{
			$inp = Input::radio($input_name, $x->get($field_name))
				->style('margin: 4px 15px 4px 7px')
				.$x->airport()->title();

//				->appendChild(Element::br())
//				->wrapWith(Element::label())
//			;

			$html = $html->appendChild(Element::label($inp))
//				->nest($x->airport()->title())
			;
		}

//		echo "<code>Result:\n", htmlspecialchars($html), "</code>";

		$select2_id = preg_replace('/^.*<input id="(.+?)".+$/', '$1', $parent_html);
		jquery::on_ready("
$('#{$select2_id}').on('select2-selecting', function(e) {
	$('input:radio[name={$input_name}]').filter('[value=0]').prop('checked', true)
})

$('input:radio[name={$input_name}]').click(function() {
	$('#{$select2_id}').select2('data', {id: $(this).val(), text: $(this).parent().text() })
})
");

		return $html;
	}
}
