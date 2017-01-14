<?php
$a_u = [
    'home'       => '/',
    'manager'    => '/manager/',
    'login'      => '/manager/login/',
    'logout'     => '/manager/logout/',
    'library'    => '/manager/library/',
    'constants'  => '/manager/library/constants/',
    'groups'     => '/manager/library/groups/',
    'people'     => '/manager/library/people/',
    'urls'       => '/manager/library/urls/',
    'routes'     => '/manager/library/routes/',
    'navigation' => '/manager/library/navigation/',
    'pages'      => '/manager/library/pages/',
    'tests'      => '/manager/library/tests/'
];

$a_constants = [
	[
        'const_name'      => 'DISPLAY_DATE_FORMAT',
        'const_value'     => 'm/d/Y',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'EMAIL_DOMAIN',
        'const_value'     => 'revealitconsulting.com',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'EMAIL_FORM_TO',
        'const_value'     => 'bill@revealitconsulting.com',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'ERROR_EMAIL_ADDRESS',
        'const_value'     => 'webmaster@revealitconsulting.com',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'PAGE_TEMPLATE',
        'const_value'     => 'index.twig',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'TWIG_PREFIX',
        'const_value'     => 'ritc_',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'LIB_TWIG_PREFIX',
        'const_value'     =>  'lib_',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'THEME_NAME',
        'const_value'     => '',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'ADMIN_THEME_NAME',
        'const_value'     => '',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'CSS_DIR_NAME',
        'const_value'     => 'css',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'HTML_DIR_NAME',
        'const_value'     => 'html',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'JS_DIR_NAME',
        'const_value'     => 'js',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'IMAGE_DIR_NAME',
        'const_value'     => 'images',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'ADMIN_DIR_NAME',
        'const_value'     => 'manager',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'ASSETS_DIR_NAME',
        'const_value'     => 'assets',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'FILES_DIR_NAME',
        'const_value'     => 'files',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'DISPLAY_PHONE_FORMAT',
        'const_value'     => 'XXX-XXX-XXXX',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'THEMES_DIR',
        'const_value'     => '',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'RIGHTS_HOLDER',
        'const_value'     => 'Reveal IT Consulting',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'PRIVATE_DIR_NAME',
        'const_value'     => 'private',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'TMP_DIR_NAME',
        'const_value'     => 'tmp',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'DEVELOPER_MODE',
        'const_value'     => 'true',
        'const_immutable' => 1
    ],
	[
        'const_name'      => 'SESSION_IDLE_TIME',
        'const_value'     => '1800',
        'const_immutable' => 1
    ]
];

$a_groups = [
	'superadmin' => [
        'group_name'        => 'SuperAdmin',
        'group_description' => 'The group for super administrators. There should be only a couple of these.',
        'group_auth_level'  => 10,
        'group_immutable'   => 1
    ],
    'admin' =>  [
        'group_name'        => 'Admins',
        'group_description' => 'The group for managing the advanced configuration of the site.',
        'group_auth_level'  => 9,
        'group_immutable'   => 1
    ],
	'manager' => [
        'group_name'        => 'Managers',
        'group_description' => 'Managers for the app should be in this group.',
        'group_auth_level'  => 8,
        'group_immutable'   => 1
    ],
	'editor' => [
        'group_name'        => 'Editor',
        'group_description' => 'Editor for the CMS which does not exist in the Manager',
        'group_auth_level'  => 5,
        'group_immutable'   => 1
    ],
	'registered' => [
        'group_name'        => 'Registered',
        'group_description' => 'The group for people that should not have access to the manager.',
        'group_auth_level'  => 1,
        'group_immutable'   => 1
    ],
	'anonymous' => [
        'group_name'        => 'Anonymous',
        'group_description' => 'Not logged in or possibly unregistered',
        'group_auth_level'  => 0,
        'group_immutable'   => 1
    ]
];

