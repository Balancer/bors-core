<?
    function lp_box($txt,$params)
    {
		if(!empty($params['description']))
			return $params['_align_b']."<dl class=\"box\"><dt>".lcml($params['description'])."</dt><dd>".lcml($txt)."</dd></dl>".$params['_align_e'];
		else
			return $params['_align_b'].lcml($txt).$params['_align_e'];

/*		return <<<__EOT__
<table border="0" width="{$params['width']}" cellPadding="8" cellSpacing="0" align="{$params['align']}">
<tr><td><div class="box">$txt</div></td></tr></table>
__EOT__; */

    }

?>