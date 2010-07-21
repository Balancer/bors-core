<?php
    function lsp_pref($txt) 
    { 
        return "<p>".join("\n<p>", explode("\n",$txt));
    }
?>