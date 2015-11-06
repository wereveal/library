# Dump of table {$dbPrefix}routes_roles_map
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}routes_roles_map`;

CREATE TABLE `{$dbPrefix}routes_roles_map` (
  `rrm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `route_id` int(11) NOT NULL DEFAULT '0',
  `role_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rrm_id`),
  UNIQUE KEY `rrm_key` (`route_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `{$dbPrefix}routes_roles_map_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `{$dbPrefix}routes` (`route_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `{$dbPrefix}routes_roles_map_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `{$dbPrefix}roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
