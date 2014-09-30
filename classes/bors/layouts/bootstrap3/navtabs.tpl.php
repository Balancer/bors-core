<?php

if(!$tabs)
	return;

echo "<ul class=\"{$layout->ul_tab_class()}\">\n";

foreach($tabs as $title => $link)
	echo "<li><a href=\"{$link}\">".htmlspecialchars($title)."</a></li>\n";

echo "</ul>\n";
