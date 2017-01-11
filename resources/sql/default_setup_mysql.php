<?php
return [
    "DROP TABLE IF EXISTS `{dbPrefix}nav_ng_map`",
    "DROP TABLE IF EXISTS `{dbPrefix}people_group_map`",
    "DROP TABLE IF EXISTS `{dbPrefix}routes_group_map`",
    "DROP TABLE IF EXISTS `{dbPrefix}constants`",
    "DROP TABLE IF EXISTS `{dbPrefix}groups`",
    "DROP TABLE IF EXISTS `{dbPrefix}page`",
    "DROP TABLE IF EXISTS `{dbPrefix}people`",
    "DROP TABLE IF EXISTS `{dbPrefix}routes`",
    "DROP TABLE IF EXISTS `{dbPrefix}navgroups`",
    "DROP TABLE IF EXISTS `{dbPrefix}navigation`",
    "DROP TABLE IF EXISTS `{dbPrefix}urls`",

"CREATE TABLE `{dbPrefix}nav_ng_map` (
  `ng_id` int(11) NOT NULL,
  `nav_id` int(11) NOT NULL,
  PRIMARY KEY (`nav_id`,`ng_id`)
) ENGINE=InnoDB DEFAULT CHARSET=uft8
",
"CREATE TABLE `{dbPrefix}people_group_map` (
  `pgm_id` int(11) NOT NULL AUTO_INCREMENT,
  `people_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL DEFAULT '3',
  PRIMARY KEY (`pgm_id`),
  UNIQUE KEY `people_id_2` (`people_id`,`group_id`),
  KEY `people_id` (`people_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
",
"CREATE TABLE `{dbPrefix}routes_group_map` (
  `rgm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `route_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rgm_id`),
  UNIQUE KEY `rgm_key` (`route_id`,`group_id`),
  KEY `route_id` (`route_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `{dbPrefix}routes_group_map_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `{dbPrefix}routes` (`route_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `{dbPrefix}routes_group_map_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `{dbPrefix}groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
",
"CREATE TABLE `{dbPrefix}constants` (
  `const_id` int(11) NOT NULL AUTO_INCREMENT,
  `const_name` varchar(64) NOT NULL DEFAULT '',
  `const_value` varchar(64) NOT NULL DEFAULT '',
  `const_immutable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`const_id`),
  UNIQUE KEY `config_key` (`const_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
",
"CREATE TABLE `{dbPrefix}groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(40) NOT NULL,
  `group_description` varchar(128) NOT NULL DEFAULT '',
  `group_auth_level` int(11) NOT NULL DEFAULT '0',
  `group_immutable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `group_name` (`group_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
