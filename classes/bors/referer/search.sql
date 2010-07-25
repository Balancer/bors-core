CREATE TABLE IF NOT EXISTS `bors_referer_search` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `create_time` int(10) unsigned NOT NULL,
  `modify_time` int(10) unsigned NOT NULL,
  `query` varchar(255) NOT NULL,
  `target_class_name` varchar(64) character set latin1 collate latin1_general_ci NOT NULL,
  `target_object_id` varchar(255) NOT NULL,
  `target_page` int(10) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL,
  `search_engine` varchar(255) NOT NULL,
  `target_url` varchar(255) NOT NULL,
  `search_url` varchar(255) NOT NULL,
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `query` (`query`(133),`target_class_name`,`target_object_id`(133),`target_page`),
  KEY `create_time` (`create_time`),
  KEY `modify_time` (`modify_time`)
) DEFAULT CHARSET=utf8;

