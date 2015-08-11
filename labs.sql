# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- release
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `release`;

CREATE TABLE `release`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `info` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- toggle
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `toggle`;

CREATE TABLE `toggle`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `release_id` INTEGER NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `toggle_fi_578f0a` (`release_id`),
    CONSTRAINT `toggle_fk_578f0a`
        FOREIGN KEY (`release_id`)
        REFERENCES `release` (`id`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;