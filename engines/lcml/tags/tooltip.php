<?php

function lp_tooltip($tooltip, $params)
{
	template_jquery();
	template_jquery_plugin('jquery.qtip-1.0.js');
	template_js("
\$(document).ready(function()
{
	\$('.tooltip').each(function()
	{
		\$(this).qtip(
		{
			content: \$(this).attr('title'), // Use the ALT attribute of the area map
			style: {
				name: 'cream', // Give it the preset dark style
				border: {
					width: 0,
					radius: 4
				},
				width: 700,
				tip: true // Apply a tip at the default tooltip corner
			}
		});
		\$(this).attr('title', '')
	});
});");

	$text = defval($params, 'text');
	$tooltip = lcml($tooltip);
	return "<span class=\"tooltip\" title=\"".htmlspecialchars($tooltip)."\">{$text}</span>";
}
