CREATE TABLE IF NOT EXISTS `moderator_notes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `create_time` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `moderator_id` int(10) unsigned NOT NULL,
  `target_class_id` int(10) unsigned NOT NULL,
  `target_object_id` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `target_class_id` (`target_class_id`,`target_object_id`),
  KEY `create_time` (`create_time`),
  KEY `user_id` (`user_id`),
  KEY `moderator_id` (`moderator_id`)
);
