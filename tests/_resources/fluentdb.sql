CREATE DATABASE IF NOT EXISTS fluentdb;
USE fluentdb;

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `article`;
CREATE TABLE `article` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `published_at` DATETIME NOT NULL DEFAULT 0,
  `title` VARCHAR(100) NOT NULL DEFAULT '',
  `content` TEXT NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_article_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `article` (`id`, `user_id`, `published_at`, `title`, `content`) VALUES
(1, 1, '2011-12-10 12:10:00', 'article 1', 'content 1'),
(2, 2, '2011-12-20 16:20:00', 'article 2', 'content 2'),
(3, 1, '2012-01-04 22:00:00', 'article 3', 'content 3'),
(4, 4, '2018-07-07 15:15:07', 'artïcle 4', 'content 4'),
(5, 3, '2018-10-01 01:10:01', 'article 5', 'content 5'),
(6, 3, '2019-01-21 07:00:00', 'სარედაქციო 6', '함유량 6');

DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` INT(10) UNSIGNED NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `content` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_comment_article_id` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  CONSTRAINT `fk_comment_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `comment` (`id`, `article_id`, `user_id`, `content`) VALUES
(1, 1, 1, 'comment 1.1'),
(2, 1, 2, 'comment 1.2'),
(3, 2, 1, 'comment 2.1'),
(4, 5, 4, 'cömment 5.4'),
(5, 6, 2, 'ਟਿੱਪਣੀ 6.2');

DROP TABLE IF EXISTS `country`;
CREATE TABLE `country` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) NOT NULL,
  `details` JSON NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `country` (`id`, `name`, `details`) VALUES
(1, 'Slovakia', '{"name": "Slovensko", "pop": 5456300, "gdp": 90.75}'),
(2, 'Canada', '{"name": "Canada", "pop": 37198400, "gdp": 1592.37}'),
(3, 'Germany', '{"name": "Deutschland", "pop": 82385700, "gdp": 3486.12}');

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_id` INT(10) UNSIGNED NOT NULL,
  `type` ENUM('admin','author') NOT NULL,
  `name` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `country_id` (`country_id`),
  CONSTRAINT `fk_user_country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `user` (`id`, `country_id`, `type`, `name`) VALUES
(1, 1, 'admin', 'Marek'),
(2, 1, 'author', 'Robert'),
(3, 2, 'admin', 'Chris'),
(4, 2, 'author', 'Kevin');

-- 2018-10-01 07:42:17
