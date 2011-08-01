<?php

if(config('smarty3_enable'))
	eval('class bors_templates_smarty extends bors_templates_smarty3 { }');
else
	eval('class bors_templates_smarty extends bors_templates_smarty2 { }');

