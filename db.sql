CREATE DATABASE IF NOT EXISTS instagram;
use instagram;

CREATE TABLE IF NOT EXISTS `User` (
	`ID` bigint(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`email` text NOT NULL,
	`password` text NOT NULL,
	`rights` int NOT NULL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS `Account` (
	`ID` bigint(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`user_id` bigint(20) UNSIGNED NOT NULL,
	`email` text NOT NULL,
	`password` text NOT NULL,
	`status` int NOT NULL DEFAULT 0
);

-- External employee: 1<<0
-- Youpic employee: 1<<1