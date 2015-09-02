CREATE TABLE `release` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`info` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`id`)
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB;

CREATE TABLE `toggle_type` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`type_name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`id`)
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB;

INSERT INTO `toggle_type` (`id`, `type_name`) VALUES
(NULL , 'user_toggle'),
(NULL, 'group_toggle');

CREATE TABLE `toggle` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(128) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`release_id` INT(11) NOT NULL,
	`toggle_type` INT(11) NOT NULL,
	`is_active` TINYINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `name` (`name`),
	INDEX `toggle_fi_1` (`release_id`),
	INDEX `toggle_fi_2` (`toggle_type`),
	CONSTRAINT `toggle_fk_1` FOREIGN KEY (`release_id`) REFERENCES `release` (`id`),
	CONSTRAINT `toggle_fk_2` FOREIGN KEY (`toggle_type`) REFERENCES `toggle_type` (`id`)
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB;

CREATE TABLE `user_activated_toggle` (
	`user_id` BIGINT(20) UNSIGNED NOT NULL,
	`toggle_id` INT(11) NOT NULL,
	`is_active` TINYINT(4) NOT NULL,
	PRIMARY KEY (`user_id`, `toggle_id`),
	INDEX `FK__toggle` (`toggle_id`),
	CONSTRAINT `FK__toggle` FOREIGN KEY (`toggle_id`) REFERENCES `toggle` (`id`)
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB;

CREATE TABLE `group_activated_toggle` (
	`group_id` BIGINT(20) UNSIGNED NOT NULL,
	`toggle_id` INT(11) NOT NULL,
	`active` TINYINT(4) NOT NULL,
	PRIMARY KEY (`group_id`, `toggle_id`),
	INDEX `FK__toggle_group` (`toggle_id`),
	CONSTRAINT `FK__toggle_group` FOREIGN KEY (`toggle_id`) REFERENCES `toggle` (`id`)
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB;

CREATE TABLE `toggle_marketing_information` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`toggle_id` INT(11) NOT NULL,
	`screenshot_urls` VARCHAR(128) NULL DEFAULT NULL,
	`description_of_toggle` TEXT NULL,
	`description_of_functionality` TEXT NULL,
	`description_of_implementation_reason` TEXT NULL,
	`description_of_location` TEXT NULL,
	`guide_url` VARCHAR(128) NULL DEFAULT NULL,
	`app_notification_copy_text` TEXT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_toggle_marketing_information_toggle` (`toggle_id`),
	CONSTRAINT `FK_toggle_marketing_information_toggle` FOREIGN KEY (`toggle_id`) REFERENCES `toggle` (`id`)
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB;
