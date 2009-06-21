CREATE TABLE IF NOT EXISTS `bors_access_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_ip` varchar(16) NOT NULL,
  `user_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `server_uri` varchar(255) NOT NULL,
  `referer` varchar(255) NOT NULL,
  `class_name` varchar(64) NOT NULL,
  `object_id` int(11) NOT NULL,
  `access_time` int(11) NOT NULL,
  `operation_time` float NOT NULL,
  `has_bors` tinyint(1) unsigned NOT NULL,
  `has_bors_url` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_ip` (`user_ip`)
);
