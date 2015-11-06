# Dump of table {$dbPrefix}roles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}roles`;

CREATE TABLE `{$dbPrefix}roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(20) NOT NULL,
  `role_description` text NOT NULL,
  `role_level` int(11) NOT NULL DEFAULT '4',
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `rolename` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `{$dbPrefix}roles` WRITE;
/*!40000 ALTER TABLE `{$dbPrefix}roles` DISABLE KEYS */;

INSERT INTO `{$dbPrefix}roles` (`role_id`, `role_name`, `role_description`, `role_level`)
VALUES
	(1,'superadmin','Has Access to Everything.',1),
	(2,'admin','Has complete access to the administration area.',2),
	(3,'editor','Can modify the CMS content.',3),
	(4,'registered','Registered User',4),
	(5,'anonymous','Anonymous User',5);

/*!40000 ALTER TABLE `{$dbPrefix}roles` ENABLE KEYS */;
UNLOCK TABLES;


