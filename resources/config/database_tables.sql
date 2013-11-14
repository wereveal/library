-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `ritc_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(40) NOT NULL,
  `group_description` text NOT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `group_name` (`group_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

TRUNCATE TABLE `ritc_groups`;

INSERT INTO `ritc_groups` (`group_id`, `group_name`, `group_description`) VALUES
(1, 'SuperAdmin', 'The group for super administrators');

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `ritc_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `access_level` int(11) NOT NULL DEFAULT '4',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

TRUNCATE TABLE `ritc_roles`;

INSERT INTO `ritc_roles` (`id`, `name`, `description`, `access_level`) VALUES
(1, 'superadmin', 'Has Access to Everything.', 1),
(2, 'admin', 'Has complete access to the administration area.', 2),
(3, 'editor', 'Can add and modify records.', 3),
(4, 'registered', 'Registered User', 4),
(5, 'anonymous', 'Anonymous User', 5);

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `ritc_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `real_name` varchar(50) NOT NULL,
  `short_name` varchar(8) DEFAULT NULL,
  `password` varchar(128) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bad_login_count` int(11) NOT NULL DEFAULT '0',
  `bad_login_ts` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `roll_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

TRUNCATE TABLE `ritc_users`;

INSERT INTO `ritc_users` (`id`, `username`, `real_name`, `short_name`, `password`, `is_default`, `created_on`, `bad_login_count`, `bad_login_ts`) VALUES
(1, 'SuperAdmin', 'Super Admin', 'GSA', '9715ab56587dd7b748c71644d014250a26b479f28dfdea9927398e3ec1f221ac83da247d016052bb8ee8334320d74c70e1ce48afcc9114d7d837bfc88abb0bc4', 1, '2012-08-11 21:55:28', 0, 0);

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `ritc_user_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`group_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

TRUNCATE TABLE `ritc_user_groups`;

INSERT INTO `ritc_user_groups` (`id`, `user_id`, `group_id`) VALUES (1, 1, 1);

ALTER TABLE `ritc_user_groups`
  ADD CONSTRAINT `ritc_user_groups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `ritc_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ritc_user_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `ritc_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `ritc_user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

TRUNCATE TABLE `ritc_user_roles`;

INSERT INTO `ritc_user_roles` (`id`, `user_id`, `group_id`) VALUES (1, 1, 1);

ALTER TABLE `ritc_user_roles`
  ADD CONSTRAINT `ritc_user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `ritc_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ritc_user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `ritc_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
