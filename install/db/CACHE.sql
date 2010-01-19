-- Таблицы системы для хранения кешированныйх данных

CREATE TABLE `cached_files` (
  `file` varchar(255) NOT NULL default '',
  `uri` varchar(255) NOT NULL default '',
  `original_uri` varchar(255) NOT NULL default '',
  `last_compile` int(11) NOT NULL default '0',
  `expire_time` int(11) NOT NULL default '0',
  `class_name` varchar(64) character set latin1 collate latin1_general_ci default NULL,
  `class_id` int(10) unsigned NOT NULL,
  `object_id` int(11) NOT NULL,
  `recreate` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY  (`file`),
  KEY `uri` (`uri`),
  KEY `original_uri` (`original_uri`),
  KEY `last_compile` (`last_compile`),
  KEY `expire_time` (`expire_time`),
  KEY `class_id` (`class_id`),
  KEY `object_id` (`object_id`),
  KEY `file` (`file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
