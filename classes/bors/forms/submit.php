<?php

class bors_forms_submit extends bors_forms_element
{
	function html()
	{
		$params = $this->params();

		$form = $this->form();

		extract($params);

		$html = "";

		if($label = $this->label())
			$value = $label;

		if(empty($value))
			$value = ec('Сохранить');

		$css = array($this->css());
		$css_class_html = $css ? ' class="'.join(' ', $css).'"' : '';

		switch(defval($params, 'type'))
		{
			case 'a':
//				$html .= "<a type=\"submit\"{$css_class_html}>".htmlspecialchars($value)."</a>";
				break;

			case 'button':
				// http://forums.balancer.ru/topics/6932/post/
				// http://www.balancer.ru/admin/forum/post/3033292/move-tree
				$html .= "<button type=\"submit\"{$css_class_html}>".htmlspecialchars($value)."</button>";
				break;

			default:
				$attrs = array();
				foreach(explode(' ', 'style onClick onclick name') as $p)
					if(!empty($$p))
						$attrs[] = "$p=\"{$$p}\"";

				foreach(array('dom_id' => 'id') as $var => $hn)
					if(!empty($$var))
						$attrs[] = "$hn=\"{$$var}\"";

				if($image_src = defval($params, 'image'))
					$html .= "<input type=\"image\" src=\"".htmlspecialchars($image_src)."\" value=\"".htmlspecialchars($value)."\"";
				else
//					"<input type=\"submit\" value=\"".htmlspecialchars($value)."\"";
					$html .= sprintf($this->form()->templater()->form_element_submit_html(),
						htmlspecialchars($value),
						join(" ", $attrs).' '.$css_class_html
					);

				break;
		}

		if(($label || $form->attr('has_form_table')) && empty($no_tab))
			$html = "<tr><th>&nbsp;</th><th style=\"text-align: left\">{$html}</th></tr>\n";

		return $html;
	}
}
