CREATE TABLE `release`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `info` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `toggle_type`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `type_name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO `toggle_type` (`id`, `type_name`) VALUES
(NULL , 'user_toggle');

CREATE TABLE `toggle` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
 `release_id` int(11) NOT NULL,
 `toggle_type` int(11) NOT NULL,
 `is_activatable` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 KEY `toggle_fi_1` (`release_id`),
 KEY `toggle_fi_2` (`toggle_type`),
 CONSTRAINT `toggle_fk_1` FOREIGN KEY (`release_id`) REFERENCES `release` (`id`),
 CONSTRAINT `toggle_fk_2` FOREIGN KEY (`toggle_type`) REFERENCES `toggle_type` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci