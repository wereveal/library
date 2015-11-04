# Dump of table {$dbPrefix}groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}groups`;

CREATE TABLE `{$dbPrefix}groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(40) NOT NULL,
  `group_description` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `group_name` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `{$dbPrefix}groups` WRITE;
/*!40000 ALTER TABLE `{$dbPrefix}groups` DISABLE KEYS */;

INSERT INTO `{$dbPrefix}groups` (`group_id`, `group_name`, `group_description`)
VALUES
	(1,'SuperAdmin','The group for super administrators. There should be only a couple of these.'),
	(2,'Managers','Most people accessing the manager should be in this group.'),
	(3,'Editor','Editor for the CMS'),
	(4,'Registered','The group for people that shouldn\'t have access to the manager.'),
	(5,'Anonymous','Not logged in, possibly unregistered');

/*!40000 ALTER TABLE `{$dbPrefix}groups` ENABLE KEYS */;
UNLOCK TABLES;


