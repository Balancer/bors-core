-- DB: BORS
ALTER TABLE `bors_keywords_index` ADD `was_auto` INT NOT NULL , ADD INDEX ( `was_auto` );

