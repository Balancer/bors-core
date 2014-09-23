<?php

	foreach(bors_lib_object::parent_lines($self) as $breadcrumbs_line)
	{
?>
<div class="ui breadcrumb">
<?php
		foreach($breadcrumbs_line as $x)
		{
			if(empty($x['is_active']))
				echo "<a class=\"section\" href=\"{$x['url']}\">{$x['title']}</a>\n<div class=\"divider\"> / </div>\n";
			else
				echo "<a class=\"active section\">{$x['title']}</a>\n";
		}
?>
</div>
<?php
	}

?>
