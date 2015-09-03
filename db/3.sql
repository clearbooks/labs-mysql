CREATE TABLE `subscribers` (
	`user_id` VARCHAR(191) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	UNIQUE INDEX `user_id` (`user_id`)
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB;