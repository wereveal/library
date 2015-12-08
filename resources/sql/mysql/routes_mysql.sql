# Dump of table {$dbPrefix}routes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}routes`;

CREATE TABLE `{$dbPrefix}routes` (
  `route_id` int(11) NOT NULL AUTO_INCREMENT,
  `route_path` varchar(128) NOT NULL,
  `route_namespace` varchar(255) NOT NULL,
  `route_class` varchar(64) NOT NULL,
  `route_method` varchar(64) NOT NULL,
  `route_action` varchar(255) NOT NULL,
  `route_can_edit` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`route_id`),
  UNIQUE KEY `route_path` (`route_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `{$dbPrefix}routes` WRITE;
/*!40000 ALTER TABLE `{$dbPrefix}routes` DISABLE KEYS */;

INSERT INTO `{$dbPrefix}routes`
    (`route_id`, `route_path`, `route_namespace`, `route_class`, `route_method`, `route_action`, `route_immutable`)
VALUES
	(1,'/manager/','Ritc\Library\Controllers','ManagerController','render','',1),
	(2,'/manager/constants/','Ritc\Library\Controllers','ManagerController','renderConstantsAdmin','',1),
	(3,'/manager/groups/','Ritc\Library\Controllers','ManagerController','renderGroupsAdmin','',1),
	(4,'/manager/login/','Ritc\Library\Controllers','ManagerController','render','verifyLogin',1),
	(5,'/manager/logout/','Ritc\Library\Controllers','ManagerController','render','logout',1),
	(6,'/manager/pages/','Ritc\Library\Controllers','ManagerController','renderPageAdmin','',1),
	(7,'/manager/people/','Ritc\Library\Controllers','ManagerController','renderPeopleAdmin','',1),
	(8,'/manager/routes/','Ritc\Library\Controllers','ManagerController','renderRoutesAdmin','',1),
	(9,'/manager/tests/','Ritc\Library\Controllers','ManagerController','renderTestsAdmin','',1);

/*!40000 ALTER TABLE `{$dbPrefix}routes` ENABLE KEYS */;
UNLOCK TABLES;