$a_urls = [
	'home'       => ['url_host' => 'self', 'url_text' => $a_u['home'],       'url_scheme' => 'http',  'url_immutable' => 1],
	'manager'    => ['url_host' => 'self', 'url_text' => $a_u['manager'],    'url_scheme' => 'https', 'url_immutable' => 1],
	'login'      => ['url_host' => 'self', 'url_text' => $a_u['login'],      'url_scheme' => 'https', 'url_immutable' => 1],
	'logout'     => ['url_host' => 'self', 'url_text' => $a_u['logout'],     'url_scheme' => 'https', 'url_immutable' => 1],
	'library'    => ['url_host' => 'self', 'url_text' => $a_u['library'],    'url_scheme' => 'https', 'url_immutable' => 1],
	'constants'  => ['url_host' => 'self', 'url_text' => $a_u['constants'],  'url_scheme' => 'https', 'url_immutable' => 1],
	'groups'     => ['url_host' => 'self', 'url_text' => $a_u['groups'],     'url_scheme' => 'https', 'url_immutable' => 1],
	'people'     => ['url_host' => 'self', 'url_text' => $a_u['people'],     'url_scheme' => 'https', 'url_immutable' => 1],
	'urls'       => ['url_host' => 'self', 'url_text' => $a_u['urls'],       'url_scheme' => 'https', 'url_immutable' => 1],
	'routes'     => ['url_host' => 'self', 'url_text' => $a_u['routes'],     'url_scheme' => 'https', 'url_immutable' => 1],
	'navigation' => ['url_host' => 'self', 'url_text' => $a_u['navigation'], 'url_scheme' => 'https', 'url_immutable' => 1],
	'pages'      => ['url_host' => 'self', 'url_text' => $a_u['pages'],      'url_scheme' => 'https', 'url_immutable' => 1],
	'tests'      => ['url_host' => 'self', 'url_text' => $a_u['tests'],      'url_scheme' => 'https', 'url_immutable' => 1]
];

$a_people = [
	'superadmin' => [
	    'login_id'        => "SuperAdmin",
	    'real_name'       => "Super Admin",
	    'short_name'      => "GSA",
	    'password'        => "letGSAin",
	    'description'     => "The all powerful Admin",
	    'is_logged_in'    => 0,
	    'bad_login_count' => 0,
	    'bad_login_ts'    => 0,
	    'is_active'       => 1,
	    'is_immutable'    => 1,
	    'created_on'      => "2012-08-12 02:55:28"
	],
	'admin' => [
	    'login_id'        => "Admin",
	    'real_name'       => "Admin",
	    'short_name'      => "ADM",
	    'password'        => "letADMin",
	    'description'     => "Allowed to admin to site",
	    'is_logged_in'    => 0,
	    'bad_login_count' => 0,
	    'bad_login_ts'    => 0,
	    'is_active'       => 1,
	    'is_immutable'    => 1,
	    'created_on'      => "2012-08-12 02:55:28"
	],
	'manager' => [
        'login_id'        => "Manager",
        'real_name'       => "Manager",
        'short_name'      => "MAN",
        'password'        => "letMANin",
        'description'     => "Allowed to manage non-critical aspects of site",
        'is_logged_in'    => 0,
        'bad_login_count' => 0,
        'bad_login_ts'    => 0,
        'is_active'       => 1,
        'is_immutable'    => 1,
        'created_on'      => "2012-08-12 02:55:28"
    ]
];

$a_navgroups = [
	'main' => [
        'ng_name'    => 'Main',
        'ng_active'  => 1,
        'ng_default' => 1
    ],
    'manager' => [
        'ng_name'    => 'Manager',
        'ng_active'  => 1,
        'ng_default' => 0
    ],
	'sitemap' => [
        'ng_name'    => 'SiteMap',
        'ng_active'  => 1,
        'ng_default' => 0
    ],
	'pagelinks' => [
        'ng_name'    => 'PageLinks',
        'ng_active'  => 1,
        'ng_default' => 0
    ],
	'managerlinks' => [
        'ng_name'    => 'ManagerLinks',
        'ng_active'  => 1,
        'ng_default' => 0
    ]

];

$a_people_group = [
    [
        'people_id' => 'superadmin',
        'group_id'  => 'superadmin'
    ],
    [
        'people_id' => 'admin',
        'group_id'  => 'admin'
    ],
    [
        'people_id' => 'manager',
        'group_id'  => 'manager'
    ],
];

