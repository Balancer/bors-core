<?
    function lp_box($txt,$params)
    {
		if(!empty($params['description']))
			return @$params['_align_b']."<dl class=\"box\"><dt>".lcml($params['description'])."</dt><dd>".lcml($txt)."</dd></dl>".@$params['_align_e'];
		elseif(!empty($params['_align_b']))
			return $params['_align_b'].lcml($txt).$params['_align_e'];
		else
			return "<div class=\"box\">".lcml($txt)."</div>";
    }
