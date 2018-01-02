<?php
return [
"SET FOREIGN_KEY_CHECKS = 0",
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
"DROP TABLE IF EXISTS `{dbPrefix}twig_templates`",
"DROP TABLE IF EXISTS `{dbPrefix}twig_dirs`",
"DROP TABLE IF EXISTS `{dbPrefix}twig_prefix`",
"SET FOREIGN_KEY_CHECKS = 1",


"CREATE TABLE `{dbPrefix}constants` (
  `const_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `const_name` varchar(64) NOT NULL DEFAULT '',
  `const_value` varchar(64) NOT NULL DEFAULT '',
  `const_immutable` varchar(10) NOT NULL DEFAULT 'false',
  PRIMARY KEY (`const_id`),
  UNIQUE KEY `config_key` (`const_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",

"CREATE TABLE `{dbPrefix}groups` (
  `group_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(40) NOT NULL,
  `group_description` varchar(128) NOT NULL DEFAULT '',
  `group_auth_level` int(11) NOT NULL DEFAULT '0',
  `group_immutable` varchar(10) NOT NULL DEFAULT 'false',
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `group_name` (`group_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",

"CREATE TABLE `{dbPrefix}urls` (
  `url_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url_host` varchar(150) NOT NULL DEFAULT 'self',
  `url_text` varchar(150) NOT NULL DEFAULT '',
  `url_scheme` enum('http','https','ftp','gopher','mailto', 'file') NOT NULL DEFAULT 'https',
  `url_immutable` varchar(10) NOT NULL DEFAULT 'false',
  PRIMARY KEY (`url_id`),
  UNIQUE KEY `urls_url` (`url_scheme`,`url_host`,`url_text`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",

"CREATE TABLE `{dbPrefix}routes` (
  `route_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url_id` int(11) unsigned NOT NULL,
  `route_class` varchar(64) NOT NULL,
  `route_method` varchar(64) NOT NULL,
  `route_action` varchar(100) NOT NULL,
  `route_immutable` varchar(10) NOT NULL DEFAULT 'false',
  PRIMARY KEY (`route_id`),
  UNIQUE KEY `url_id` (`url_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",

"CREATE TABLE `{dbPrefix}page` (
  `page_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url_id` int(11) unsigned NOT NULL,
  `ng_id` int(11) unsigned NOT NULL,
  `tpl_id` int(11) unsigned NOT NULL,
  `page_type` varchar(20) NOT NULL DEFAULT 'text/html',
  `page_title` varchar(100) NOT NULL DEFAULT 'Needs a title',
  `page_description` varchar(150) NOT NULL DEFAULT 'Needs a description',
  `page_base_url` varchar(50) NOT NULL DEFAULT '/',
  `page_lang` varchar(50) NOT NULL DEFAULT 'en',
  `page_charset` varchar(100) NOT NULL DEFAULT 'utf-8',
  `page_immutable` varchar(10) NOT NULL DEFAULT 'false',
  PRIMARY KEY (`page_id`),
  KEY (`url_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",

"CREATE TABLE `{dbPrefix}people` (
  `people_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `login_id` varchar(60) NOT NULL,
  `real_name` varchar(50) NOT NULL,
  `short_name` varchar(8) NOT NULL,
  `password` varchar(128) NOT NULL,
  `description` varchar(250) NOT NULL DEFAULT '',
  `is_logged_in` varchar(10) NOT NULL DEFAULT 'false',
  `bad_login_count` int(11) NOT NULL DEFAULT '0',
  `bad_login_ts` int(11) NOT NULL DEFAULT '0',
  `is_active` varchar(10) NOT NULL DEFAULT 'true',
  `is_immutable` varchar(10) NOT NULL DEFAULT 'false',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`people_id`),
  UNIQUE KEY `loginid` (`login_id`),
  UNIQUE KEY `shortname` (`short_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",

"CREATE TABLE `{dbPrefix}navgroups` (
  `ng_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ng_name` varchar(128) NOT NULL DEFAULT 'Main',
  `ng_active` varchar(10) NOT NULL DEFAULT 'true',
  `ng_default` varchar(10) NOT NULL DEFAULT 'false',
  `ng_immutable` varchar(10) NOT NULL DEFAULT 'false',
  PRIMARY KEY (`ng_id`),
  UNIQUE KEY `ng_name` (`ng_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",

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
  `nav_active` varchar(10) NOT NULL DEFAULT 'true',
  `nav_immutable` varchar(10) NOT NULL DEFAULT 'false',
  PRIMARY KEY (`nav_id`),
  KEY `url_id` (`url_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",

"CREATE TABLE `{dbPrefix}nav_ng_map` (
  `nnm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ng_id` int(11) unsigned NOT NULL,
  `nav_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`nnm_id`),
  UNIQUE KEY `ng_id_2` (`ng_id`,`nav_id`),
  KEY `ng_id` (`ng_id`),
  KEY `nav_id` (`nav_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

"CREATE TABLE `{dbPrefix}people_group_map` (
  `pgm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `people_id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned NOT NULL DEFAULT '3',
  PRIMARY KEY (`pgm_id`),
  UNIQUE KEY `people_id_2` (`people_id`,`group_id`),
  KEY `people_id` (`people_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",

"CREATE TABLE `{dbPrefix}routes_group_map` (
  `rgm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `route_id` int(11) unsigned NOT NULL DEFAULT '0',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rgm_id`),
  UNIQUE KEY `rgm_key` (`route_id`,`group_id`),
  KEY `route_id` (`route_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",

"CREATE TABLE `{dbPrefix}twig_prefix` (
  `tp_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tp_prefix` varchar(32) NOT NULL DEFAULT 'site_',
  `tp_path` varchar(150) NOT NULL DEFAULT '/src/templates' COMMENT 'Does not include the BASE_PATH of the site',
  `tp_active` int(2) NOT NULL DEFAULT '1',
  `tp_default` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tp_id`),
  UNIQUE KEY `tp_prefix` (`tp_prefix`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",

"CREATE TABLE `{dbPrefix}twig_dirs` (
  `td_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tp_id` int(11) unsigned NOT NULL,
  `td_name` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`td_id`),
  UNIQUE KEY `tp_id_td_name` (`tp_id`,`td_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",

"CREATE TABLE `{dbPrefix}twig_templates` (
  `tpl_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `td_id` int(11) unsigned NOT NULL,
  `tpl_name` varchar(128) NOT NULL DEFAULT '',
  `tpl_immutable` varchar(10) NOT NULL DEFAULT 'false',
  PRIMARY KEY (`tpl_id`),
  UNIQUE KEY `td_id_tpl_name` (`td_id`,`tpl_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",

"ALTER TABLE `{dbPrefix}routes` ADD CONSTRAINT `{dbPrefix}routes_ibfk_1` FOREIGN KEY (`url_id`) REFERENCES `{dbPrefix}urls` (`url_id`) ON DELETE CASCADE ON UPDATE CASCADE",
"ALTER TABLE `{dbPrefix}page` ADD CONSTRAINT `{dbPrefix}page_ibfk_1` FOREIGN KEY (`url_id`) REFERENCES `{dbPrefix}urls` (`url_id`) ON DELETE CASCADE ON UPDATE CASCADE",
"ALTER TABLE `{dbPrefix}page` ADD CONSTRAINT `{dbPrefix}page_ibfk_2` FOREIGN KEY (`tpl_id`) REFERENCES `{dbPrefix}twig_templates` (`tpl_id`) ON DELETE CASCADE ON UPDATE CASCADE",
"ALTER TABLE `{dbPrefix}navigation` ADD CONSTRAINT `{dbPrefix}navigation_ibfk_1` FOREIGN KEY (`url_id`) REFERENCES `{dbPrefix}urls` (`url_id`) ON DELETE CASCADE ON UPDATE CASCADE",
"ALTER TABLE `{dbPrefix}nav_ng_map` ADD CONSTRAINT `{dbPrefix}nnm_ibfk_1` FOREIGN KEY (`ng_id`) REFERENCES `{dbPrefix}navgroups` (`ng_id`) ON DELETE CASCADE ON UPDATE CASCADE",
"ALTER TABLE `{dbPrefix}nav_ng_map` ADD CONSTRAINT `{dbPrefix}nnm_ibfk_2` FOREIGN KEY (`nav_id`) REFERENCES `{dbPrefix}navigation` (`nav_id`) ON DELETE CASCADE ON UPDATE CASCADE",
"ALTER TABLE `{dbPrefix}people_group_map` ADD CONSTRAINT `{dbPrefix}pgm_ibfk_1` FOREIGN KEY (`people_id`) REFERENCES `{dbPrefix}people` (`people_id`) ON DELETE CASCADE ON UPDATE CASCADE",
"ALTER TABLE `{dbPrefix}people_group_map` ADD CONSTRAINT `{dbPrefix}pgm_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `{dbPrefix}groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE",
"ALTER TABLE `{dbPrefix}routes_group_map` ADD CONSTRAINT `{dbPrefix}rgm_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `{dbPrefix}routes` (`route_id`) ON DELETE CASCADE ON UPDATE CASCADE",
"ALTER TABLE `{dbPrefix}routes_group_map` ADD CONSTRAINT `{dbPrefix}rgm_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `{dbPrefix}groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE",
"ALTER TABLE `{dbPrefix}twig_dirs` ADD CONSTRAINT `lib_twig_dirs_ibfk_1` FOREIGN KEY (`tp_id`) REFERENCES `{dbPrefix}twig_prefix` (`tp_id`) ON DELETE CASCADE ON UPDATE CASCADE",
"ALTER TABLE `{dbPrefix}twig_templates` ADD CONSTRAINT `{dbPrefix}twig_templates_ibfk_1` FOREIGN KEY (`td_id`) REFERENCES `{dbPrefix}twig_dirs` (`td_id`) ON DELETE CASCADE ON UPDATE CASCADE",
];
