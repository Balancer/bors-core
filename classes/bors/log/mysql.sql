CREATE TABLE IF NOT EXISTS `bors_logging` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`category` VARCHAR(255) NOT NULL,
	`type` VARCHAR(255) NOT NULL,
	`title` TEXT NOT NULL,
	`target_class_name` VARCHAR(255) NOT NULL,
	`target_object_id` VARCHAR(255) NOT NULL,
	`message` TEXT NOT NULL,
	`create_time` INT NOT NULL,
	`modify_time` INT NOT NULL,
	`owner_id` INT NOT NULL,
	`last_editor_id` INT NOT NULL,

	PRIMARY KEY (`id`),
	KEY `category` (`category`),
	KEY `type` (`type`),
	KEY `create_time` (`create_time`),
	KEY `modify_time` (`modify_time`)
)
