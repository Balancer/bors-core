CREATE TABLE IF NOT EXISTS `bors_cross` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type_id` int(10) unsigned default NULL,
  `from_class` int(10) unsigned NOT NULL default '0',
  `from_id` int(10) unsigned NOT NULL default '0',
  `to_class` int(10) unsigned NOT NULL default '0',
  `to_id` int(10) unsigned NOT NULL default '0',
  `sort_order` int(11) NOT NULL default '0',
  `create_time` int(10) unsigned default NULL,
  `modify_time` int(10) unsigned default NULL,
  `target_create_time` int(11) default NULL,
  `target_modify_time` int(11) default NULL,
  `target_time1` int(11) default NULL,
  `target_time2` int(11) default NULL,
  `owner_id` int(11) default NULL COMMENT 'Кто добавил эту связь. Если положительное - то ID добавившего пользователя. Если отрицательно - от ID добавляющего скрипта.',
  `was_moderated` int(11) default NULL,
  `comment` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `from_class_2` (`from_class`,`from_id`,`to_class`,`to_id`),
  KEY `from_class` (`from_class`,`from_id`),
  KEY `to_class` (`to_class`,`to_id`),
  KEY `type_id` (`type_id`),
  KEY `create_time` (`create_time`),
  KEY `modify_time` (`modify_time`),
  KEY `owner_id` (`owner_id`),
  KEY `was_moderated` (`was_moderated`),
  KEY `target_create_time` (`target_create_time`),
  KEY `target_modify_time` (`target_modify_time`),
  KEY `target_time1` (`target_time1`),
  KEY `targer_time2` (`target_time2`)
);
