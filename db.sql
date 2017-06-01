-- Adminer 4.3.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `todos`;
CREATE TABLE `todos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `hash` binary(16) NOT NULL,
  `is_complete` tinyint(3) unsigned NOT NULL,
  `text` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_hash` (`user_id`,`hash`),
  CONSTRAINT `todos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(32) NOT NULL,
  `password` binary(16) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `login`, `password`) VALUES
(1,	'admin',	UNHEX('698D51A19D8A121CE581499D7B701668')),
(2,	'bobo',	UNHEX('698D51A19D8A121CE581499D7B701668')),
(3,	'max',	UNHEX('698D51A19D8A121CE581499D7B701668')),
(4,	'bill',	UNHEX('698D51A19D8A121CE581499D7B701668')),
(5,	'vladimir',	UNHEX('698D51A19D8A121CE581499D7B701668')),
(6,	'trump',	UNHEX('698D51A19D8A121CE581499D7B701668'));

-- 2017-06-01 17:49:02