<?php

class bors_forms_submit extends bors_forms_element
{
	function html()
	{
		if(empty($this))
			echo 0/0;

		$params = $this->params();

		$form = $this->form();

		extract($params);

		$html = "";

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($label = defval($params, 'label', defval($params, 'th')))
			$value = $label;

		if(empty($value))
			$value = @$title;

		$css = array($this->css());

		switch(defval($params, 'type'))
		{
/*
			case 'a':
				$html .= "<a type=\"submit\" class=\"".defval($params, 'css_class')."\">".htmlspecialchars($value)."</button>";
				break;
*/
			case 'button':
				// http://forums.balancer.ru/topics/6932/post/
				// http://www.balancer.ru/admin/forum/post/3033292/move-tree
				$html .= "<button type=\"submit\" class=\"".join(' ', $css)."\">".htmlspecialchars($value)."</button>";
				break;

			default:
				if($image_src = defval($params, 'image'))
					$html .= "<input type=\"image\" src=\"".htmlspecialchars($image_src)."\" value=\"".htmlspecialchars($value)."\"";
				else
					$html .= "<input type=\"submit\" value=\"".htmlspecialchars($value)."\"";

				foreach(explode(' ', 'class style onClick onclick name') as $p)
					if(!empty($$p))
						$html .= " $p=\"{$$p}\"";

				$html .= " />";
				break;
		}

		if($label || $form->attr('has_form_table'))
			$html = "<tr><th>&nbsp;</th><th style=\"text-align: left\">{$html}</th></tr>\n";

		return $html;
	}
}
