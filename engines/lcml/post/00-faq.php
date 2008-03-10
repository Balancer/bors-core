<?
    function lcml_faq($txt)
    {
        $txt = preg_replace("!^Q:\s+!m", "<p/><b><i>Вопрос:</i></b> ", $txt);
        $txt = preg_replace("!^A:\s+!m", "<br/><b><i>Ответ:</i></b> ", $txt);
        return $txt;
    }
?>
