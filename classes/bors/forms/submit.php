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
		if($th = defval($params, 'th'))
			$value = $th;

		if(empty($value))
			$value = @$title;

		if($image_src = defval($params, 'image'))
			$html .= "<input type=\"image\" src=\"".htmlspecialchars($image_src)."\" value=\"".htmlspecialchars($value)."\"";
		else
			$html .= "<input type=\"submit\" value=\"".htmlspecialchars($value)."\"";

		foreach(explode(' ', 'class style onClick onclick name') as $p)
			if(!empty($$p))
				$html .= " $p=\"{$$p}\"";

		$html .= " />";

		if($th || $form->attr('has_form_table'))
			$html = "<tr><th colspan=\"2\">{$html}</th></tr>\n";

		return $html;
	}
}
