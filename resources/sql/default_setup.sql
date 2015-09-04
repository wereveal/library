# ************************************************************
# Default Setup of the Framework
# Replace {$dbPrefix} with appropriate prefix
# Generation Time: 2015-09-04 18:41:03 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table {$dbPrefix}constants
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}constants`;

CREATE TABLE `{$dbPrefix}constants` (
  `const_id` int(11) NOT NULL AUTO_INCREMENT,
  `const_name` varchar(64) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `const_value` varchar(64) CHARACTER SET latin1 NOT NULL DEFAULT '',
  PRIMARY KEY (`const_id`),
  UNIQUE KEY `config_key` (`const_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `{$dbPrefix}constants` WRITE;
/*!40000 ALTER TABLE `{$dbPrefix}constants` DISABLE KEYS */;

INSERT INTO `{$dbPrefix}constants` (`const_id`, `const_name`, `const_value`)
VALUES
	(1,'DISPLAY_DATE_FORMAT','m/d/Y'),
	(2,'EMAIL_DOMAIN','revealitconsulting.com'),
	(3,'EMAIL_FORM_TO','bill@revealitconsulting.com'),
	(4,'ERROR_EMAIL_ADDRESS','webmaster@revealitconsulting.com'),
	(5,'PAGE_TEMPLATE','index.twig'),
	(6,'THEME_NAME',''),
	(7,'ADMIN_THEME_NAME',''),
	(8,'CSS_DIR_NAME','css'),
	(9,'HTML_DIR_NAME','html'),
	(10,'JS_DIR_NAME','js'),
	(11,'IMAGE_DIR_NAME','images'),
	(12,'ADMIN_DIR_NAME','manager'),
	(13,'ASSETS_DIR_NAME','assets'),
	(14,'FILES_DIR_NAME','files'),
	(15,'DISPLAY_PHONE_FORMAT','XXX-XXX-XXXX'),
	(16,'THEMES_DIR',''),
	(17,'RIGHTS_HOLDER','Reveal IT Consulting'),
	(18,'PRIVATE_DIR_NAME','private'),
	(19,'TMP_DIR_NAME','tmp'),
	(20,'DEVELOPER_MODE','true');

/*!40000 ALTER TABLE `{$dbPrefix}constants` ENABLE KEYS */;
UNLOCK TABLES;


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


# Dump of table {$dbPrefix}people
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}people`;

CREATE TABLE `{$dbPrefix}people` (
  `people_id` int(11) NOT NULL AUTO_INCREMENT,
  `login_id` varchar(60) NOT NULL,
  `real_name` varchar(50) NOT NULL,
  `short_name` varchar(8) NOT NULL DEFAULT '',
  `password` varchar(128) NOT NULL,
  `is_logged_in` tinyint(2) NOT NULL DEFAULT '0',
  `bad_login_count` int(11) NOT NULL DEFAULT '0',
  `bad_login_ts` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  `is_immutable` tinyint(1) NOT NULL DEFAULT '0',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`people_id`),
  UNIQUE KEY `loginid` (`login_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `{$dbPrefix}people` WRITE;
/*!40000 ALTER TABLE `{$dbPrefix}people` DISABLE KEYS */;

INSERT INTO `{$dbPrefix}people` (`people_id`, `login_id`, `real_name`, `short_name`, `password`, `is_logged_in`, `bad_login_count`, `bad_login_ts`, `is_active`, `is_immutable`, `created_on`)
VALUES
	(1,'SuperAdmin','Super Admin','GSA','$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW',1,0,0,1,1,'2012-08-12 02:55:28'),
	(2,'Sysmin','Access to the backend','SYS','',0,0,0,1,0,'2015-09-04 13:15:55');

/*!40000 ALTER TABLE `{$dbPrefix}people` ENABLE KEYS */;
UNLOCK TABLES;


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
	(10,'/manager/roles/','ManagerController','renderRolesAdmin','',1),
	(11,'/manager/groups/save/','ManagerController','renderGroupsAdmin','save',1),
	(12,'/manager/groups/update/','ManagerController','renderGroupsAdmin','update',1),
	(13,'/manager/groups/delete/','ManagerController','renderGroupsAdmin','delete',1),
	(14,'/manager/roles/save/','ManagerController','renderRolesAdmin','save',1),
	(15,'/manager/roles/update/','ManagerController','renderRolesAdmin','update',1),
	(16,'/manager/roles/delete/','ManagerController','renderRolesAdmin','delete',1),
	(17,'/manager/routes/list/','ManagerController','renderRouterAdmin','',1),
	(18,'/manager/constants/list/','ManagerController','renderConstantsAdmin','list',1),
	(19,'/manager/people/list/','ManagerController','renderPeopleAdmin','list',1),
	(20,'/manager/groups/list/','ManagerController','renderGroupsAdmin','',1),
	(21,'/manager/roles/list/','ManagerController','renderRolesAdmin','list',1),
	(22,'/manager/routes/save/','ManagerController','renderRouterAdmin','save',1),
	(23,'/manager/routes/update/','ManagerController','renderRouterAdmin','update',1),
	(24,'/manager/routes/delete/','ManagerController','renderRouterAdmin','delete',1),
	(25,'/manager/constants/save/','ManagerController','renderConstantsAdmin','save',1),
	(26,'/manager/constants/modify/','ManagerController','renderConstantsAdmin','modify',1),
	(27,'/manager/constants/delete/','ManagerController','renderConstantsAdmin','delete',1);

/*!40000 ALTER TABLE `{$dbPrefix}routes` ENABLE KEYS */;
UNLOCK TABLES;


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




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
