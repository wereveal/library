# Dump of table {$dbPrefix}constants
# Replace {$dbPrefix} with appropriate prefix
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
