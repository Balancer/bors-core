{foreach from=$module_htmls item="x"}
$(function() { $('#bors_touch_{$x.id}').html("{$x.html|addslashes|replace:"\n":"\\n"}") } )
{/foreach}
