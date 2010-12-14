CREATE TABLE IF NOT EXISTS `cache_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cache_group` varchar(128) NOT NULL DEFAULT '',
  `_target_class_id` int(64) unsigned NOT NULL DEFAULT '0',
  `_target_object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cache_group` (`cache_group`,`_target_class_id`,`_target_object_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

