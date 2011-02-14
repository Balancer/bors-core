CREATE TABLE IF NOT EXISTS `bors_search_source_0` (
  `word_id` int(11) NOT NULL,
  `target_class_id` int(11) NOT NULL,
  `target_object_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `target_create_time` int(11) NOT NULL,
  `target_modify_time` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bors_search_source_1` (
  `word_id` int(11) NOT NULL,
  `target_class_id` int(11) NOT NULL,
  `target_object_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `target_create_time` int(11) NOT NULL,
  `target_modify_time` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bors_search_source_2` (
  `word_id` int(11) NOT NULL,
  `target_class_id` int(11) NOT NULL,
  `target_object_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `target_create_time` int(11) NOT NULL,
  `target_modify_time` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bors_search_source_3` (
  `word_id` int(11) NOT NULL,
  `target_class_id` int(11) NOT NULL,
  `target_object_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `target_create_time` int(11) NOT NULL,
  `target_modify_time` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bors_search_source_4` (
  `word_id` int(11) NOT NULL,
  `target_class_id` int(11) NOT NULL,
  `target_object_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `target_create_time` int(11) NOT NULL,
  `target_modify_time` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bors_search_source_5` (
  `word_id` int(11) NOT NULL,
  `target_class_id` int(11) NOT NULL,
  `target_object_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `target_create_time` int(11) NOT NULL,
  `target_modify_time` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bors_search_source_6` (
  `word_id` int(11) NOT NULL,
  `target_class_id` int(11) NOT NULL,
  `target_object_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `target_create_time` int(11) NOT NULL,
  `target_modify_time` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bors_search_source_7` (
  `word_id` int(11) NOT NULL,
  `target_class_id` int(11) NOT NULL,
  `target_object_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `target_create_time` int(11) NOT NULL,
  `target_modify_time` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bors_search_source_8` (
  `word_id` int(11) NOT NULL,
  `target_class_id` int(11) NOT NULL,
  `target_object_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `target_create_time` int(11) NOT NULL,
  `target_modify_time` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bors_search_source_9` (
  `word_id` int(11) NOT NULL,
  `target_class_id` int(11) NOT NULL,
  `target_object_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `target_create_time` int(11) NOT NULL,
  `target_modify_time` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bors_search_titles` (
  `word_id` int(11) NOT NULL,
  `target_class_id` int(11) NOT NULL,
  `target_object_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `target_create_time` int(11) NOT NULL,
  `target_modify_time` int(11) NOT NULL,
  KEY `word_id` (`word_id`,`target_class_id`,`target_object_id`),
  KEY `word_id_2` (`word_id`,`target_class_id`,`target_object_id`,`target_create_time`),
  KEY `word_id_3` (`word_id`,`target_class_id`,`target_object_id`,`target_modify_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bors_search_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

