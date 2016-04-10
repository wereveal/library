<?php
return [
"CREATE TABLE {$dbPrefix}constants (
  const_id serial NOT NULL,
  const_name character varying(64) NOT NULL UNIQUE,
  const_value character varying(64) NOT NULL,
  const_immutable smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (const_id)
)",

"CREATE TABLE {$dbPrefix}groups (
  group_id serial NOT NULL,
  group_name character varying(40) NOT NULL UNIQUE,
  group_description character varying(128) NOT NULL,
  group_auth_level integer NOT NULL DEFAULT 0,
  group_immutable smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (group_id)
)",

"CREATE TABLE {$dbPrefix}page (
  page_id serial NOT NULL,
  page_url character varying(255) NOT NULL DEFAULT '/'::character varying,
  page_type character varying(20) NOT NULL DEFAULT 'text/html'::character varying,
  page_title character varying(100) NOT NULL DEFAULT 'The Title'::character varying,
  page_description character varying(150) NOT NULL DEFAULT 'The Description'::character varying,
  page_base_url character varying(50) NOT NULL DEFAULT '/'::character varying,
  page_lang character varying(50) NOT NULL DEFAULT 'en'::character varying,
  page_charset character varying(100) NOT NULL DEFAULT 'utf-8'::character varying,
  page_immutable smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (page_id),
  UNIQUE (page_url)
)",

"CREATE TABLE {$dbPrefix}people (
  people_id serial NOT NULL,
  login_id character varying(60) NOT NULL,
  real_name character varying(50) NOT NULL,
  short_name character varying(8) NOT NULL,
  password character varying(128) NOT NULL,
  description character varying(250) NOT NULL DEFAULT ''::character varying,
  is_logged_in smallint NOT NULL DEFAULT 0,
  bad_login_count integer NOT NULL DEFAULT 0,
  bad_login_ts integer NOT NULL DEFAULT 0,
  is_active smallint NOT NULL DEFAULT 1,
  is_immutable smallint NOT NULL DEFAULT 0,
  created_on timestamp NOT NULL DEFAULT now(),
  PRIMARY KEY (people_id),
  UNIQUE (login_id),
  UNIQUE (short_name)
)",

"CREATE TABLE {$dbPrefix}people_group_map (
  pgm_id serial NOT NULL,
  people_id integer NOT NULL,
  group_id integer NOT NULL DEFAULT '3',
  PRIMARY KEY (pgm_id)
)",
"CREATE INDEX pgm_people_id_idx on {$dbPrefix}people_group_map (people_id)",
"CREATE INDEX pgm_group_id_idx on {$dbPrefix}people_group_map (group_id)",
"CREATE UNIQUE INDEX people_group_idx on {$dbPrefix}people_group_map (people_id, group_id)",

"ALTER TABLE ONLY {$dbPrefix}people_group_map
    ADD CONSTRAINT {$dbPrefix}pgm_ibfk_1 FOREIGN KEY (people_id) REFERENCES {$dbPrefix}people (people_id) ON DELETE CASCADE ON UPDATE CASCADE",
"ALTER TABLE ONLY {$dbPrefix}people_group_map
    ADD CONSTRAINT {$dbPrefix}pgm_ibfk_2 FOREIGN KEY (group_id) REFERENCES {$dbPrefix}groups (group_id) ON DELETE CASCADE ON UPDATE CASCADE",

"CREATE TABLE {$dbPrefix}routes (
  route_id serial NOT NULL,
  route_path character varying(128) NOT NULL,
  route_class character varying(64) NOT NULL,
  route_method character varying(64) NOT NULL,
  route_action character varying(255) NOT NULL,
  route_immutable smallint NOT NULL DEFAULT 0::smallint,
  PRIMARY KEY (route_id)
)",
"CREATE UNIQUE INDEX route_path_idx ON {$dbPrefix}routes (route_path)",
"ALTER SEQUENCE {$dbPrefix}routes_route_id_seq RESTART WITH 10",

"CREATE TABLE {$dbPrefix}routes_group_map (
  rgm_id serial NOT NULL,
  route_id integer NOT NULL DEFAULT 0,
  group_id integer NOT NULL DEFAULT 0,
  PRIMARY KEY (rgm_id)
)",
"CREATE INDEX rgm_group_id_idx ON {$dbPrefix}routes_group_map (group_id)",
"CREATE INDEX rgm_route_id_idx ON {$dbPrefix}routes_group_map (route_id)",
"CREATE UNIQUE INDEX rgm_idx ON {$dbPrefix}routes_group_map (route_id, group_id)",
"ALTER TABLE {$dbPrefix}routes_group_map
    ADD CONSTRAINT {$dbPrefix}routes_group_map_ibfk_1 FOREIGN KEY (route_id) REFERENCES {$dbPrefix}routes (route_id) ON DELETE CASCADE ON UPDATE CASCADE",
"ALTER TABLE {$dbPrefix}routes_group_map
    ADD CONSTRAINT {$dbPrefix}routes_group_map_ibfk_2 FOREIGN KEY (group_id) REFERENCES {$dbPrefix}groups (group_id) ON DELETE CASCADE ON UPDATE CASCADE",

"INSERT INTO {$dbPrefix}constants
    (const_name, const_value, const_immutable)
