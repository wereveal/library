# Dump of table {$dbPrefix}page
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}page`;

CREATE TABLE `{$dbPrefix}page` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_url` varchar(255) NOT NULL DEFAULT '/',
  `page_type` varchar(20) NOT NULL DEFAULT 'text/html',
  `page_title` varchar(100) NOT NULL DEFAULT '',
  `page_description` varchar(150) NOT NULL DEFAULT '',
  `page_base_url` varchar(50) NOT NULL DEFAULT '/',
  `page_lang` varchar(50) NOT NULL DEFAULT 'en',
  `page_charset` varchar(100) NOT NULL DEFAULT 'utf-8',
  `page_immutable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `pages_url` (`page_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `{$dbPrefix}page` WRITE;
/*!40000 ALTER TABLE `{$dbPrefix}page` DISABLE KEYS */;

INSERT INTO `{$dbPrefix}page`
    (`page_url`, `page_type`, `page_title`, `page_description`, `page_base_url`, `page_lang`, `page_charset`, `page_immutable`)
VALUES
    ('/manager/','text/html','Manager','Manages People, Places and Things','/','en','utf-8',1),
    ('/manager/constants/','text/html','Manager for Constants','Manages the Constants used in app','/','en','utf-8',1),
    ('/manager/constants/verify/','text/html','Manager for Constants','Manages the Constants, verifies that the constant should be deleted.','/','en','utf-8',1),
    ('/manager/groups/','text/html','Manager for Groups','Manages the Groups','/','en','utf-8',1),
    ('/manager/groups/verify/','text/html','Manager for Groups','Manages the groups, this page verifies deletion.','/','en','utf-8',1),
    ('/manager/pages/','text/html','Manager for Pages','Manages pages head information primarily','/','en','utf-8',1),
    ('/manager/pages/verify/','text/html','Manager for Pages','Manages pages, verifies if record should be deleted','/','en','utf-8',1),
    ('/manager/people/','text/html','Manager for People','Manages people','/','en','utf-8',1),
    ('/manager/people/new/','text/html','Manager for People','Manages people, form to add a new person.','/','en','utf-8',1),
    ('/manager/people/modify/','text/html','Manager for People','Manages people, for modifying a person','/','en','utf-8',1),
    ('/manager/people/verify/','text/html','Manager for People','Manages people, verifies a person should be deleted.','/','en','utf-8',1),
    ('/manager/people/delete/','text/html','Manager for People','Manages people','/','en','utf-8',1),
    ('/manager/roles/','text/html','Manager for Roles','Manages the roles','/','en','utf-8',1),
    ('/manager/roles/verify/','text/html','Manager for Roles','Manages the roles, verifies a role should be deleted.','/','en','utf-8',1),
    ('/manager/routes/','text/html','Manager for Routes','Manages the routes','/','en','utf-8',1),
    ('/manager/routes/verify/','text/html','Manager for Routes','Manages the routes, verifies route should be deleted.','/','en','utf-8',1),
    ('/manager/tests/','text/html','Manager Tests','Runs tests for the code.','/','en','utf-8',1),
    ('/manager/login/','text/html','Manager: Please Login','Login page for the manager.','/','en','utf-8',1),
    ('/manager/logout/','text/html','Manager: Please Login','Login page for the manager.','/','en','utf-8',0);

/*!40000 ALTER TABLE `{$dbPrefix}page` ENABLE KEYS */;
UNLOCK TABLES;

