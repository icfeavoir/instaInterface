CREATE DATABASE IF NOT EXISTS instagram;
use instagram;

CREATE TABLE IF NOT EXISTS `User` (
	`ID` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`email` text NOT NULL,
	`password` text NOT NULL,
	`rights` int NOT NULL DEFAULT 0
);

-- External employee: 1<<0
-- Youpic employee: 1<<1