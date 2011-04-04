-- В БД BORS. Дополнительные поля тэгов.
ALTER TABLE `bors_keywords` ADD `keyword_forms` text COMMENT 'Словоформы для жёсткого, но вариативного поиска' AFTER `keyword_original`;
ALTER TABLE `bors_keywords` ADD `target_containers_count` int(11) NOT NULL AFTER `targets_count`;
ALTER TABLE `bors_keywords` ADD `tree_map` varchar(255) NOT NULL AFTER `synonym_to`;
ALTER TABLE `bors_keywords` ADD `is_autosearch_original` tinyint(1) unsigned DEFAULT NULL AFTER `tree_map`;
ALTER TABLE `bors_keywords` ADD `is_autosearch_normalized` tinyint(1) unsigned DEFAULT NULL AFTER `is_autosearch_original`;
ALTER TABLE `bors_keywords` ADD `is_moderated` tinyint(1) NOT NULL DEFAULT 0;
