<?php
    $map = array(
		'.*/\d{4}/\d{1,2}/\d{1,2}/topic\-(\d+)\-rss\.xml => forum_topic_rss(1)',
		'.*/\d{4}/\d{1,2}/topic\-(\d+)\-rss\.xml => forum_topic_rss(1)',
		'.* => page_fs_separate(url)',
		'.* => page_fs_xml(url)',
		'.* => base_page_hts(url)',
		'/do-login/? => common_do_login',
		
		'(/admin/)\?object=([^&]+).* => bors_admin_main(1)',
		'/admin/delete/\?object=([^&]+).* => bors_tools_delete(1)',
		'/admin/edit\-smart/\?object=([^&]+) => bors_admin_edit_smart(1)',
		'/admin/edit\-smart/ => bors_admin_edit_smart',
		'/admin/cross_unlink\?.* => bors_admin_cross_unlink',

		'/admin/tools/set\-sort\-order/ => bors_admin_tools_setsortorder',

		'/admin/\?object=([^&]+).* => bors_admin_main(1)',
		'/admin/edit/\?object=([^&]+).* => bors_admin_edit(1)',
		'/admin/clean/\?object=([^&]+).* => bors_admin_tools_clean(1)',
		'/admin/logout/ => bors_admin_logout',
		
		'/admin/image/append => bors_admin_image_append',
	);
