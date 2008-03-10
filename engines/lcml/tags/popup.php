<?
    function lt_popup($params)
    { 
        // [popup http://www.ru 400x300|Text]
        if(empty($params['width']) || $params['width'] == '100%')
            $params['width'] = 500;
        if(empty($params['height']))
            $params['height'] = 400;
        return "<a href=\"{$params['url']}\" class=\"popup\" target=\"_blank\" onClick=\"window.open('{$params['url']}','Popup".md5($params['url'])."','toolbar=no,directories=no,width={$params['width']},height={$params['height']},resizable=yes'); return false;\">{$params['description']}</a>";
    }
?>