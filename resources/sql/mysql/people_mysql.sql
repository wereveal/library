# Dump of table {$dbPrefix}people
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}people`;

CREATE TABLE `{$dbPrefix}people` (
  `people_id` int(11) NOT NULL AUTO_INCREMENT,
  `login_id` varchar(60) NOT NULL,
  `real_name` varchar(50) NOT NULL,
  `short_name` varchar(8) NOT NULL DEFAULT '',
  `password` varchar(128) NOT NULL,
  `is_logged_in` tinyint(2) NOT NULL DEFAULT '0',
  `bad_login_count` int(11) NOT NULL DEFAULT '0',
  `bad_login_ts` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  `is_immutable` tinyint(1) NOT NULL DEFAULT '0',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`people_id`),
  UNIQUE KEY `loginid` (`login_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `{$dbPrefix}people` WRITE;
/*!40000 ALTER TABLE `{$dbPrefix}people` DISABLE KEYS */;

INSERT INTO `{$dbPrefix}people` (`people_id`, `login_id`, `real_name`, `short_name`, `password`, `is_logged_in`, `bad_login_count`, `bad_login_ts`, `is_active`, `is_immutable`, `created_on`)
VALUES
	(1,'SuperAdmin','Super Admin','GSA','$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW',1,0,0,1,1,'2012-08-12 02:55:28'),
	(2,'Sysmin','Access to the backend','SYS','',0,0,0,1,0,'2015-09-04 13:15:55');

/*!40000 ALTER TABLE `{$dbPrefix}people` ENABLE KEYS */;
UNLOCK TABLES;


