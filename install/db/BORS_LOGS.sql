CREATE TABLE IF NOT EXISTS `bors_debug_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `title` text NOT NULL,
  `category` varchar(255) NOT NULL,
  `level` varchar(255) NOT NULL,
  `trace` text,
  `owner_id` int(11) DEFAULT NULL,
  `request_uri` text,
  `get_data` text,
  `referer` text,
  `remote_addr` varchar(16) DEFAULT NULL,
  `server_data` text,
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`),
  KEY `level` (`level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
