<?php

function lcml_line_format($txt, $lcml)
{
	if($lcml->output_type() == 'text')
		return trim(preg_replace("/\n{3,}/", "\n\n", $txt));

	if(empty($GLOBALS['lcml']['cr_type']))
		$cr_type = 'empty_as_para';
	else
		$cr_type = $GLOBALS['lcml']['cr_type'];

//		echo "lcml_line_format,$cr_type: #$txt#\n";

	switch($cr_type)
	{
		case 'none':
			break;
		case 'string_as_para':
			$txt = preg_replace("!(^|\n)!", "\n<p>", $txt); 
			break;
		case 'dblstring_as_para':
			$txt = preg_replace("!(^|(\n\n\n))!", "\n<p>", $txt);
			$txt = preg_replace("!\n\n!", "<br />\n", $txt);
			$txt = preg_replace("!\n!", " ", $txt);
			break;
		case 'save_cr':
		case 'cr_as_br':
			$txt = preg_replace("!\n!", "<br />\n", $txt);
			break;
		case 'smart':
			echo "Smart!";
			$txt = preg_split("!\n{2,}!", $txt);

			if(sizeof($txt) > 1)
				$txt = join("</p>>>>save_cr<<<<p>", $txt);
			else
				$txt = $txt[0];
			$txt = str_replace("\n", "<br/>\n", $txt);
			$txt = str_replace('>>>save_cr<<<', "\n", $txt);
			return "<p>$txt</p>";
			break;
		case 'empty_as_para':
		default:
			$txt = preg_split("!\n{2,}!", $txt);
			if(sizeof($txt) > 1)
				$txt = '<p>'.join("</p>\n\n<p>", $txt).'</p>';
			else
				$txt = $txt[0];
			$txt = str_replace('<p><h', '<h', $txt);
			$txt = preg_replace('!(</h\d>)</p>!', '\1', $txt);
			break;
	}

	return $txt;
}
