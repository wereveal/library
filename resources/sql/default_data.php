<?php
/*
The plan is to enter data into the database but not assume knowledge of primary
keys.
First we enter data into tables that do not reference other data records:
constants
groups
navgroups
people
urls

Next we start connecting existing data with new data
people_group_map search for group id, search for people id, map them together
routes search for url id for each routes
route_group search for route, search for group, map them together
navigation search for url for each navigation
nav_ng_map search for nav, search for navgroup, map them together
page search for url for each page
*/
$a_constants = [
	['DISPLAY_DATE_FORMAT','m/d/Y',1],
	['EMAIL_DOMAIN','revealitconsulting.com',1],
	['EMAIL_FORM_TO','bill@revealitconsulting.com',1],
	['ERROR_EMAIL_ADDRESS','webmaster@revealitconsulting.com',1],
	['PAGE_TEMPLATE','index.twig',1],
	['TWIG_PREFIX','ritc_',1],
	['LIB_TWIG_PREFIX', 'lib_',1],
	['THEME_NAME','',1],
	['ADMIN_THEME_NAME','',1],
	['CSS_DIR_NAME','css',1],
	['HTML_DIR_NAME','html',1],
	['JS_DIR_NAME','js',1],
	['IMAGE_DIR_NAME','images',1],
	['ADMIN_DIR_NAME','manager',1],
	['ASSETS_DIR_NAME','assets',1],
	['FILES_DIR_NAME','files',1],
	['DISPLAY_PHONE_FORMAT','XXX-XXX-XXXX',1],
	['THEMES_DIR','',1],
	['RIGHTS_HOLDER','Reveal IT Consulting',1],
	['PRIVATE_DIR_NAME','private',1],
	['TMP_DIR_NAME','tmp',1],
	['DEVELOPER_MODE','true',1],
	['SESSION_IDLE_TIME','1800',1]
];

$a_groups = [
	['SuperAdmin','The group for super administrators. There should be only a couple of these.',10,1],
	['Managers','Most people accessing the manager should be in this group.',9,1],
	['Editor','Editor for the CMS which does not exist in the FtpManager',5,1],
	['Registered','The group for people that should not have access to the manager.',3,1],
	['Anonymous','Not logged in, possibly unregistered',0,1]
];

$a_urls = [
	['self','/','http',1],
	['self','/manager/','https',1],
	['self','/manager/login/','https',1],
	['self','/manager/logout/','https',1],
	['self','/manager/library/','https',1],
	['self','/manager/library/constants/','https',1],
	['self','/manager/library/groups/','https',1],
	['self','/manager/library/people/','https',1],
	['self','/manager/library/urls/','https',1],
	['self','/manager/library/routes/','https',1],
	['self','/manager/library/navigation/','https',1],
	['self','/manager/library/pages/','https',1],
	['self','/manager/library/tests/','https',1]
];

$a_people = [
	["SuperAdmin","Super Admin","GSA","letGSAin","The all powerful Admin",0,0,0,1,1,"2012-08-12 02:55:28"],
	["Admin","Admin","ADM","letADMin","Allowed to admin the backend.",1,0,0,1,1,"2015-09-04 13:15:55"]
];

$a_navgroups = [
	['Main',1,1],
	['SiteMap',1,0],
	['PageLinks',1,0],
	['ManagerLinks',1,0]
];

$a_people_group = [
    ['SuperAdmin', 'SuperAdmin'],
    ['Admin', 'Managers']
];

$a_routes = [
    ['/','HomeController','render','',1],
	['/manager/','ManagerController','render','',1],
	['/manager/login/','LibraryController','route','verifyLogin',1],
	['/manager/logout/','LibraryController','route','logout',1],
	['/manager/library/', 'LibraryController','render','',1],
	['/manager/library/constants/','LibraryController','render','constantsAdmin',1],
	['/manager/library/groups/','LibraryController','route','groupsAdmin',1],
	['/manager/library/people/','LibraryController','route','peopleAdmin',1],
	['/manager/library/urls/','LibraryController','route','urlsAdmin',1],
	['/manager/library/pages/','LibraryController','route','pageAdmin',1],
	['/manager/library/routes/','LibraryController','route','routesAdmin',1],
	['/manager/library/tests/','LibraryController','route','testsAdmin',1],
	['/manager/library/navigation/','LibraryController','route','navigationAdmin',1]
];

$a_route_group_map = [
    ['/', 'Anonymous'],
	['/manager/','Anonymous'],
	['/manager/login/','Anonymous'],
	['/manager/logout/','Admin'],
	['/manager/library/', 'Admin'],
	['/manager/library/constants/','Admin'],
	['/manager/library/groups/','Admin'],
	['/manager/library/people/','Admin'],
	['/manager/library/urls/','Admin'],
	['/manager/library/pages/','Admin'],
	['/manager/library/routes/','Admin'],
	['/manager/library/tests/','Admin'],
	['/manager/library/navigation/','Admin']
];
