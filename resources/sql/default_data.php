<?php
$a_u = [
    'home'             => '/',
    'manager'          => '/manager/',
    'login'            => '/manager/login/',
    'logout'           => '/manager/logout/',
    'tests'            => '/manager/tests/',
    'test_results'     => '/manager/tests/results/',
    'library'          => '/manager/config/',
    'lib_login'        => '/manager/config/login/',
    'lib_logout'       => '/manager/config/logout/',
    'constants'        => '/manager/config/constants/',
    'groups'           => '/manager/config/groups/',
    'people'           => '/manager/config/people/',
    'urls'             => '/manager/config/urls/',
    'routes'           => '/manager/config/routes/',
    'navigation'       => '/manager/config/navigation/',
    'pages'            => '/manager/config/pages/',
    'lib_tests'        => '/manager/config/tests/',
    'lib_test_results' => '/manager/config/tests/results/',
    'twig'             => '/manager/config/twig/',
    'error'            => '/error/'
];

$a_constants = [
	'display_date_format' => [
        'const_name'      => 'DISPLAY_DATE_FORMAT',
        'const_value'     => 'm/d/Y',
        'const_immutable' => 'true'
    ],
    'email_domain' => [
        'const_name'      => 'EMAIL_DOMAIN',
        'const_value'     => 'revealitconsulting.com',
        'const_immutable' => 'true'
    ],
    'email_form_to' => [
        'const_name'      => 'EMAIL_FORM_TO',
        'const_value'     => 'bill@revealitconsulting.com',
        'const_immutable' => 'true'
    ],
    'error_email_address' => [
        'const_name'      => 'ERROR_EMAIL_ADDRESS',
        'const_value'     => 'webmaster@revealitconsulting.com',
        'const_immutable' => 'true'
    ],
    'page_template' => [
        'const_name'      => 'PAGE_TEMPLATE',
        'const_value'     => 'index.twig',
        'const_immutable' => 'true'
    ],
    'twig_prefix' => [
        'const_name'      => 'TWIG_PREFIX',
        'const_value'     => 'site_',
        'const_immutable' => 'true'
    ],
    'lib_twig_prefix' => [
        'const_name'      => 'LIB_TWIG_PREFIX',
        'const_value'     =>  'lib_',
        'const_immutable' => 'true'
    ],
    'theme_name' => [
        'const_name'      => 'THEME_NAME',
        'const_value'     => '',
        'const_immutable' => 1
    ],
    'admin_theme_name' => [
        'const_name'      => 'ADMIN_THEME_NAME',
        'const_value'     => '',
        'const_immutable' => 1
    ],
    'css_dir_name' => [
        'const_name'      => 'CSS_DIR_NAME',
        'const_value'     => 'css',
        'const_immutable' => 'true'
    ],
    'html_dir_name' => [
        'const_name'      => 'HTML_DIR_NAME',
        'const_value'     => 'html',
        'const_immutable' => 'true'
    ],
    'js_dir_name' => [
        'const_name'      => 'JS_DIR_NAME',
        'const_value'     => 'js',
        'const_immutable' => 'true'
    ],
    'image_dir_name' => [
        'const_name'      => 'IMAGE_DIR_NAME',
        'const_value'     => 'images',
        'const_immutable' => 'true'
    ],
    'admin_dir_name' => [
        'const_name'      => 'ADMIN_DIR_NAME',
        'const_value'     => 'manager',
        'const_immutable' => 'true'
    ],
    'assets_dir_name' => [
        'const_name'      => 'ASSETS_DIR_NAME',
        'const_value'     => 'assets',
        'const_immutable' => 'true'
    ],
    'files_dir_name' => [
        'const_name'      => 'FILES_DIR_NAME',
        'const_value'     => 'files',
        'const_immutable' => 'true'
    ],
    'display_phone_format' => [
        'const_name'      => 'DISPLAY_PHONE_FORMAT',
        'const_value'     => 'XXX-XXX-XXXX',
        'const_immutable' => 'true'
    ],
    'themes_dir' => [
        'const_name'      => 'THEMES_DIR',
        'const_value'     => '',
        'const_immutable' => 'true'
    ],
    'rights_holder' => [
        'const_name'      => 'RIGHTS_HOLDER',
        'const_value'     => 'Reveal IT Consulting',
        'const_immutable' => 'true'
    ],
    'copyright_date' => [
        'const_name'      => 'COPYRIGHT_DATE',
        'const_value'     => '2001-2017',
        'const_immutable' => 'true'
    ],
    'private_dir_name' => [
        'const_name'      => 'PRIVATE_DIR_NAME',
        'const_value'     => 'private',
        'const_immutable' => 'true'
    ],
    'tmp_dir_name' => [
        'const_name'      => 'TMP_DIR_NAME',
        'const_value'     => 'tmp',
        'const_immutable' => 'true'
    ],
    'developer_mode' => [
        'const_name'      => 'DEVELOPER_MODE',
        'const_value'     => 'true',
        'const_immutable' => 'true'
    ],
    'session_idle_time' => [
        'const_name'      => 'SESSION_IDLE_TIME',
        'const_value'     => '1800',
        'const_immutable' => 'true'
    ]
];