",
"CREATE TABLE `{dbPrefix}urls` (
  `url_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url_host` varchar(255) NOT NULL DEFAULT 'self',
  `url_text` varchar(255) NOT NULL DEFAULT '',
  `url_scheme` enum('http','https','ftp','gopher','mailto') NOT NULL DEFAULT 'https',
  `url_immutable` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`url_id`),
  UNIQUE KEY `url_text` (`url_text`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
",
"CREATE TABLE `{dbPrefix}routes` (
  `route_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url_id` int(11) unsigned NOT NULL,
  `route_class` varchar(64) NOT NULL,
  `route_method` varchar(64) NOT NULL,
  `route_action` varchar(255) NOT NULL,
  `route_immutable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`route_id`),
  UNIQUE KEY `url_id` (`url_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
",
"CREATE TABLE `{dbPrefix}page` (
  `page_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url_id` int(11) unsigned NOT NULL,
  `page_type` varchar(20) NOT NULL DEFAULT 'text/html',
  `page_title` varchar(100) NOT NULL DEFAULT 'Needs a title',
  `page_description` varchar(150) NOT NULL DEFAULT 'Needs a description',
  `page_base_url` varchar(50) NOT NULL DEFAULT '/',
  `page_lang` varchar(50) NOT NULL DEFAULT 'en',
  `page_charset` varchar(100) NOT NULL DEFAULT 'utf-8',
  `page_immutable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
",
"CREATE TABLE `{dbPrefix}people` (
  `people_id` int(11) NOT NULL AUTO_INCREMENT,
  `login_id` varchar(60) NOT NULL,
  `real_name` varchar(50) NOT NULL,
  `short_name` varchar(8) NOT NULL,
  `password` varchar(128) NOT NULL,
  `description` varchar(250) NOT NULL DEFAULT '',
  `is_logged_in` tinyint(2) NOT NULL DEFAULT '0',
  `bad_login_count` int(11) NOT NULL DEFAULT '0',
  `bad_login_ts` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  `is_immutable` tinyint(1) NOT NULL DEFAULT '0',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`people_id`),
  UNIQUE KEY `loginid` (`login_id`),
  UNIQUE KEY `shortname` (`short_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
",
"CREATE TABLE `{dbPrefix}navgroups` (
  `ng_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ng_name` varchar(128) NOT NULL DEFAULT 'Main',
  `ng_active` tinyint(1) NOT NULL DEFAULT '1',
  `ng_default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ng_id`),
  UNIQUE KEY `ng_name` (`ng_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
",
"CREATE TABLE `{dbPrefix}navigation` (
  `nav_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url_id` int(11) unsigned NOT NULL DEFAULT '0',
  `nav_parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  `nav_name` varchar(128) NOT NULL DEFAULT 'Fred',
  `nav_text` varchar(128) NOT NULL DEFAULT '',
  `nav_description` varchar(255) NOT NULL DEFAULT '',
  `nav_css` varchar(64) NOT NULL DEFAULT 'menu-item',
  `nav_level` int(11) NOT NULL DEFAULT '1',
  `nav_order` int(11) NOT NULL DEFAULT '0',
  `nav_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`nav_id`),
  KEY `url_id` (`url_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
",
"LOCK TABLES `{dbPrefix}routes` WRITE",
"INSERT INTO `{dbPrefix}routes`
    (`route_id`, `url_id`, `route_class`, `route_method`, `route_action`, `route_immutable`)
VALUES
    (1,1,'HomeController','route','',1),
	(2,2,'ManagerController','route','',1),
	(3,3,'LibraryController','route','logout',1),
	(4,4,'LibraryController','route','ConstantsAdmin',1),
	(5,5,'LibraryController','route','GroupsAdmin',1),
	(6,6,'LibraryController','route','PeopleAdmin',1),
	(7,7,'LibraryController','route','UrlsAdmin',1),
	(8,8,'LibraryController','route','RoutesAdmin',1),
	(9,9,'LibraryController','route','NavigationAdmin',1),
	(10,10,'LibraryController','route','PageAdmin',1),
	(11,12,'ManagerController','route','TestsAdmin',1)",
"UNLOCK TABLES",
"LOCK TABLES `{dbPrefix}page` WRITE",
"INSERT INTO `{dbPrefix}page`
    (`page_id`, `url_id`, `page_type`, `page_title`, `page_description`, `page_base_url`, `page_lang`, `page_charset`, `page_immutable`)
VALUES
	(1,1,'text/html','My App','My App','/','en','utf-8',0),
	(2,2,'text/html','Manager','Manages Advanced Configuration for People, Places and Things','/','en','utf-8',1),
	(3,3,'text/html','Manager for Constants','Manages the Constants used in app','/','en','utf-8',1),
	(5,5,'text/html','Manager for Groups','Manages the Groups','/','en','utf-8',1),
	(7,7,'text/html','Manager for Pages','Manages pages head information primarily','/','en','utf-8',1),
	(9,9,'text/html','Manager for People','Manages people','/','en','utf-8',1),
	(11,11,'text/html','Manager for the Navigation tools','Manager for Navigation tools','/','en','utf-8',0),
	(12,12,'text/html','Manager for Routes','Manages the routes','/','en','utf-8',1),
	(14,14,'text/html','Manager Tests','Runs tests for the code.','/','en','utf-8',1),
	(15,15,'text/html','Manager: Please Login','Login page for the manager.','/','en','utf-8',1),
	(17,17,'text/html','Manager for Urls','Manages the Urls','/','en','utf-8',1),
	(27,30,'text/html','Manager: Please Login','Login page for the manager.','/','en','utf-8',0),
",
"UNLOCK TABLES",
"LOCK TABLES `{dbPrefix}people` WRITE",
'INSERT INTO `{dbPrefix}people`
    (`people_id`, `login_id`, `real_name`, `short_name`, `password`, `description`, `is_logged_in`, `bad_login_count`, `bad_login_ts`, `is_active`, `is_immutable`, `created_on`)
VALUES
	(1,"SuperAdmin","Super Admin","GSA","$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW","The all powerful Admin",0,0,0,1,1,"2012-08-12 02:55:28"),
	(2,"Admin","Admin","ADM","$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW","Allowed to admin the backend.",1,0,0,1,1,"2015-09-04 13:15:55")',
"UNLOCK TABLES",
"LOCK TABLES `{dbPrefix}people_group_map` WRITE",
"INSERT INTO `{dbPrefix}people_group_map`
    (`people_id`, `group_id`)
VALUES
	(1,1),
	(1,2),
	(1,3),
	(2,2),
	(2,3)",
"UNLOCK TABLES",
"LOCK TABLES `{dbPrefix}routes_group_map` WRITE",
"INSERT INTO `{dbPrefix}routes_group_map`
    (`route_id`, `group_id`)
VALUES
	(1,1),
	(1,2),
	(2,1),
	(2,2),
	(3,1),
	(3,2),
	(4,1),
	(4,2),
	(4,3),
	(4,4),
	(4,5),
	(5,1),
	(5,2),
	(5,3),
	(5,4),
	(5,5),
	(6,1),
	(6,2),
	(7,1),
	(7,2),
	(8,1),
	(8,2),
	(9,1)",
"UNLOCK TABLES",
"INSERT INTO `{dbPrefix}navgroups` (`ng_id`, `ng_name`, `ng_active`, `ng_default`)
VALUES
	(1,'Main',1,1),
	(2,'SiteMap',1,0),
	(3,'PageLinks',1,0),
	(4,'ManagerLinks',1,0)
",
"INSERT INTO `qcdg_navigation` (`url_id`, `nav_parent_id`, `nav_name`, `nav_text`, `nav_description`, `nav_css`, `nav_level`, `nav_order`, `nav_active`)
VALUES
	(1,1,'Home','Home','Home page.','',1,1,1),
	(2,2,'Advanced Config','Advanced Config','Backend Manager Page','',1,4,1),
	(25,3,'Manager','Manager','Manager','',1,3,1),
	(3,2,'Constants','Constants','Define constants used throughout app.','',2,6,1),
	(5,2,'Groups','Groups','Define Groups used for accessing app.','',2,4,1),
	(7,2,'Pages','Pages','Define Page values.','',2,2,1),
	(9,2,'People','People','Setup people allowed to access app.','',2,5,1),
	(11,2,'Navigation','Navigation','Define Navigation Groups and Items','',2,3,1),
	(12,2,'Routes','Routes','Define routes used for where to go.','',2,1,1),
	(17,2,'Urls','Urls','Define the URLs used in the app','',2,7,1),
	(24,3,'Tests','Tests','Run Tests','',2,6,0),
	(31,26,'Logout','Logout','Logout of app.','',1,4,1)"
];
