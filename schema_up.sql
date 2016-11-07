DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) DEFAULT NULL,
  `title` varchar(250) DEFAULT NULL,
  `message` text,
  `fromUser` int(11) unsigned DEFAULT NULL,
  `user` int(11) unsigned DEFAULT NULL,
  `unread` tinyint(1) NOT NULL DEFAULT '1',
  `creationDate` bigint(20) unsigned DEFAULT NULL,
  `modificationDate` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;