$a_groups = [
	'superadmin' => [
        'group_name'        => 'SuperAdmin',
        'group_description' => 'The group for super administrators. There should be only a couple of these.',
        'group_auth_level'  => 10,
        'group_immutable'   => 'true'
    ],
    'admin' =>  [
        'group_name'        => 'Admins',
        'group_description' => 'The group for managing the advanced configuration of the site.',
        'group_auth_level'  => 9,
        'group_immutable'   => 'true'
    ],
	'manager' => [
        'group_name'        => 'Managers',
        'group_description' => 'Managers for the app should be in this group.',
        'group_auth_level'  => 8,
        'group_immutable'   => 'true'
    ],
	'editor' => [
        'group_name'        => 'Editor',
        'group_description' => 'Editor for the CMS which does not exist in the Manager',
        'group_auth_level'  => 5,
        'group_immutable'   => 'true'
    ],
	'registered' => [
        'group_name'        => 'Registered',
        'group_description' => 'The group for people that should not have access to the manager.',
        'group_auth_level'  => 1,
        'group_immutable'   => 'true'
    ],
	'anonymous' => [
        'group_name'        => 'Anonymous',
        'group_description' => 'Not logged in or possibly unregistered',
        'group_auth_level'  => 0,
        'group_immutable'   => 'true'
    ]
];

$a_urls = [
	'home'             => ['url_host' => 'self', 'url_text' => $a_u['home'],             'url_scheme' => 'http',  'url_immutable' => 'true'],
	'manager'          => ['url_host' => 'self', 'url_text' => $a_u['manager'],          'url_scheme' => 'https', 'url_immutable' => 'true'],
	'login'            => ['url_host' => 'self', 'url_text' => $a_u['login'],            'url_scheme' => 'https', 'url_immutable' => 'true'],
	'logout'           => ['url_host' => 'self', 'url_text' => $a_u['logout'],           'url_scheme' => 'https', 'url_immutable' => 'true'],
    'tests'            => ['url_host' => 'self', 'url_text' => $a_u['tests'],            'url_scheme' => 'https', 'url_immutable' => 'true'],
    'test_results'     => ['url_host' => 'self', 'url_text' => $a_u['test_results'],     'url_scheme' => 'https', 'url_immutable' => 'true'],
	'library'          => ['url_host' => 'self', 'url_text' => $a_u['library'],          'url_scheme' => 'https', 'url_immutable' => 'true'],
    'lib_login'        => ['url_host' => 'self', 'url_text' => $a_u['lib_login'],        'url_scheme' => 'https', 'url_immutable' => 'true'],
    'lib_logout'       => ['url_host' => 'self', 'url_text' => $a_u['lib_logout'],       'url_scheme' => 'https', 'url_immutable' => 'true'],
	'constants'        => ['url_host' => 'self', 'url_text' => $a_u['constants'],        'url_scheme' => 'https', 'url_immutable' => 'true'],
	'groups'           => ['url_host' => 'self', 'url_text' => $a_u['groups'],           'url_scheme' => 'https', 'url_immutable' => 'true'],
	'people'           => ['url_host' => 'self', 'url_text' => $a_u['people'],           'url_scheme' => 'https', 'url_immutable' => 'true'],
	'urls'             => ['url_host' => 'self', 'url_text' => $a_u['urls'],             'url_scheme' => 'https', 'url_immutable' => 'true'],
	'routes'           => ['url_host' => 'self', 'url_text' => $a_u['routes'],           'url_scheme' => 'https', 'url_immutable' => 'true'],
	'navigation'       => ['url_host' => 'self', 'url_text' => $a_u['navigation'],       'url_scheme' => 'https', 'url_immutable' => 'true'],
	'pages'            => ['url_host' => 'self', 'url_text' => $a_u['pages'],            'url_scheme' => 'https', 'url_immutable' => 'true'],
	'lib_tests'        => ['url_host' => 'self', 'url_text' => $a_u['lib_tests'],        'url_scheme' => 'https', 'url_immutable' => 'true'],
    'lib_test_results' => ['url_host' => 'self', 'url_text' => $a_u['lib_test_results'], 'url_scheme' => 'https', 'url_immutable' => 'true'],
    'twig'             => ['url_host' => 'self', 'url_text' => $a_u['twig'],             'url_scheme' => 'https', 'url_immutable' => 'true'],
	'error'            => ['url_host' => 'self', 'url_text' => $a_u['error'],            'url_scheme' => 'https', 'url_immutable' => 'true']
];

