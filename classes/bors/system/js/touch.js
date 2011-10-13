{foreach from=$module_htmls item="x"}
$(function(){literal}{{/literal} $('#bors_touch_{$x.id}').html("{$x.html|addslashes}") {literal}}{/literal})
{/foreach}
