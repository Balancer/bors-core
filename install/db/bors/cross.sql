CREATE TABLE IF NOT EXISTS `bors_cross` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(10) unsigned DEFAULT NULL,
  `is_auto` tinyint(1) unsigned NOT NULL COMMENT 'Выставляется, если связь была получена автоматически.',
  `from_class` int(10) unsigned NOT NULL DEFAULT '0',
  `from_id` int(10) unsigned NOT NULL DEFAULT '0',
  `to_class` int(10) unsigned NOT NULL DEFAULT '0',
  `to_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `from_class_2` (`from_class`,`from_id`,`to_class`,`to_id`),
  KEY `from_class` (`from_class`,`from_id`),
  KEY `to_class` (`to_class`,`to_id`),
  KEY `type_id` (`type_id`),
  KEY `is_auto` (`is_auto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
