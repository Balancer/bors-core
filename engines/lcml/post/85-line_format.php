<?php

    function lcml_line_format($txt)
    {
        if(empty($GLOBALS['lcml']['cr_type']))
            $cr_type = 'empty_as_para';
        else
            $cr_type = $GLOBALS['lcml']['cr_type'];

//		$txt .= "===$cr_type==<xmp>$txt</xmp>==";

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
            case 'empty_as_para':
            default:
                $txt = preg_split("!\n{2,}!", $txt);

				if(sizeof($txt) > 1)
					$txt = join("</p>\n\n<p>", $txt);
				else
					$txt = $txt[0];
                break;
        }

        return $txt;
    }
