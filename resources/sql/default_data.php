<?php
/*
The plan is to enter data into the database but not assume knowledge of primary
keys.
First we enter data into tables that do not reference other data records:
constants, groups, navgroups, people, urls

Next we start connecting existing data with new data
people_group_map search for group id, search for people id, map them together
routes search for url id for each routes
route_group search for route, search for group, map them together
navigation search for url for each navigation
nav_ng_map search for nav, search for navgroup, map them together
page search for url for each page
*/
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
foreach ($a_constants as $a_constant) {

}

$a_groups = [
	[
        'group_name'        => 'SuperAdmin',
        'group_description' => 'The group for super administrators. There should be only a couple of these.',
        'group_auth_level'  => 10,
        'group_immutable'   => 1
    ],
	[
        'group_name'        => 'Managers',
        'group_description' => 'Most people accessing the manager should be in this group.',
        'group_auth_level'  => 9,
        'group_immutable'   => 1
    ],
	[
        'group_name'        => 'Editor',
        'group_description' => 'Editor for the CMS which does not exist in the FtpManager',
        'group_auth_level'  => 5,
        'group_immutable'   => 1
    ],
	[
        'group_name'        => 'Registered',
        'group_description' => 'The group for people that should not have access to the manager.',
        'group_auth_level'  => 3,
        'group_immutable'   => 1
    ],
	[
        'group_name'        => 'Anonymous',
        'group_description' => 'Not logged in or possibly unregistered',
        'group_auth_level'  => 0,
        'group_immutable'   => 1
    ]
];

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

$a_urls = [
	['url_host' => 'self', 'url_text' => $a_u['home'],       'url_scheme' => 'http',  'url_immutable' => 1],
	['url_host' => 'self', 'url_text' => $a_u['manager'],    'url_scheme' => 'https', 'url_immutable' => 1],
	['url_host' => 'self', 'url_text' => $a_u['login'],      'url_scheme' => 'https', 'url_immutable' => 1],
	['url_host' => 'self', 'url_text' => $a_u['logout'],     'url_scheme' => 'https', 'url_immutable' => 1],
	['url_host' => 'self', 'url_text' => $a_u['library'],    'url_scheme' => 'https', 'url_immutable' => 1],
	['url_host' => 'self', 'url_text' => $a_u['constants'],  'url_scheme' => 'https', 'url_immutable' => 1],
	['url_host' => 'self', 'url_text' => $a_u['groups'],     'url_scheme' => 'https', 'url_immutable' => 1],
	['url_host' => 'self', 'url_text' => $a_u['people'],     'url_scheme' => 'https', 'url_immutable' => 1],
	['url_host' => 'self', 'url_text' => $a_u['urls'],       'url_scheme' => 'https', 'url_immutable' => 1],
	['url_host' => 'self', 'url_text' => $a_u['routes'],     'url_scheme' => 'https', 'url_immutable' => 1],
	['url_host' => 'self', 'url_text' => $a_u['navigation'], 'url_scheme' => 'https', 'url_immutable' => 1],
	['url_host' => 'self', 'url_text' => $a_u['pages'],      'url_scheme' => 'https', 'url_immutable' => 1],
	['url_host' => 'self', 'url_text' => $a_u['tests'],      'url_scheme' => 'https', 'url_immutable' => 1]
];

