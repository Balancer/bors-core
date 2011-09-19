INSERT IGNORE INTO `bors_cross` (
		`type_id`, `is_auto`, `from_class`, `from_id`, `to_class`, `to_id`,
		`sort_order`, `owner_id`, `was_moderated`, `comment`,
		`create_time`, `modify_time`,
		`target_create_time`, `target_modify_time`, `target_time1`, `target_time2`)
	SELECT `type_id`, `is_auto`, `to_class`, `to_id`, `from_class`, `from_id`,
		`sort_order`, `owner_id`, `was_moderated`, `comment`,
		`create_time`, `modify_time`,
		`target_create_time`, `target_modify_time`, `target_time1`, `target_time2` FROM `bors_cross`;

INSERT IGNORE INTO `bors_cross` (
		`type_id`, `is_auto`, `to_class`, `to_id`, `from_class`, `from_id`,
		`sort_order`, `owner_id`, `was_moderated`, `comment`,
		`create_time`, `modify_time`,
		`target_create_time`, `target_modify_time`, `target_time1`, `target_time2`)
	SELECT `type_id`, `is_auto`, `from_class`, `from_id`, `to_class`, `to_id`,
		`sort_order`, `owner_id`, `was_moderated`, `comment`,
		`create_time`, `modify_time`,
		`target_create_time`, `target_modify_time`, `target_time1`, `target_time2` FROM `bors_cross`;
