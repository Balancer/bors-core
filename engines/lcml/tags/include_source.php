<?
    function lt_include_source($params)
    {
        $hts = new DataBaseHTS();
   		return $hts->get_data($params['url'], "source");
   	}
?>
