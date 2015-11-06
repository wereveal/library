# Dump of table {$dbPrefix}group_role_map
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}group_role_map`;

CREATE TABLE `{$dbPrefix}group_role_map` (
  `grm_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`grm_id`),
  KEY `group_id` (`group_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `{$dbPrefix}group_role_map_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `{$dbPrefix}groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `{$dbPrefix}group_role_map_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `{$dbPrefix}roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `{$dbPrefix}group_role_map` WRITE;
/*!40000 ALTER TABLE `{$dbPrefix}group_role_map` DISABLE KEYS */;

INSERT INTO `{$dbPrefix}group_role_map` (`grm_id`, `group_id`, `role_id`)
VALUES
	(1,1,1),
	(2,2,2),
	(3,3,3),
	(4,4,4),
	(5,5,5);

/*!40000 ALTER TABLE `{$dbPrefix}group_role_map` ENABLE KEYS */;
UNLOCK TABLES;
