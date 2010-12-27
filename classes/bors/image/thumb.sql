CREATE TABLE IF NOT EXISTS `bors_pictures_thumbs` (
  `id` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alt` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `author_name` varchar(255) NOT NULL,
  `picture_type` varchar(32) NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `modify_time` int(10) unsigned NOT NULL,
  `full_file_name` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `full_url` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `relative_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `width` int(10) unsigned NOT NULL,
  `height` int(10) unsigned NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `extension` varchar(6) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `created_from` varchar(255) DEFAULT NULL,
  `moderated` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `full_file_name` (`full_file_name`),
  UNIQUE KEY `full_url` (`full_url`),
  KEY `create_time` (`create_time`),
  KEY `modify_time` (`modify_time`),
  KEY `relative_path` (`relative_path`),
  KEY `file_name` (`file_name`),
  KEY `width` (`width`),
  KEY `height` (`height`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
