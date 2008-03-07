<?php
    $map = array(
		'.*/\d{4}/\d{1,2}/\d{1,2}/topic\-(\d+)\-rss\.xml => forum_topic_rss(1)',
//		'.* => base_page_hts',
		'.* => page_fs_separate',
		
		'/do-login/? => common_do_login',
	);