VALUES
    ('DISPLAY_DATE_FORMAT','m/d/Y',1),
    ('EMAIL_DOMAIN','revealitconsulting.com',1),
    ('EMAIL_FORM_TO','bill@revealitconsulting.com',1),
    ('ERROR_EMAIL_ADDRESS','webmaster@revealitconsulting.com',1),
    ('PAGE_TEMPLATE','index.twig',1),
    ('TWIG_PREFIX','app_',1),
    ('LIB_TWIG_PREFIX','lib_',1),
    ('THEME_NAME','',1),
    ('ADMIN_THEME_NAME','',1),
    ('CSS_DIR_NAME','css',1),
    ('HTML_DIR_NAME','html',1),
    ('JS_DIR_NAME','js',1),
    ('IMAGE_DIR_NAME','images',1),
    ('ADMIN_DIR_NAME','manager',1),
    ('ASSETS_DIR_NAME','assets',1),
    ('FILES_DIR_NAME','files',1),
    ('PRIVATE_DIR_NAME','private',1),
    ('TMP_DIR_NAME','tmp',1),
    ('DEVELOPER_MODE','true',1),
    ('DISPLAY_PHONE_FORMAT','XXX-XXX-XXXX',1),
    ('SESSION_IDLE_TIME','1800',1),
    ('THEMES_DIR','',1),
    ('RIGHTS_HOLDER','Reveal IT Consulting',1)",

"INSERT INTO {$dbPrefix}groups
    (group_name, group_description, group_auth_level, group_immutable)
VALUES
    ('SuperAdmin','The group for super administrators. There should be only a couple of these.',10,1),
    ('Managers','Most people accessing the manager should be in this group.',9,1),
    ('Editor','Editor for the CMS',5,1),
    ('Registered','The group for people that should not have access to the manager.',3,1),
    ('Anonymous','Not logged in, possibly unregistered',0,1)",

"INSERT INTO {$dbPrefix}page
    (page_url, page_type, page_title, page_description, page_base_url, page_lang, page_charset, page_immutable)
VALUES
    ('/manager/','text/html','Manager','Manages People, Places and Things','/','en','utf-8',1),
    ('/manager/constants/','text/html','Manager for Constants','Manages the Constants used in app','/','en','utf-8',1),
    ('/manager/constants/verify/','text/html','Manager for Constants','Manages the Constants, verifies that the constant should be deleted.','/','en','utf-8',1),
    ('/manager/groups/','text/html','Manager for Groups','Manages the Groups','/','en','utf-8',1),
    ('/manager/groups/verify/','text/html','Manager for Groups','Manages the groups, this page verifies deletion.','/','en','utf-8',1),
	('/manager/login/','text/html','Manager: Please Login','Login page for the manager.','/','en','utf-8',1),
	('/manager/logout/','text/html','Manager: Please Login','Login page for the manager.','/','en','utf-8',0),
    ('/manager/pages/','text/html','Manager for Pages','Manages pages head information primarily','/','en','utf-8',1),
    ('/manager/pages/verify/','text/html','Manager for Pages','Manages pages, verifies if record should be deleted','/','en','utf-8',1),
    ('/manager/people/','text/html','Manager for People','Manages people','/','en','utf-8',1),
    ('/manager/people/verify/','text/html','Manager for People','Manages people, verifies a person should be deleted.','/','en','utf-8',1),
	('/manager/routes/','text/html','Manager for Routes','Manages the routes','/','en','utf-8',1),
	('/manager/routes/verify/','text/html','Manager for Routes','Manages the routes, verifies route should be deleted.','/','en','utf-8',1),
	('/manager/tests/','text/html','Manager Tests','Runs tests for the code.','/','en','utf-8',1)",

"INSERT INTO {$dbPrefix}people
    (login_id, real_name, short_name, password, description, is_logged_in, bad_login_count, bad_login_ts, is_active, is_immutable, created_on)
VALUES
    ('SuperAdmin','Super Admin','GSA','$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW','The all powerful Admin',0,0,0,1,1,'2012-08-12 07:55:28'),
	('Admin','Admin','ADM','$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW','Allowed to admin the backend.',1,0,0,1,1,'2015-09-04 18:15:55')",

"INSERT INTO {$dbPrefix}people_group_map
    (people_id, group_id)
VALUES
    (1,1),
	(1,2),
	(1,3),
	(2,2),
	(2,3)",

"INSERT INTO {$dbPrefix}routes
    (route_id, route_path, route_class, route_method, route_action, route_immutable)
VALUES
    (1, '/manager/','GuideManagerController','render','',1),
	(2, '/manager/login/','GuideManagerController','render','verifyLogin',1),
	(3, '/manager/routes/','GuideManagerController','renderRoutesAdmin','',1),
	(4, '/manager/constants/','GuideManagerController','renderConstantsAdmin','',1),
	(5, '/manager/people/','GuideManagerController','renderPeopleAdmin','',1),
	(6, '/manager/groups/','GuideManagerController','renderGroupsAdmin','',1),
	(7, '/manager/pages/','GuideManagerController','renderPageAdmin','',1),
	(8, '/manager/tests/','GuideManagerController','renderTestsAdmin','',1),
	(9, '/manager/logout/','GuideManagerController','render','logout',1)",

"INSERT INTO {$dbPrefix}routes_group_map
    (route_id, group_id)
VALUES
    (1,1),
	(1,2),
	(2,1),
	(2,2),
	(2,3),
	(2,4),
	(2,5),
	(3,1),
	(3,2),
	(4,1),
	(4,2),
	(5,1),
	(5,2),
	(6,1),
	(6,2),
	(7,1),
	(7,2),
	(8,1),
	(9,1),
	(9,2),
	(9,3),
	(9,4),
	(9,5)"
];