$a_people = [
	'superadmin' => [
	    'login_id'        => "SuperAdmin",
	    'real_name'       => "Super Admin",
	    'short_name'      => "GSA",
	    'password'        => "letGSAin",
	    'description'     => "The all powerful Admin",
	    'is_logged_in'    => 'false',
	    'bad_login_count' => 0,
	    'bad_login_ts'    => 0,
	    'is_active'       => 'true',
	    'is_immutable'    => 'true',
	    'created_on'      => "2012-08-12 02:55:28"
	],
	'admin' => [
	    'login_id'        => "Admin",
	    'real_name'       => "Admin",
	    'short_name'      => "ADM",
	    'password'        => "letADMin",
	    'description'     => "Allowed to admin to site",
	    'is_logged_in'    => 'false',
	    'bad_login_count' => 0,
	    'bad_login_ts'    => 0,
	    'is_active'       => 'true',
	    'is_immutable'    => 'true',
	    'created_on'      => "2012-08-12 02:55:28"
	],
	'manager' => [
        'login_id'        => "Manager",
        'real_name'       => "Manager",
        'short_name'      => "MAN",
        'password'        => "letMANin",
        'description'     => "Allowed to manage non-critical aspects of site",
        'is_logged_in'    => 'false',
        'bad_login_count' => 0,
        'bad_login_ts'    => 0,
        'is_active'       => 'true',
        'is_immutable'    => 'true',
        'created_on'      => "2012-08-12 02:55:28"
    ]
];

