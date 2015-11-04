# Dump of table {$dbPrefix}constants
# Replace {$dbPrefix} with appropriate prefix
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{$dbPrefix}constants`;

CREATE TABLE `{$dbPrefix}constants` (
  `const_id` int(11) NOT NULL AUTO_INCREMENT,
  `const_name` varchar(64) NOT NULL DEFAULT '',
  `const_value` varchar(64) NOT NULL DEFAULT '',
  `const_immutable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`const_id`),
  UNIQUE KEY `config_key` (`const_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `{$dbPrefix}constants` WRITE;
/*!40000 ALTER TABLE `{$dbPrefix}constants` DISABLE KEYS */;

INSERT INTO `{$dbPrefix}constants`
    (`const_name`, `const_value`, `const_immutable`)
VALUES
	('DISPLAY_DATE_FORMAT','m/d/Y',1),
	('EMAIL_DOMAIN','revealitconsulting.com',1),
	('EMAIL_FORM_TO','bill@revealitconsulting.com',1),
	('ERROR_EMAIL_ADDRESS','webmaster@revealitconsulting.com',1),
	('PAGE_TEMPLATE','index.twig',1),
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
	('SESSION_IDLE_TIME','1800',1);

/*!40000 ALTER TABLE `{$dbPrefix}constants` ENABLE KEYS */;
UNLOCK TABLES;
