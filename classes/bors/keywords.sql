-- Поменяйте <<project>> на имя вашего проекта

CREATE TABLE IF NOT EXISTS <<project>>_keywords (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `keyword` VARCHAR(255) NOT NULL,
  `keyword_original` VARCHAR(255) NOT NULL,
  `modify_time` INT(10) UNSIGNED,
  `targets_count` INT(10) UNSIGNED,
  `description` TEXT,
  `linked_class_name` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_general_ci,
  `target_object_id`  VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_general_ci,
  `synonym_to` INT(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `targets_count` (`targets_count`),
  KEY `modify_time` (`modify_time`),
  KEY `keyword` (`keyword`),
  KEY `synonym_to` (`synonym_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
