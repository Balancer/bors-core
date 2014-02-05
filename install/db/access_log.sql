DROP TABLE IF EXISTS `bors_access_log`;

CREATE TABLE `bors_access_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_ip` varchar(16) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `url` varchar(4095) NOT NULL,
  `server_uri` varchar(4095) NOT NULL,
  `referer` varchar(4095) DEFAULT NULL,
  `class_name` varchar(64) NOT NULL,
  `object_id` varchar(255) DEFAULT NULL,
  `access_time` int(10) unsigned NOT NULL,
  `operation_time` float NOT NULL,
  `has_bors` tinyint(1) unsigned NOT NULL,
  `has_bors_url` tinyint(1) unsigned NOT NULL,
  `user_agent` varchar(4095) DEFAULT NULL,
  `is_bot` varchar(64) DEFAULT NULL,
  `was_counted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_ip` (`user_ip`),
  KEY `access_time` (`access_time`),
  KEY `was_counted` (`was_counted`),
  KEY `user_ip___access_time` (`user_ip`,`access_time`)
) ENGINE=MEMORY ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8;
