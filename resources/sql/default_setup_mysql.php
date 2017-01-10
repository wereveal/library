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

"CREATE TABLE `{dbPrefix}constants` (
  `const_id` int(11) NOT NULL AUTO_INCREMENT,
  `const_name` varchar(64) NOT NULL DEFAULT '',
  `const_value` varchar(64) NOT NULL DEFAULT '',
  `const_immutable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`const_id`),
  UNIQUE KEY `config_key` (`const_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8",

"LOCK TABLES `{dbPrefix}constants` WRITE",

"INSERT INTO `{dbPrefix}constants`
    (`const_name`, `const_value`, `const_immutable`)
VALUES
	('DISPLAY_DATE_FORMAT','m/d/Y',1),
	('EMAIL_DOMAIN','revealitconsulting.com',1),
	('EMAIL_FORM_TO','bill@revealitconsulting.com',1),
	('ERROR_EMAIL_ADDRESS','webmaster@revealitconsulting.com',1),
	('PAGE_TEMPLATE','index.twig',1),
	('TWIG_PREFIX','ritc_',1),
	('LIB_TWIG_PREFIX', 'lib_',1),
	('THEME_NAME','',1),
	('ADMIN_THEME_NAME','',1),
	('CSS_DIR_NAME','css',1),
	('HTML_DIR_NAME','html',1),
	('JS_DIR_NAME','js',1),
	('IMAGE_DIR_NAME','images',1),
	('ADMIN_DIR_NAME','manager',1),
	('ASSETS_DIR_NAME','assets',1),
	('FILES_DIR_NAME','files',1),
	('DISPLAY_PHONE_FORMAT','XXX-XXX-XXXX',1),
	('THEMES_DIR','',1),
	('RIGHTS_HOLDER','Reveal IT Consulting',1),
	('PRIVATE_DIR_NAME','private',1),
	('TMP_DIR_NAME','tmp',1),
	('DEVELOPER_MODE','true',1),
	('SESSION_IDLE_TIME','1800',1)",

"UNLOCK TABLES",

"CREATE TABLE `{dbPrefix}groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(40) NOT NULL,
  `group_description` varchar(128) NOT NULL DEFAULT '',
  `group_auth_level` int(11) NOT NULL DEFAULT '0',
  `group_immutable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `group_name` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8",

"LOCK TABLES `{dbPrefix}groups` WRITE",

"INSERT INTO `{dbPrefix}groups`
    (`group_id`, `group_name`, `group_description`, `group_auth_level`, `group_immutable`)
VALUES
	(1,'SuperAdmin','The group for super administrators. There should be only a couple of these.',10,1),
	(2,'Managers','Most people accessing the manager should be in this group.',9,1),
	(3,'Editor','Editor for the CMS which does not exist in the FtpManager',5,1),
	(4,'Registered','The group for people that should not have access to the manager.',3,1),
	(5,'Anonymous','Not logged in, possibly unregistered',0,1)",

"UNLOCK TABLES",

"CREATE TABLE `{dbPrefix}urls` (
  `url_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url_host` varchar(255) NOT NULL DEFAULT 'self',
  `url_text` varchar(255) NOT NULL DEFAULT '',
  `url_scheme` enum('http','https','ftp','gopher','mailto') NOT NULL DEFAULT 'https',
  `url_immutable` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`url_id`),
  UNIQUE KEY `url_text` (`url_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8",

"LOCK TABLES `{dbPrefix}urls` WRITE",

"INSERT INTO `qcdg_urls` (`url_id`, `url_host`, `url_text`, `url_scheme`, `url_immutable`)
VALUES
	(1,'self','/','http',1),
	(2,'self','/manager/','http',1),
	(3,'self','/manager/logout/','http',1),
	(4,'self','/manager/library/constants/','http',1),
	(5,'self','/manager/library/groups/','http',1),
	(6,'self','/manager/library/people/','http',1),
	(7,'self','/manager/library/urls/','http',1),
	(8,'self','/manager/library/routes/','http',1),
	(9,'self','/manager/library/navigation/','http',1),
	(10,'self','/manager/library/pages/','http',1),
	(11,'self','/manager/login/','http',1),
	(12,'self','/manager/library/tests/','http',1)",

"CREATE TABLE `{dbPrefix}routes` (
  `route_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url_id` int(11) unsigned NOT NULL,
  `route_class` varchar(64) NOT NULL,
  `route_method` varchar(64) NOT NULL,
  `route_action` varchar(255) NOT NULL,
  `route_immutable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`route_id`),
  UNIQUE KEY `url_id` (`url_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8",

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8",

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8",

"LOCK TABLES `{dbPrefix}people` WRITE",

'INSERT INTO `{dbPrefix}people`
    (`people_id`, `login_id`, `real_name`, `short_name`, `password`, `description`, `is_logged_in`, `bad_login_count`, `bad_login_ts`, `is_active`, `is_immutable`, `created_on`)
VALUES
	(1,"SuperAdmin","Super Admin","GSA","$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW","The all powerful Admin",0,0,0,1,1,"2012-08-12 02:55:28"),
	(2,"Admin","Admin","ADM","$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW","Allowed to admin the backend.",1,0,0,1,1,"2015-09-04 13:15:55")',

"UNLOCK TABLES",

"CREATE TABLE `{dbPrefix}people_group_map` (
  `pgm_id` int(11) NOT NULL AUTO_INCREMENT,
  `people_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL DEFAULT '3',
  PRIMARY KEY (`pgm_id`),
  UNIQUE KEY `people_id_2` (`people_id`,`group_id`),
  KEY `people_id` (`people_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8",

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

"CREATE TABLE `{dbPrefix}routes_group_map` (
  `rgm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `route_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rgm_id`),
  UNIQUE KEY `rgm_key` (`route_id`,`group_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `{dbPrefix}routes_group_map_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `{dbPrefix}routes` (`route_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `{dbPrefix}routes_group_map_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `{dbPrefix}groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8",

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

"UNLOCK TABLES"
];