$a_navgroups = [
	'main' => [
        'ng_name'      => 'Main',
        'ng_active'    => 'true',
        'ng_default'   => 'true',
        'ng_immutable' => 'true'
    ],
    'manager' => [
        'ng_name'      => 'Manager',
        'ng_active'    => 'true',
        'ng_default'   => 'false',
        'ng_immutable' => 'true'
    ],
    'managerlinks' => [
        'ng_name'      => 'ManagerLinks',
        'ng_active'    => 'true',
        'ng_default'   => 'false',
        'ng_immutable' => 'true'
    ],
	'sitemap' => [
        'ng_name'      => 'SiteMap',
        'ng_active'    => 'true',
        'ng_default'   => 'false',
        'ng_immutable' => 'true'
    ],
	'pagelinks' => [
        'ng_name'      => 'PageLinks',
        'ng_active'    => 'true',
        'ng_default'   => 'false',
        'ng_immutable' => 'true'
    ],
    'configlinks' => [
        'ng_name'      => 'ConfigLinks',
        'ng_active'    => 'true',
        'ng_default'   => 'false',
        'ng_immutable' => 'true'
    ],
    'configtestlinks' => [
        'ng_name'      => 'ConfigTestLinks',
        'ng_active'    => 'true',
        'ng_default'   => 'false',
        'ng_immutable' => 'true'
    ],
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
        'route_immutable' => 'true'
    ],
    'error' => [
        'url_id'          => 'error',
        'route_class'     => 'HomeController',
        'route_method'    => 'routeError',
        'route_action'    => '',
        'route_immutable' => 'true'
    ],
	'manager' => [
	    'url_id'          => 'manager',
        'route_class'     => 'ManagerController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 'true'
	],
	'login' => [
	    'url_id'          => 'login',
        'route_class'     => 'ManagerController',
        'route_method'    => 'route',
        'route_action'    => 'login',
        'route_immutable' => 'true'
	],
	'logout' => [
	    'url_id'          => 'logout',
        'route_class'     => 'ManagerController',
        'route_method'    => 'route',
        'route_action'    => 'logout',
        'route_immutable' => 'true'
	],
    'tests' => [
        'url_id'          => 'tests',
        'route_class'     => 'ManagerController',
        'route_method'    => 'route',
        'route_action'    => 'tests',
        'route_immutable' => 'true'
    ],
    'test_results' => [
        'url_id'          => 'test_results',
        'route_class'     => 'ManagerController',
        'route_method'    => 'route',
        'route_action'    => 'tests',
        'route_immutable' => 'true'
    ],
	'library' => [
	    'url_id'          => 'library',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 'true'
	],
	'constants' => [
	    'url_id'          => 'constants',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'constants',
        'route_immutable' => 'true'
	],
	'groups' => [
	    'url_id'          => 'groups',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'groups',
        'route_immutable' => 'true'
	],
    'lib_login' => [
        'url_id'          => 'lib_login',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'login',
        'route_immutable' => 'true'
    ],
    'lib_logout' => [
        'url_id'          => 'lib_logout',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'logout',
        'route_immutable' => 'true'
    ],
	'navigation' => [
	    'url_id'          => 'navigation',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'navigation',
        'route_immutable' => 'true'
	],
    'pages' => [
        'url_id'          => 'pages',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'pages',
        'route_immutable' => 'true'
    ],
	'people' => [
	    'url_id'          => 'people',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'people',
        'route_immutable' => 'true'
	],
    'routes' => [
        'url_id'          => 'routes',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'routes',
        'route_immutable' => 'true'
    ],
    'lib_tests' => [
        'url_id'          => 'lib_tests',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'tests',
        'route_immutable' => 'true'
    ],
    'lib_test_results' => [
        'url_id'          => 'lib_test_results',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'tests',
        'route_immutable' => 'true'
    ],
    'twig' => [
        'url_id'          => 'twig',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'twig',
        'route_immutable' => 'true'
    ],
	'urls' => [
	    'url_id'          => 'urls',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'urls',
        'route_immutable' => 'true'
	]
];

$a_route_group_map = [
    ['route_id' => 'home',             'group_id' => 'anonymous'],
    ['route_id' => 'error',            'group_id' => 'anonymous'],
	['route_id' => 'manager',          'group_id' => 'anonymous'],
	['route_id' => 'login',            'group_id' => 'anonymous'],
	['route_id' => 'logout',           'group_id' => 'manager'],
    ['route_id' => 'tests',            'group_id' => 'manager'],
    ['route_id' => 'test_results',     'group_id' => 'manager'],
	['route_id' => 'library',          'group_id' => 'admin'],
    ['route_id' => 'lib_login',        'group_id' => 'admin'],
    ['route_id' => 'lib_logout',       'group_id' => 'admin'],
	['route_id' => 'constants',        'group_id' => 'admin'],
	['route_id' => 'groups',           'group_id' => 'admin'],
	['route_id' => 'people',           'group_id' => 'admin'],
	['route_id' => 'urls',             'group_id' => 'admin'],
    ['route_id' => 'routes',           'group_id' => 'admin'],
	['route_id' => 'pages',            'group_id' => 'admin'],
    ['route_id' => 'navigation',       'group_id' => 'admin'],
    ['route_id' => 'lib_tests',        'group_id' => 'admin'],
    ['route_id' => 'lib_test_results', 'group_id' => 'admin'],
    ['route_id' => 'twig',             'group_id' => 'admin']
];

