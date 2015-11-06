# Dump of table {$dbPrefix}groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}groups`;

CREATE TABLE `{$dbPrefix}groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(40) NOT NULL,
  `group_description` varchar(128) NOT NULL DEFAULT '',
  `group_auth_level` int(11) NOT NULL DEFALUT '0',
  `group_immutable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `group_name` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `{$dbPrefix}groups` WRITE;
/*!40000 ALTER TABLE `{$dbPrefix}groups` DISABLE KEYS */;

INSERT INTO `{$dbPrefix}groups`
    (`group_id`, `group_name`, `group_description`, `group_auth_level`, `group_immutable`)
VALUES
	(1,'SuperAdmin','The group for super administrators. There should be only a couple of these.',10,1),
	(2,'Managers','Most people accessing the manager should be in this group.',9,1),
	(3,'Editor','Editor for the CMS which doesn&#039;t exist in the FtpManager',5,1),
	(4,'Registered','The group for people that should&#039;t have access to the manager.',3,1),
	(5,'Anonymous','Not logged in, possibly unregistered',0,1);

/*!40000 ALTER TABLE `{$dbPrefix}groups` ENABLE KEYS */;
UNLOCK TABLES;


