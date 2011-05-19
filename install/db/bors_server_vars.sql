CREATE TABLE IF NOT EXISTS `bors_server_vars` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `title` varchar(255) DEFAULT NULL,
  `value` text character set utf8,
  `type` varchar(255) default NULL,
  `create_time` int(10) unsigned NOT NULL default '0',
  `modify_time` int(10) unsigned NOT NULL default '0',
  `expire_time` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `create_time` (`create_time`),
  KEY `modify_time` (`modify_time`),
  KEY `expire_time` (`expire_time`)
) DEFAULT CHARSET=utf8;

