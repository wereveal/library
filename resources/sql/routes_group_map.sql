# Dump of table {$dbPrefix}routes_group_map
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}routes_group_map`;

CREATE TABLE `{$dbPrefix}routes_group_map` (
  `rgm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `route_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rgm_id`),
  UNIQUE KEY `rgm_key` (`route_id`,`group_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `{$dbPrefix}routes_group_map_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `{$dbPrefix}groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `{$dbPrefix}routes_group_map_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `{$dbPrefix}routes` (`route_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `{$dbPrefix}routes_group_map` WRITE;
/*!40000 ALTER TABLE `{$dbPrefix}routes_group_map` DISABLE KEYS */;

INSERT INTO `{$dbPrefix}routes_group_map` (`rgm_id`, `route_id`, `group_id`)
VALUES
	(1,1,1),
	(2,1,2),
	(3,2,1),
	(4,2,2),
	(5,3,1),
	(6,3,2),
	(7,4,1),
	(8,4,2),
	(9,5,1),
	(10,5,2),
	(11,6,1),
	(12,6,2),
	(13,7,1),
	(14,7,2),
	(15,8,1),
	(16,8,2),
	(17,9,1),
	(18,9,2),
	(19,10,1),
	(20,10,2),
	(21,11,1),
	(22,11,2),
	(23,12,1),
	(24,12,2),
	(25,13,1),
	(26,13,2),
	(27,14,1),
	(28,14,2),
	(29,15,1),
	(30,15,2),
	(31,16,1),
	(32,16,2),
	(33,17,1),
	(34,17,2),
	(35,18,1),
	(36,18,2),
	(37,19,1),
	(38,19,2),
	(39,20,1),
	(40,20,2),
	(41,21,1),
	(42,21,2),
	(43,22,1),
	(44,22,2),
	(45,23,1),
	(46,23,2),
	(47,24,1),
	(48,24,2),
	(49,25,1),
	(50,25,2),
	(51,26,1),
	(52,26,2),
	(53,27,1),
	(54,27,2);

/*!40000 ALTER TABLE `{$dbPrefix}routes_group_map` ENABLE KEYS */;
UNLOCK TABLES;