$a_people = [
	[
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
	[
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
	]
];

$a_navgroups = [
	[
        'ng_name'    => 'Main',
        'ng_active'  => 1,
        'ng_default' => 1
    ],
	[
        'ng_name'    => 'SiteMap',
        'ng_active'  => 1,
        'ng_default' => 0
    ],
	[
        'ng_name'    => 'PageLinks',
        'ng_active'  => 1,
        'ng_default' => 0
    ],
	[
        'ng_name'    => 'ManagerLinks',
        'ng_active'  => 1,
        'ng_default' => 0
    ]

];

$a_people_group = [
    ['people_id' => 'SuperAdmin', 'group_id' => 'SuperAdmin'],
    ['people_id' => 'Admin',      'group_id' => 'Managers']
];

$a_routes = [
    [
        'url_id'          => $a_u['home'],
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
    ],
	[
	    'url_id'          => $a_u['manager'],
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	[
	    'url_id'          => $a_u['login'],
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	[
	    'url_id'          => $a_u['logout'],
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	[
	    'url_id'          => $a_u['library'],
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	[
	    'url_id'          => $a_u['constants'],
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	[
	    'url_id'          => $a_u['groups'],
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	[
	    'url_id'          => $a_u['people'],
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	[
	    'url_id'          => $a_u['urls'],
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	[
	    'url_id'          => $a_u['pages'],
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	[
	    'url_id'          => $a_u['routes'],
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	[
	    'url_id'          => $a_u['tests'],
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
	[
	    'url_id'          => $a_u['navigation'],
        'route_class'     => 'HomeController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	]
];

$a_route_group_map = [
    ['route_id' => $a_u['home'],       'group_id' => 'Anonymous'],
	['route_id' => $a_u['manager'],    'group_id' => 'Anonymous'],
	['route_id' => $a_u['login'],      'group_id' => 'Anonymous'],
	['route_id' => $a_u['logout'],     'group_id' => 'Admin'],
	['route_id' => $a_u['library'],    'group_id' => 'Admin'],
	['route_id' => $a_u['constants'],  'group_id' => 'Admin'],
	['route_id' => $a_u['groups'],     'group_id' => 'Admin'],
	['route_id' => $a_u['people'],     'group_id' => 'Admin'],
	['route_id' => $a_u['urls'],       'group_id' => 'Admin'],
	['route_id' => $a_u['pages'],      'group_id' => 'Admin'],
	['route_id' => $a_u['routes'],     'group_id' => 'Admin'],
	['route_id' => $a_u['tests'],      'group_id' => 'Admin'],
	['route_id' => $a_u['navigation'], 'group_id' => 'Admin']
];

$a_navigation = [
    'home'       => [
        'url_id'          => $a_u['home'],
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
        'url_id'          => $a_u['manager'],
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
        'url_id'          => $a_u['login'],
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
        'url_id'          => $a_u['logout'],
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
        'url_id'          => $a_u['library'],
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
        'url_id'          => $a_u['constants'],
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
        'url_id'          => $a_u['groups'],
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
        'url_id'          => $a_u['people'],
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
        'url_id'          => $a_u['urls'],
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
        'url_id'          => $a_u['pages'],
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
        'url_id'          => $a_u['routes'],
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
        'url_id'          => $a_u['navigation'],
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
        'url_id'          => $a_u['tests'],
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
    ['ng_id' => 'Main',         'nav_id' => 'home'],
    ['ng_id' => 'SiteMap',      'nav_id' => 'home'],
    ['ng_id' => 'PageLinks',    'nav_id' => 'home'],
    ['ng_id' => 'ManagerLinks', 'nav_id' => 'home'],
    ['ng_id' => 'ManagerLinks', 'nav_id' => 'manager'],
    ['ng_id' => 'ManagerLinks', 'nav_id' => 'login'],
    ['ng_id' => 'ManagerLinks', 'nav_id' => 'logout'],
    ['ng_id' => 'ManagerLinks', 'nav_id' => 'library'],
    ['ng_id' => 'ManagerLinks', 'nav_id' => 'constants'],
    ['ng_id' => 'ManagerLinks', 'nav_id' => 'groups'],
    ['ng_id' => 'ManagerLinks', 'nav_id' => 'people'],
    ['ng_id' => 'ManagerLinks', 'nav_id' => 'urls'],
    ['ng_id' => 'ManagerLinks', 'nav_id' => 'pages'],
    ['ng_id' => 'ManagerLinks', 'nav_id' => 'routes'],
    ['ng_id' => 'ManagerLinks', 'nav_id' => 'navigation'],
    ['ng_id' => 'ManagerLinks', 'nav_id' => 'tests']
];

$a_page = [
    'home'       => [
        'home',
        'text/html',
        'Home Page',
        'Home Page',
        '/',
        'en',
        'utf-8',
        1
    ],
    'manager'    => [
        'manager',
        'text/html',
        'Manager',
        'Manage Web Site',
        '/',
        'en',
        'utf-8',
        1
    ],
    'login'      => [
        'login',
        'text/html',
        'Manager: Please Login',
        'Login page for the manager.',
        '/',
        'en',
        'utf-8',
        1
    ],
    'logout'     => [
        'logout',
        'text/html',
        'Manager: Logout',
        'Logout page for the manager.',
        '/',
        'en',
        'utf-8',
        1
    ],
    'library'    => [
        'library',
        'text/html',
        'Advanced Config',
        'Manages People, Places and Things',
        '/',
        'en',
        'utf-8',
        1
    ],
    'constants'  => [
        'constants',
        'text/html',
        'Manager for Constants',
        'Manages the Constants used in app',
        '/',
        'en',
        'utf-8',
        1
    ],
    'groups'     => [
        'groups',
        'text/html',
        'Manager for Groups',
        'Manages the Groups',
        '/',
        'en',
        'utf-8',
        1
    ],
    'people'     => [
        'people',
        'text/html',
        'Manager for People',
        'Manages people',
        '/',
        'en',
        'utf-8',
        1
    ],
    'urls'       => [
        'urls',
        'text/html',
        'Manager for Urls',
        'Manages the Urls',
        '/',
        'en',
        'utf-8',
        1
    ],
    'routes'     => [
        'routes',
        'text/html',
        'Manager for Routes',
        'Manages the routes',
        '/',
        'en',
        'utf-8',
        1
    ],
    'navigation' => [
        'navigation',
        'text/html',
        'Manager for the Navigation tools',
        'Manager for Navigation tools',
        '/',
        'en',
        'utf-8',
        1
    ],
    'pages'      => [
        'pages',
        'text/html',
        'Manager for Pages',
        'Manages pages head information primarily',
        '/',
        'en',
        'utf-8',
        1
    ],
    'tests'      => [
        'tests',
        'text/html',
        'Manager Tests',
        'Runs tests for the code.',
        '/',
        'en',
        'utf-8',
        1
    ],
];
