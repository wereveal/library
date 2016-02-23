CREATE TABLE `{$dbPrefix}menus` (
  `menu_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `menu_page_id` int(11) NOT NULL DEFAULT '0',
  `menu_parent_id` int(11) NOT NULL DEFAULT '0',
  `menu_name` varchar(128) NOT NULL DEFAULT 'Fred',
  `menu_css` varchar(64) NOT NULL DEFAULT 'menu-item',
  `menu_order` int(11) NOT NULL DEFAULT '0',
  `menu_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`menu_id`),
  KEY `menu_page_id` (`menu_page_id`),
  CONSTRAINT `qcdg_menus_ibfk_1` FOREIGN KEY (`menu_page_id`) REFERENCES `qcdg_page` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;