$a_navigation = [
    'home'       => [
        'url_id'          => 'home',
        'nav_parent_id'   => 'home',
        'nav_name'        => 'home',
        'nav_text'        => 'Home',
        'nav_description' => 'Home page.',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 1,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'manager'    => [
        'url_id'          => 'manager',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'manager',
        'nav_text'        => 'Manager',
        'nav_description' => 'Manager Page',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 2,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'tests'    => [
        'url_id'          => 'tests',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'manager_tests',
        'nav_text'        => 'Manager Tests',
        'nav_description' => 'Manager Tests',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 5,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'login'      => [
        'url_id'          => 'login',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'manager_login',
        'nav_text'        => 'Manager Login',
        'nav_description' => 'Manager Login',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 4,
        'nav_active'      => 'false',
        'nav_immutable'   => 'true'
    ],
    'logout'     => [
        'url_id'          => 'logout',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'manager_logout',
        'nav_text'        => 'Manager Logout',
        'nav_description' => 'Manager Logout',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 4,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'library'    => [
        'url_id'          => 'library',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'library',
        'nav_text'        => 'Advanced Config',
        'nav_description' => 'Backend Manager Page',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 3,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'lib_login' => [
        'url_id'          => 'lib_login',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'lib_login',
        'nav_text'        => 'Login',
        'nav_description' => 'Login',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 10,
        'nav_active'      => 'false',
        'nav_immutable'   => 'true'
    ],
    'lib_logout' => [
        'url_id'          => 'lib_logout',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'lib_logout',
        'nav_text'        => 'Manager Logout',
        'nav_description' => 'Manager Logout',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 10,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'constants'  => [
        'url_id'          => 'constants',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'constants',
        'nav_text'        => 'Constants',
        'nav_description' => 'Define constants used throughout app.',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 7,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'groups'     => [
        'url_id'          => 'groups',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'groups',
        'nav_text'        => 'Groups',
        'nav_description' => 'Define Groups used for accessing app.',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 5,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'people'     => [
        'url_id'          => 'people',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'people',
        'nav_text'        => 'People',
        'nav_description' => 'Setup people allowed to access app.',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 6,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'urls'       => [
        'url_id'          => 'urls',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'urls',
        'nav_text'        => 'Urls',
        'nav_description' => 'Define the URLs used in the app',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 1,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'pages'      => [
        'url_id'          => 'pages',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'pages',
        'nav_text'        => 'Pages',
        'nav_description' => 'Define Page values.',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 3,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'routes'     => [
        'url_id'          => 'routes',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'routes',
        'nav_text'        => 'Routes',
        'nav_description' => 'Define routes used for where to go.',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 2,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'navigation' => [
        'url_id'          => 'navigation',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'navigation',
        'nav_text'        => 'Navigation',
        'nav_description' => 'Define Navigation Groups and Items',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 4,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'twig' => [
        'url_id'          => 'twig',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'twig',
        'nav_text'        => 'Twig',
        'nav_description' => 'Define Twig prefix, directories, and templates',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 8,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'lib_tests'      => [
        'url_id'          => 'lib_tests',
        'nav_parent_id'   => 'library',
        'nav_name'        => 'lib_tests',
        'nav_text'        => 'Configuration Tests',
        'nav_description' => 'Run Configuration Tests',
        'nav_css'         => '',
        'nav_level'       => 2,
        'nav_order'       => 9,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'constantsmodel_test'      => [
        'url_id'          => 'lib_tests',
        'nav_parent_id'   => 'lib_tests',
        'nav_name'        => 'constantsmodel_test',
        'nav_text'        => 'ConstantsModel',
        'nav_description' => 'Constants Model',
        'nav_css'         => '',
        'nav_level'       => 3,
        'nav_order'       => 1,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'pagemodel_test'      => [
        'url_id'          => 'lib_tests',
        'nav_parent_id'   => 'lib_tests',
        'nav_name'        => 'pagemodel_test',
        'nav_text'        => 'PageModel',
        'nav_description' => 'Page Model',
        'nav_css'         => '',
        'nav_level'       => 3,
        'nav_order'       => 1,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'peoplemodel_test'      => [
        'url_id'          => 'lib_tests',
        'nav_parent_id'   => 'lib_tests',
        'nav_name'        => 'peoplemodel_test',
        'nav_text'        => 'PeopleModel',
        'nav_description' => 'People Model',
        'nav_css'         => '',
        'nav_level'       => 3,
        'nav_order'       => 1,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'urlsmodel_test'      => [
        'url_id'          => 'lib_tests',
        'nav_parent_id'   => 'lib_tests',
        'nav_name'        => 'urlsmodel_test',
        'nav_text'        => 'UrlsModel',
        'nav_description' => 'Urls Model',
        'nav_css'         => '',
        'nav_level'       => 3,
        'nav_order'       => 1,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'navgroupsmodel_test'      => [
        'url_id'          => 'lib_tests',
        'nav_parent_id'   => 'lib_tests',
        'nav_name'        => 'navgroupsmodel_test',
        'nav_text'        => 'NavgroupsModel',
        'nav_description' => 'Navgroups Model',
        'nav_css'         => '',
        'nav_level'       => 3,
        'nav_order'       => 1,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'navigationmodel_test'      => [
        'url_id'          => 'lib_tests',
        'nav_parent_id'   => 'lib_tests',
        'nav_name'        => 'navigationmodel_test',
        'nav_text'        => 'NavigationModel',
        'nav_description' => 'Navigation Model',
        'nav_css'         => '',
        'nav_level'       => 3,
        'nav_order'       => 1,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
    'navngmapmodel_test'      => [
        'url_id'          => 'lib_tests',
        'nav_parent_id'   => 'lib_tests',
        'nav_name'        => 'navngmapmodel_test',
        'nav_text'        => 'NavNgMapModel',
        'nav_description' => 'NavNgMap Model',
        'nav_css'         => '',
        'nav_level'       => 3,
        'nav_order'       => 1,
        'nav_active'      => 'true',
        'nav_immutable'   => 'true'
    ],
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
    ['ng_id' => 'managerlinks', 'nav_id' => 'tests'],
    ['ng_id' => 'manager',      'nav_id' => 'home'],
    ['ng_id' => 'manager',      'nav_id' => 'manager'],
    ['ng_id' => 'manager',      'nav_id' => 'login'],
    ['ng_id' => 'manager',      'nav_id' => 'logout'],
    ['ng_id' => 'manager',      'nav_id' => 'library'],
    ['ng_id' => 'manager',      'nav_id' => 'lib_login'],
    ['ng_id' => 'manager',      'nav_id' => 'lib_logout'],
    ['ng_id' => 'manager',      'nav_id' => 'constants'],
    ['ng_id' => 'manager',      'nav_id' => 'groups'],
    ['ng_id' => 'manager',      'nav_id' => 'people'],
    ['ng_id' => 'manager',      'nav_id' => 'urls'],
    ['ng_id' => 'manager',      'nav_id' => 'pages'],
    ['ng_id' => 'manager',      'nav_id' => 'routes'],
    ['ng_id' => 'manager',      'nav_id' => 'navigation'],
    ['ng_id' => 'manager',      'nav_id' => 'twig'],
    ['ng_id' => 'manager',      'nav_id' => 'lib_tests'],
    ['ng_id' => 'manager',      'nav_id' => 'tests'],
    ['ng_id' => 'configlinks',  'nav_id' => 'home'],
    ['ng_id' => 'configlinks',  'nav_id' => 'manager'],
    ['ng_id' => 'configlinks',  'nav_id' => 'library'],
    ['ng_id' => 'configlinks',  'nav_id' => 'lib_login'],
    ['ng_id' => 'configlinks',  'nav_id' => 'lib_logout'],
    ['ng_id' => 'configlinks',  'nav_id' => 'constants'],
    ['ng_id' => 'configlinks',  'nav_id' => 'groups'],
    ['ng_id' => 'configlinks',  'nav_id' => 'people'],
    ['ng_id' => 'configlinks',  'nav_id' => 'urls'],
    ['ng_id' => 'configlinks',  'nav_id' => 'pages'],
    ['ng_id' => 'configlinks',  'nav_id' => 'routes'],
    ['ng_id' => 'configlinks',  'nav_id' => 'navigation'],
    ['ng_id' => 'configlinks',  'nav_id' => 'twig'],
    ['ng_id' => 'configlinks',  'nav_id' => 'lib_tests'],
    ['ng_id' => 'configtestlinks', 'nav_id' => 'constantsmodel_test'],
    ['ng_id' => 'configtestlinks', 'nav_id' => 'pagemodel_test'],
    ['ng_id' => 'configtestlinks', 'nav_id' => 'peoplemodel_test'],
    ['ng_id' => 'configtestlinks', 'nav_id' => 'urlsmodel_test'],
    ['ng_id' => 'configtestlinks', 'nav_id' => 'navgroupsmodel_test'],
    ['ng_id' => 'configtestlinks', 'nav_id' => 'navigationmodel_test'],
    ['ng_id' => 'configtestlinks', 'nav_id' => 'navngmapmodel_test'],
];

$a_page = [
    'home' => [
        'url_id'           => 'home',
        'ng_id'            => '1',
        'tpl_id'           => 'index',
        'page_type'        => 'text/html',
        'page_title'       => 'Home Page',
        'page_description' => 'Home Page',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'error' => [
        'url_id'           => 'error',
        'ng_id'            => '1',
        'tpl_id'           => 'error',
        'page_type'        => 'text/html',
        'page_title'       => 'Error Page',
        'page_description' => 'Error Page',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'manager' => [
        'url_id'           => 'manager',
        'ng_id'            => '2',
        'tpl_id'           => 'manager',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager',
        'page_description' => 'Manage Web Site',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'manager_tests' => [
        'url_id'           => 'tests',
        'ng_id'            => '2',
        'tpl_id'           => 'test',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager Tests',
        'page_description' => 'Manager Test',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'manager_test_results' => [
        'url_id'           => 'test_results',
        'ng_id'            => '2',
        'tpl_id'           => 'test_results',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager Test Results',
        'page_description' => 'Manager Test Results',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'login' => [
        'url_id'           => 'login',
        'ng_id'            => '2',
        'tpl_id'           => 'login',
        'page_type'        => 'text/html',
        'page_title'       => 'Please Login',
        'page_description' => 'Login page.',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'logout' => [
        'url_id'           => 'logout',
        'ng_id'            => '2',
        'tpl_id'           => 'login',
        'page_type'        => 'text/html',
        'page_title'       => 'Logout',
        'page_description' => 'Logout page.',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'verify_delete' => [
        'url_id'           => 'manager',
        'ng_id'            => '2',
        'tpl_id'           => 'verify_delete',
        'page_type'        => 'text/html',
        'page_title'       => 'Logout',
        'page_description' => 'Logout page.',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'library' => [
        'url_id'           => 'library',
        'ng_id'            => '2',
        'tpl_id'           => 'library',
        'page_type'        => 'text/html',
        'page_title'       => 'Advanced Config',
        'page_description' => 'Manages People, Places and Things',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'constants' => [
        'url_id'           => 'constants',
        'ng_id'            => '2',
        'tpl_id'           => 'lib_constants',
        'page_type'        => 'text/html',
        'page_title'       => 'Configuration for Constants',
        'page_description' => 'Configuration for Constants',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'groups' => [
        'url_id'           => 'groups',
        'ng_id'            => '2',
        'tpl_id'           => 'lib_groups',
        'page_type'        => 'text/html',
        'page_title'       => 'Configuration for Groups',
        'page_description' => 'Configuration for Groups',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'people' => [
        'url_id'           => 'people',
        'ng_id'            => '2',
        'tpl_id'           => 'lib_people',
        'page_type'        => 'text/html',
        'page_title'       => 'Configuration for People',
        'page_description' => 'Configuration for People',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'urls' => [
        'url_id'           => 'urls',
        'ng_id'            => '2',
        'tpl_id'           => 'lib_urls',
        'page_type'        => 'text/html',
        'page_title'       => 'Configuration for Urls',
        'page_description' => 'Configuration for Urls',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'routes' => [
        'url_id'           => 'routes',
        'ng_id'            => '2',
        'tpl_id'           => 'lib_routes',
        'page_type'        => 'text/html',
        'page_title'       => 'Configuration for Routes',
        'page_description' => 'Configuration for Routes',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'navigation' => [
        'url_id'           => 'navigation',
        'ng_id'            => '2',
        'tpl_id'           => 'lib_nav',
        'page_type'        => 'text/html',
        'page_title'       => 'Configuration for Navigation tools',
        'page_description' => 'Configuration for Navigation tools',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'pages' => [
        'url_id'           => 'pages',
        'ng_id'            => '2',
        'tpl_id'           => 'lib_pages',
        'page_type'        => 'text/html',
        'page_title'       => 'Configuration for Pages',
        'page_description' => 'Configuration for pages, head information primarily',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'twig' => [
        'url_id'           => 'twig',
        'ng_id'            => '2',
        'tpl_id'           => 'lib_twig',
        'page_type'        => 'text/html',
        'page_title'       => 'Configuration for Twig',
        'page_description' => 'Configuration for Twig prefix, directories, and templates',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'lib_tests' => [
        'url_id'           => 'lib_tests',
        'ng_id'            => '2',
        'tpl_id'           => 'lib_test',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager Tests',
        'page_description' => 'Runs tests for the code.',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
    'lib_test_results' => [
        'url_id'           => 'lib_test_results',
        'ng_id'            => '2',
        'tpl_id'           => 'lib_test_results',
        'page_type'        => 'text/html',
        'page_title'       => 'Manager Tests Results',
        'page_description' => 'Returns the test results for the configution section.',
        'page_base_url'    => '/',
        'page_lang'        => 'en',
        'page_charset'     => 'utf-8',
        'page_immutable'   => 'true'
    ],
];

$a_twig_prefix = [
    'site' => [
        'tp_prefix'  => 'site_',
        'tp_path'    => '/src/templates',
        'tp_active'  => 1,
        'tp_default' => 1
    ],
    'lib' => [
        'tp_prefix'  => 'lib_',
        'tp_path'    => '/src/apps/Ritc/Library/resources/templates',
        'tp_active'  => 1,
        'tp_default' => 0
    ]
];

$a_twig_dirs = [
    'site_default' => [
        'tp_id'   => 'site',
        'td_name' => 'default',
    ],
    'site_elements' => [
        'tp_id'   => 'site',
        'td_name' => 'elements',
    ],
    'site_forms' => [
        'tp_id'   => 'site',
        'td_name' => 'forms',
    ],
    'site_pages' => [
        'tp_id'   => 'site',
        'td_name' => 'pages',
    ],
    'site_snippets' => [
        'tp_id'     => 'site',
        'td_name'   => 'snippets',
    ],
    'lib_default' => [
        'tp_id'   => 'lib',
        'td_name' => 'default',
    ],
    'lib_elements' => [
        'tp_id'    => 'lib',
        'td_name'  => 'elements',
    ],
    'lib_forms' => [
        'tp_id'   => 'lib',
        'td_name' => 'forms',
    ],
    'lib_pages' => [
        'tp_id'   => 'lib',
        'td_name' => 'pages',
    ],
    'lib_snippets' => [
        'tp_id'   => 'lib',
        'td_name' => 'snippets',
    ]
];

$a_twig_tpls = [
    'index' => [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'index',
        'tpl_immutable' => 'true'
    ],
    'login' =>  [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'login',
        'tpl_immutable' => 'true'
    ],
    'manager' => [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'manager',
        'tpl_immutable' => 'true'
    ],
    'verify_delete' => [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'verify_delete',
        'tpl_immutable' => 'true'
    ],
    'error' => [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'error',
        'tpl_immutable' => 'true'
    ],
    'test' => [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'test',
        'tpl_immutable' => 'true'
    ],
    'test_results' => [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'test_results',
        'tpl_immutable' => 'true'
    ],
    'library' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'index',
        'tpl_immutable' => 'true'
    ],
    'lib_vd' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'verify_delete',
        'tpl_immutable' => 'true'
    ],
    'lib_constants' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'constants',
        'tpl_immutable' => 'true'
    ],
    'lib_groups' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'groups',
        'tpl_immutable' => 'true'
    ],
    'lib_nav' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'navigation',
        'tpl_immutable' => 'true'
    ],
    'lib_nav_form' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'navigation_form',
        'tpl_immutable' => 'true'
    ],
    'lib_pages' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'pages',
        'tpl_immutable' => 'true'
    ],
    'lib_page_form' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'page_form',
        'tpl_immutable' => 'true'
    ],
    'lib_people' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'people',
        'tpl_immutable' => 'true'
    ],
    'lib_person_form' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'person_form',
        'tpl_immutable' => 'true'
    ],
    'lib_routes' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'routes',
        'tpl_immutable' => 'true'
    ],
    'lib_urls' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'urls',
        'tpl_immutable' => 'true'
    ],
    'lib_error' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'error',
        'tpl_immutable' => 'true'
    ],
    'lib_tail' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'tail',
        'tpl_immutable' => 'true'
    ],
    'lib_twig' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'twig',
        'tpl_immutable' => 'true'
    ],
    'lib_test' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'test_list',
        'tpl_immutable' => 'true'
    ],
    'lib_test_results' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'test_results',
        'tpl_immutable' => 'true'
    ]
];

$a_twig_default_dir_names = [
    'default',
    'elements',
    'forms',
    'pages',
    'snippets',
    'tests'
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
    'page'             => $a_page,
    'tp_prefix'        => $a_twig_prefix,
    'tp_dirs'          => $a_twig_dirs,
    'tp_templates'     => $a_twig_tpls,
    'tp_default_dirs'  => $a_twig_default_dir_names
];
