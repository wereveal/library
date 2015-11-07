# Dump of table {$dbPrefix}routes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}routes`;

CREATE TABLE `{$dbPrefix}routes` (
  `route_id` int(11) NOT NULL AUTO_INCREMENT,
  `route_path` varchar(128) NOT NULL,
  `route_class` varchar(64) NOT NULL,
  `route_method` varchar(64) NOT NULL,
  `route_action` varchar(255) NOT NULL,
  `route_can_edit` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`route_id`),
  UNIQUE KEY `route_path` (`route_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `{$dbPrefix}routes` WRITE;
/*!40000 ALTER TABLE `{$dbPrefix}routes` DISABLE KEYS */;

INSERT INTO `{$dbPrefix}routes` (`route_id`, `route_path`, `route_class`, `route_method`, `route_action`, `route_can_edit`)
VALUES
	(1,'/manager/','ManagerController','render','',1),
	(2,'/manager/login/','ManagerController','render','verifyLogin',1),
	(3,'/manager/routes/','ManagerController','renderRouterAdmin','',1),
	(4,'/manager/constants/','ManagerController','renderConstantsAdmin','',1),
	(5,'/manager/people/','ManagerController','renderPeopleAdmin','',1),
	(6,'/manager/people/save/','ManagerController','renderPeopleAdmin','save',1),
	(7,'/manager/people/modify/','ManagerController','renderPeopleAdmin','modify',1),
	(8,'/manager/people/delete/','ManagerController','renderPeopleAdmin','delete',1),
	(9,'/manager/groups/','ManagerController','renderGroupsAdmin','',1),
	(10,'/manager/groups/save/','ManagerController','renderGroupsAdmin','save',1),
	(11,'/manager/groups/update/','ManagerController','renderGroupsAdmin','update',1),
	(12,'/manager/groups/delete/','ManagerController','renderGroupsAdmin','delete',1),
	(13,'/manager/routes/list/','ManagerController','renderRouterAdmin','',1),
	(14,'/manager/constants/list/','ManagerController','renderConstantsAdmin','list',1),
	(15,'/manager/people/list/','ManagerController','renderPeopleAdmin','list',1),
	(16,'/manager/groups/list/','ManagerController','renderGroupsAdmin','',1),
	(17,'/manager/routes/save/','ManagerController','renderRouterAdmin','save',1),
	(18,'/manager/routes/update/','ManagerController','renderRouterAdmin','update',1),
	(19,'/manager/routes/delete/','ManagerController','renderRouterAdmin','delete',1),
	(20,'/manager/constants/save/','ManagerController','renderConstantsAdmin','save',1),
	(21,'/manager/constants/modify/','ManagerController','renderConstantsAdmin','modify',1),
	(22,'/manager/constants/delete/','ManagerController','renderConstantsAdmin','delete',1);

/*!40000 ALTER TABLE `{$dbPrefix}routes` ENABLE KEYS */;
UNLOCK TABLES;