$a_routes = [
    'home' => [
        'url_id'          => 'home',
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
    ],
	'manager' => [
	    'url_id'          => 'manager',
        'route_class'     => 'ManagerController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	'login' => [
	    'url_id'          => 'login',
        'route_class'     => 'ManagerController',
        'route_method'    => 'route',
        'route_action'    => 'login',
        'route_immutable' => 1
	],
	'logout' => [
	    'url_id'          => 'logout',
        'route_class'     => 'ManagerController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	'library' => [
	    'url_id'          => 'library',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	'constants' => [
	    'url_id'          => 'constants',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'constants',
        'route_immutable' => 1
	],
	'groups' => [
	    'url_id'          => 'groups',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'groups',
        'route_immutable' => 1
	],
	'people' => [
	    'url_id'          => 'people',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'people',
        'route_immutable' => 1
	],
	'urls' => [
	    'url_id'          => 'urls',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'urls',
        'route_immutable' => 1
	],
	'pages' => [
	    'url_id'          => 'pages',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'pages',
        'route_immutable' => 1
	],
	'routes' => [
	    'url_id'          => 'routes',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'routes',
        'route_immutable' => 1
	],
	'tests' => [
	    'url_id'          => 'tests',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'tests',
        'route_immutable' => 1
	],
	'navigation' => [
	    'url_id'          => 'navigation',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'navigation',
        'route_immutable' => 1
	]
];

$a_route_group_map = [
    ['route_id' => 'home',       'group_id' => 'anonymous'],
	['route_id' => 'manager',    'group_id' => 'anonymous'],
	['route_id' => 'login',      'group_id' => 'anonymous'],
	['route_id' => 'logout',     'group_id' => 'manager'],
	['route_id' => 'library',    'group_id' => 'admin'],
	['route_id' => 'constants',  'group_id' => 'admin'],
	['route_id' => 'groups',     'group_id' => 'admin'],
	['route_id' => 'people',     'group_id' => 'admin'],
	['route_id' => 'urls',       'group_id' => 'admin'],
    ['route_id' => 'routes',     'group_id' => 'admin'],
	['route_id' => 'pages',      'group_id' => 'admin'],
    ['route_id' => 'navigation', 'group_id' => 'admin'],
	['route_id' => 'tests',      'group_id' => 'admin']
];

$a_navigation = [
    'home'       => [
        'url_id'          => 'home',
        'nav_parent_id'   => 'home',
        'nav_name'        => 'Home',
        'nav_text'        => 'Home',
        'nav_description' => 'Home page.',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 1,
        'nav_active'      => 1
    ],
    'manager'    => [
        'url_id'          => 'manager',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'Manager',
        'nav_text'        => 'Manager',
        'nav_description' => 'Manager Page',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 4,
        'nav_active'      => 1
    ],
    'login'      => [
        'url_id'          => 'login',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'Manager',
        'nav_text'        => 'Manager',
        'nav_description' => 'Manager',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 3,
        'nav_active'      => 1
    ],
    'logout'     => [
        'url_id'          => 'logout',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'Manager',
        'nav_text'        => 'Manager',
        'nav_description' => 'Manager',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 3,
        'nav_active'      => 1
    ],
    'library'    => [
        'url_id'          => 'library',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'Advanced Config',
        'nav_text'        => 'Advanced Config',
        'nav_description' => 'Backend Manager Page',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 4,
        'nav_active'      => 1
    ],
    'constants'  => [
        'url_id'          => 'constants',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'Constants',
        'nav_text'        => 'Constants',
        'nav_description' => 'Define constants used throughout app.',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 6,
        'nav_active'      => 1
    ],
    'groups'     => [
        'url_id'          => 'groups',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'Groups',
        'nav_text'        => 'Groups',
        'nav_description' => 'Define Groups used for accessing app.',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 4,
        'nav_active'      => 1
    ],
    'people'     => [
        'url_id'          => 'people',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'People',
        'nav_text'        => 'People',
        'nav_description' => 'Setup people allowed to access app.',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 5,
        'nav_active'      => 1
    ],
    'urls'       => [
        'url_id'          => 'urls',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'Urls',
        'nav_text'        => 'Urls',
        'nav_description' => 'Define the URLs used in the app',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 7,
        'nav_active'      => 1
    ],
    'pages'      => [
        'url_id'          => 'pages',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'Pages',
        'nav_text'        => 'Pages',
        'nav_description' => 'Define Page values.',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 2,
        'nav_active'      => 1
    ],
    'routes'     => [
        'url_id'          => 'routes',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'Routes',
        'nav_text'        => 'Routes',
        'nav_description' => 'Define routes used for where to go.',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 1,
        'nav_active'      => 1
    ],
    'navigation' => [
        'url_id'          => 'navigation',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'Navigation',
        'nav_text'        => 'Navigation',
        'nav_description' => 'Define Navigation Groups and Items',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 3,
        'nav_active'      => 1
    ],
    'tests'      => [
        'url_id'          => 'tests',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'Tests',
        'nav_text'        => 'Tests',
        'nav_description' => 'Run Tests',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 6,
        'nav_active'      => 0
    ]
];

$a_nav_ng_map = [
    ['ng_id' => 'main',         'nav_id' => 'home'],
    ['ng_id' => 'sitemap',      'nav_id' => 'home'],
    ['ng_id' => 'pagelinks',    'nav_id' => 'home'],
    ['ng_id' => 'managerlinks', 'nav_id' => 'home'],
    ['ng_id' => 'managerlinks', 'nav_id' => 'manager'],
    ['ng_id' => 'managerlinks', 'nav_id' => 'login'],
    ['ng_id' => 'managerlinks', 'nav_id' => 'logout'],
    ['ng_id' => 'managerlinks', 'nav_id' => 'library'],
    ['ng_id' => 'managerlinks', 'nav_id' => 'constants'],
    ['ng_id' => 'managerlinks', 'nav_id' => 'groups'],
    ['ng_id' => 'managerlinks', 'nav_id' => 'people'],
    ['ng_id' => 'managerlinks', 'nav_id' => 'urls'],
    ['ng_id' => 'managerlinks', 'nav_id' => 'pages'],
    ['ng_id' => 'managerlinks', 'nav_id' => 'routes'],
    ['ng_id' => 'managerlinks', 'nav_id' => 'navigation'],
    ['ng_id' => 'managerlinks', 'nav_id' => 'tests'],
    ['ng_id' => 'manager',      'nav_id' => 'home'],
    ['ng_id' => 'manager',      'nav_id' => 'manager'],
    ['ng_id' => 'manager',      'nav_id' => 'login'],
    ['ng_id' => 'manager',      'nav_id' => 'logout'],
    ['ng_id' => 'manager',      'nav_id' => 'library'],
    ['ng_id' => 'manager',      'nav_id' => 'constants'],
    ['ng_id' => 'manager',      'nav_id' => 'groups'],
    ['ng_id' => 'manager',      'nav_id' => 'people'],
    ['ng_id' => 'manager',      'nav_id' => 'urls'],
    ['ng_id' => 'manager',      'nav_id' => 'pages'],
    ['ng_id' => 'manager',      'nav_id' => 'routes'],
    ['ng_id' => 'manager',      'nav_id' => 'navigation'],
    ['ng_id' => 'manager',      'nav_id' => 'tests']

];

$a_page = [
    'home' => [
        'url_id'           => 'home',
        'page_type'        => 'text/html',
        'page_title'       => 'Home Page',
        'page_description' => 'Home Page',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 1
    ],
    'manager' => [
        'url_id'           => 'manager',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager',
        'page_description' => 'Manage Web Site',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 1
    ],
    'login' => [
        'url_id'           => 'login',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager: Please Login',
        'page_description' => 'Login page for the manager.',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 1
    ],
    'logout' => [
        'url_id'           => 'logout',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager: Logout',
        'page_description' => 'Logout page for the manager.',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 1
    ],
    'library' => [
        'url_id'           => 'library',
        'page_type'        => 'text/html',
        'page_title'       => 'Advanced Config',
        'page_description' => 'Manages People, Places and Things',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 1
    ],
    'constants' => [
        'url_id'           => 'constants',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager for Constants',
        'page_description' => 'Manages the Constants used in app',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 1
    ],
    'groups' => [
        'url_id'           => 'groups',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager for Groups',
        'page_description' => 'Manages the Groups',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 1
    ],
    'people' => [
        'url_id'           => 'people',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager for People',
        'page_description' => 'Manages people',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 1
    ],
    'urls' => [
        'url_id'           => 'urls',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager for Urls',
        'page_description' => 'Manages the Urls',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 1
    ],
    'routes' => [
        'url_id'           => 'routes',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager for Routes',
        'page_description' => 'Manages the routes',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 1
    ],
    'navigation' => [
        'url_id'           => 'navigation',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager for the Navigation tools',
        'page_description' => 'Manager for Navigation tools',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 1
    ],
    'pages' => [
        'url_id'           => 'pages',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager for Pages',
        'page_description' => 'Manages pages head information primarily',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 1
    ],
    'tests' => [
        'url_id'           => 'tests',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager Tests',
        'page_description' => 'Runs tests for the code.',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 1
    ],
];

return [
    'constants'        => $a_constants,
    'groups'           => $a_groups,
    'urls'             => $a_urls,
    'people'           => $a_people,
    'navgroups'        => $a_navgroups,
    'people_group_map' => $a_people_group,
    'routes'           => $a_routes,
    'routes_group_map' => $a_route_group_map,
    'navigation'       => $a_navigation,
    'nav_ng_map'       => $a_nav_ng_map,
    'page'             => $a_page
];
