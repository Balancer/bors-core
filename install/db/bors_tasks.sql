CREATE TABLE IF NOT EXISTS `bors_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `target_class_id` int(10) unsigned NOT NULL,
  `target_object_id` int(10) unsigned NOT NULL,
  `target_object_page` int(10) unsigned NOT NULL,
  `working_class_id` int(10) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `execute_time` int(10) unsigned NOT NULL,
  `priority` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `target_class_id` (`target_class_id`,`target_object_id`,`target_object_page`,`working_class_id`),
  KEY `create_time` (`create_time`),
  KEY `execute_time` (`execute_time`),
  KEY `priority` (`priority`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
