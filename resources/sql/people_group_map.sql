# Dump of table {$dbPrefix}people_group_map
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}people_group_map`;

CREATE TABLE `{$dbPrefix}people_group_map` (
  `pgm_id` int(11) NOT NULL AUTO_INCREMENT,
  `people_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL DEFAULT '3',
  PRIMARY KEY (`pgm_id`),
  UNIQUE KEY `people_id_2` (`people_id`,`group_id`),
  KEY `people_id` (`people_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `{$dbPrefix}people_group_map` WRITE;
/*!40000 ALTER TABLE `{$dbPrefix}people_group_map` DISABLE KEYS */;

INSERT INTO `{$dbPrefix}people_group_map` (`pgm_id`, `people_id`, `group_id`)
VALUES
	(1,1,1),
	(2,1,2),
	(3,1,3),
	(4,1,4),
	(5,1,5),
	(6,2,2),
	(7,2,3),
	(8,2,4),
	(9,2,5);

/*!40000 ALTER TABLE `{$dbPrefix}people_group_map` ENABLE KEYS */;
UNLOCK TABLES;


