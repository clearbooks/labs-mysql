CREATE TABLE `release`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `info` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `toggle`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `release_id` INTEGER,
    PRIMARY KEY (`id`),
    INDEX `toggle_fi_578f0a` (`release_id`),
    CONSTRAINT `toggle_fk_578f0a`
    FOREIGN KEY (`release_id`)
    REFERENCES `release` (`id`)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;