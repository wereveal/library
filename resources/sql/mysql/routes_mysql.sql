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
	(1,'/manager/','GuideManagerController','render','',1),
	(2,'/manager/login/','GuideManagerController','render','verifyLogin',1),
	(3,'/manager/routes/','GuideManagerController','renderRouterAdmin','',1),
	(4,'/manager/constants/','GuideManagerController','renderConstantsAdmin','',1),
	(5,'/manager/people/','GuideManagerController','renderPeopleAdmin','',1),
	(6,'/manager/people/save/','GuideManagerController','renderPeopleAdmin','save',1),
	(7,'/manager/people/modify/','GuideManagerController','renderPeopleAdmin','modify',1),
	(8,'/manager/people/delete/','GuideManagerController','renderPeopleAdmin','delete',1),
	(9,'/manager/groups/','GuideManagerController','renderGroupsAdmin','',1),
	(10,'/manager/groups/save/','GuideManagerController','renderGroupsAdmin','save',1),
	(11,'/manager/groups/update/','GuideManagerController','renderGroupsAdmin','update',1),
	(12,'/manager/groups/delete/','GuideManagerController','renderGroupsAdmin','delete',1),
	(13,'/manager/routes/list/','GuideManagerController','renderRouterAdmin','',1),
	(14,'/manager/constants/list/','GuideManagerController','renderConstantsAdmin','list',1),
	(15,'/manager/people/list/','GuideManagerController','renderPeopleAdmin','list',1),
	(16,'/manager/groups/list/','GuideManagerController','renderGroupsAdmin','',1),
	(17,'/manager/routes/save/','GuideManagerController','renderRouterAdmin','save',1),
	(18,'/manager/routes/update/','GuideManagerController','renderRouterAdmin','update',1),
	(19,'/manager/routes/delete/','GuideManagerController','renderRouterAdmin','delete',1),
	(20,'/manager/constants/save/','GuideManagerController','renderConstantsAdmin','save',1),
	(21,'/manager/constants/modify/','GuideManagerController','renderConstantsAdmin','modify',1),
	(22,'/manager/constants/delete/','GuideManagerController','renderConstantsAdmin','delete',1);

/*!40000 ALTER TABLE `{$dbPrefix}routes` ENABLE KEYS */;
UNLOCK TABLES;


