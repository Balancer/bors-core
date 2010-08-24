CREATE TABLE IF NOT EXISTS `bors_favorites` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_class_name` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'Имя класса владельца. ',
	`user_id` INT UNSIGNED NOT NULL COMMENT 'ID владельца. ',
	`target_title` VARCHAR(255) NULL COMMENT 'Название объекта. ',
	`target_class_name` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'Имя класса объекта. ',
	`target_object_id` INT UNSIGNED NOT NULL COMMENT 'ID объекта. ',
	`target_create_time` INT UNSIGNED NOT NULL COMMENT 'Дата создания объекта. ',
	`create_time` INT UNSIGNED NOT NULL,

	PRIMARY KEY (`id`),
	UNIQUE `user_class_name__user_id__target_class_name__target_object_id` (`user_class_name`,`user_id`,`target_class_name`,`target_object_id`),
	KEY `create_time` (`create_time`)
)
