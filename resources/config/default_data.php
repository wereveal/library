<?php
$a_u = [
    'home'             => '/',
    'manager'          => '/manager/',
    'login'            => '/manager/login/',
    'logout'           => '/manager/logout/',
    'tests'            => '/manager/tests/',
    'test_results'     => '/manager/tests/results/',
    'library'          => '/manager/config/',
    'lib_ajax'         => '/manager/config/ajax/',
    'lib_login'        => '/manager/config/login/',
    'lib_logout'       => '/manager/config/logout/',
    'constants'        => '/manager/config/constants/',
    'groups'           => '/manager/config/groups/',
    'navigation'       => '/manager/config/navigation/',
    'pages'            => '/manager/config/pages/',
    'people'           => '/manager/config/people/',
    'routes'           => '/manager/config/routes/',
    'lib_tests'        => '/manager/config/tests/',
    'lib_test_results' => '/manager/config/tests/results/',
    'twig'             => '/manager/config/twig/',
    'urls'             => '/manager/config/urls/',
    'error'            => '/error/'
];

$a_constants = [
	'display_date_format' => [
        'const_name'      => 'DISPLAY_DATE_FORMAT',
        'const_value'     => 'm/d/Y',
        'const_immutable' => 1
    ],
    'email_domain' => [
        'const_name'      => 'EMAIL_DOMAIN',
        'const_value'     => 'revealitconsulting.com',
        'const_immutable' => 1
    ],
    'email_form_to' => [
        'const_name'      => 'EMAIL_FORM_TO',
        'const_value'     => 'bill@revealitconsulting.com',
        'const_immutable' => 1
    ],
    'error_email_address' => [
        'const_name'      => 'ERROR_EMAIL_ADDRESS',
        'const_value'     => 'webmaster@revealitconsulting.com',
        'const_immutable' => 1
    ],
    'page_template' => [
        'const_name'      => 'PAGE_TEMPLATE',
        'const_value'     => 'index.twig',
        'const_immutable' => 1
    ],
    'css_dir_name' => [
        'const_name'      => 'CSS_DIR_NAME',
        'const_value'     => 'css',
        'const_immutable' => 1
    ],
    'scss_dir_name' => [
        'const_name'      => 'SCSS_DIR_NAME',
        'const_value'     => 'scss',
        'const_immutable' => 1
    ],
    'html_dir_name' => [
        'const_name'      => 'HTML_DIR_NAME',
        'const_value'     => 'html',
        'const_immutable' => 1
    ],
    'js_dir_name' => [
        'const_name'      => 'JS_DIR_NAME',
        'const_value'     => 'js',
        'const_immutable' => 1
    ],
    'image_dir_name' => [
        'const_name'      => 'IMAGES_DIR_NAME',
        'const_value'     => 'images',
        'const_immutable' => 1
    ],
    'admin_dir_name' => [
        'const_name'      => 'ADMIN_DIR_NAME',
        'const_value'     => 'manager',
        'const_immutable' => 1
    ],
    'assets_dir_name' => [
        'const_name'      => 'ASSETS_DIR_NAME',
        'const_value'     => 'assets',
        'const_immutable' => 1
    ],
    'files_dir_name' => [
        'const_name'      => 'FILES_DIR_NAME',
        'const_value'     => 'files',
        'const_immutable' => 1
    ],
    'fonts_dir_name' => [
        'const_name'      => 'FONTS_DIR_NAME',
        'const_value'     => 'fonts',
        'const_immutable' => 1
    ],
    'display_phone_format' => [
        'const_name'      => 'DISPLAY_PHONE_FORMAT',
        'const_value'     => 'XXX-XXX-XXXX',
        'const_immutable' => 1
    ],
    'rights_holder' => [
        'const_name'      => 'RIGHTS_HOLDER',
        'const_value'     => 'Reveal IT Consulting',
        'const_immutable' => 1
    ],
    'copyright_date' => [
        'const_name'      => 'COPYRIGHT_DATE',
        'const_value'     => '2001-2017',
        'const_immutable' => 1
    ],
    'private_dir_name' => [
        'const_name'      => 'PRIVATE_DIR_NAME',
        'const_value'     => 'private',
        'const_immutable' => 1
    ],
    'tmp_dir_name' => [
        'const_name'      => 'TMP_DIR_NAME',
        'const_value'     => 'tmp',
        'const_immutable' => 1
    ],
    'developer_mode' => [
        'const_name'      => 'DEVELOPER_MODE',
        'const_value'     => 'true',
        'const_immutable' => 1
    ],
    'session_idle_time' => [
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
	'home'             => ['url_host' => 'self', 'url_text' => $a_u['home'],             'url_scheme' => 'http',  'url_immutable' => 1],
	'manager'          => ['url_host' => 'self', 'url_text' => $a_u['manager'],          'url_scheme' => 'https', 'url_immutable' => 1],
	'login'            => ['url_host' => 'self', 'url_text' => $a_u['login'],            'url_scheme' => 'https', 'url_immutable' => 1],
	'logout'           => ['url_host' => 'self', 'url_text' => $a_u['logout'],           'url_scheme' => 'https', 'url_immutable' => 1],
    'tests'            => ['url_host' => 'self', 'url_text' => $a_u['tests'],            'url_scheme' => 'https', 'url_immutable' => 1],
    'test_results'     => ['url_host' => 'self', 'url_text' => $a_u['test_results'],     'url_scheme' => 'https', 'url_immutable' => 1],
	'library'          => ['url_host' => 'self', 'url_text' => $a_u['library'],          'url_scheme' => 'https', 'url_immutable' => 1],
    'lib_ajax'         => ['url_host' => 'self', 'url_text' => $a_u['lib_ajax'],         'url_scheme' => 'https', 'url_immutable' => 1],
    'lib_login'        => ['url_host' => 'self', 'url_text' => $a_u['lib_login'],        'url_scheme' => 'https', 'url_immutable' => 1],
    'lib_logout'       => ['url_host' => 'self', 'url_text' => $a_u['lib_logout'],       'url_scheme' => 'https', 'url_immutable' => 1],
	'constants'        => ['url_host' => 'self', 'url_text' => $a_u['constants'],        'url_scheme' => 'https', 'url_immutable' => 1],
	'groups'           => ['url_host' => 'self', 'url_text' => $a_u['groups'],           'url_scheme' => 'https', 'url_immutable' => 1],
    'navigation'       => ['url_host' => 'self', 'url_text' => $a_u['navigation'],       'url_scheme' => 'https', 'url_immutable' => 1],
	'pages'            => ['url_host' => 'self', 'url_text' => $a_u['pages'],            'url_scheme' => 'https', 'url_immutable' => 1],
    'people'           => ['url_host' => 'self', 'url_text' => $a_u['people'],           'url_scheme' => 'https', 'url_immutable' => 1],
    'routes'           => ['url_host' => 'self', 'url_text' => $a_u['routes'],           'url_scheme' => 'https', 'url_immutable' => 1],
    'lib_tests'        => ['url_host' => 'self', 'url_text' => $a_u['lib_tests'],        'url_scheme' => 'https', 'url_immutable' => 1],
    'lib_test_results' => ['url_host' => 'self', 'url_text' => $a_u['lib_test_results'], 'url_scheme' => 'https', 'url_immutable' => 1],
    'twig'             => ['url_host' => 'self', 'url_text' => $a_u['twig'],             'url_scheme' => 'https', 'url_immutable' => 1],
    'urls'             => ['url_host' => 'self', 'url_text' => $a_u['urls'],             'url_scheme' => 'https', 'url_immutable' => 1],
	'error'            => ['url_host' => 'self', 'url_text' => $a_u['error'],            'url_scheme' => 'https', 'url_immutable' => 1]
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
        'ng_active'    => 1,
        'ng_default'   => 1,
        'ng_immutable' => 1
    ],
    'manager' => [
        'ng_name'      => 'Manager',
        'ng_active'    => 1,
        'ng_default'   => 0,
        'ng_immutable' => 1
    ],
    'managerlinks' => [
        'ng_name'      => 'ManagerLinks',
        'ng_active'    => 1,
        'ng_default'   => 0,
        'ng_immutable' => 1
    ],
	'sitemap' => [
        'ng_name'      => 'SiteMap',
        'ng_active'    => 1,
        'ng_default'   => 0,
        'ng_immutable' => 1
    ],
	'pagelinks' => [
        'ng_name'      => 'PageLinks',
        'ng_active'    => 1,
        'ng_default'   => 0,
        'ng_immutable' => 1
    ],
    'configlinks' => [
        'ng_name'      => 'ConfigLinks',
        'ng_active'    => 1,
        'ng_default'   => 0,
        'ng_immutable' => 1
    ],
    'configtestlinks' => [
        'ng_name'      => 'ConfigTestLinks',
        'ng_active'    => 1,
        'ng_default'   => 0,
        'ng_immutable' => 1
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
        'route_immutable' => 1
    ],
    'error' => [
        'url_id'          => 'error',
        'route_class'     => 'HomeController',
        'route_method'    => 'routeError',
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
        'route_action'    => 'logout',
        'route_immutable' => 1
	],
    'tests' => [
        'url_id'          => 'tests',
        'route_class'     => 'ManagerController',
        'route_method'    => 'route',
        'route_action'    => 'tests',
        'route_immutable' => 1
    ],
    'test_results' => [
        'url_id'          => 'test_results',
        'route_class'     => 'ManagerController',
        'route_method'    => 'route',
        'route_action'    => 'tests',
        'route_immutable' => 1
    ],
	'library' => [
	    'url_id'          => 'library',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => '',
        'route_immutable' => 1
	],
    'lib_ajax' => [
        'url_id'          => 'lib_ajax',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'ajax',
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
    'lib_login' => [
        'url_id'          => 'lib_login',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'login',
        'route_immutable' => 1
    ],
    'lib_logout' => [
        'url_id'          => 'lib_logout',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'logout',
        'route_immutable' => 1
    ],
	'navigation' => [
	    'url_id'          => 'navigation',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'navigation',
        'route_immutable' => 1
	],
    'pages' => [
        'url_id'          => 'pages',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'pages',
        'route_immutable' => 1
    ],
	'people' => [
	    'url_id'          => 'people',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'people',
        'route_immutable' => 1
	],
    'routes' => [
        'url_id'          => 'routes',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'routes',
        'route_immutable' => 1
    ],
    'lib_tests' => [
        'url_id'          => 'lib_tests',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'tests',
        'route_immutable' => 1
    ],
    'lib_test_results' => [
        'url_id'          => 'lib_test_results',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'tests',
        'route_immutable' => 1
    ],
    'twig' => [
        'url_id'          => 'twig',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'twig',
        'route_immutable' => 1
    ],
	'urls' => [
	    'url_id'          => 'urls',
        'route_class'     => 'LibraryController',
        'route_method'    => 'route',
        'route_action'    => 'urls',
        'route_immutable' => 1
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
    ['route_id' => 'lib_ajax',         'group_id' => 'admin'],
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
    ],
    'manager'    => [
        'url_id'          => 'manager',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'manager',
        'nav_text'        => 'Manager',
        'nav_description' => 'Manager Page',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 3,
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
    ],
    'login'      => [
        'url_id'          => 'login',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'manager_login',
        'nav_text'        => 'Manager Login',
        'nav_description' => 'Manager Login',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 5,
        'nav_active'      => 0,
        'nav_immutable'   => 1
    ],
    'logout'     => [
        'url_id'          => 'logout',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'manager_logout',
        'nav_text'        => 'Manager Logout',
        'nav_description' => 'Manager Logout',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 5,
        'nav_active'      => 1,
        'nav_immutable'   => 1
    ],
    'library'    => [
        'url_id'          => 'library',
        'nav_parent_id'   => 'manager',
        'nav_name'        => 'library',
        'nav_text'        => 'Advanced Config',
        'nav_description' => 'Backend Manager Page',
        'nav_css'         => '',
        'nav_level'       => 1,
        'nav_order'       => 4,
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 0,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'nav_active'      => 1,
        'nav_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
        'page_immutable'   => 1
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
    'site_tests' => [
        'tp_id'   => 'site',
        'td_name' => 'tests',
    ],
    'site_themes' => [
        'tp_id'   => 'site',
        'td_name' => 'themes',
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
    ],
    'lib_tests' => [
        'tp_id'   => 'lib',
        'td_name' => 'tests',
    ],
    'lib_themes' => [
        'tp_id'   => 'lib',
        'td_name' => 'themes',
    ],
];

$a_twig_tpls = [
    'index' => [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'index',
        'tpl_immutable' => 1
    ],
    'login' =>  [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'login',
        'tpl_immutable' => 1
    ],
    'manager' => [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'manager',
        'tpl_immutable' => 1
    ],
    'verify_delete' => [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'verify_delete',
        'tpl_immutable' => 1
    ],
    'error' => [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'error',
        'tpl_immutable' => 1
    ],
    'test' => [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'test',
        'tpl_immutable' => 1
    ],
    'test_results' => [
        'td_id'         => 'site_pages',
        'tpl_name'      => 'test_results',
        'tpl_immutable' => 1
    ],
    'library' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'index',
        'tpl_immutable' => 1
    ],
    'lib_vd' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'verify_delete',
        'tpl_immutable' => 1
    ],
    'lib_constants' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'constants',
        'tpl_immutable' => 1
    ],
    'lib_groups' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'groups',
        'tpl_immutable' => 1
    ],
    'lib_nav' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'navigation',
        'tpl_immutable' => 1
    ],
    'lib_nav_form' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'navigation_form',
        'tpl_immutable' => 1
    ],
    'lib_pages' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'pages',
        'tpl_immutable' => 1
    ],
    'lib_page_form' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'page_form',
        'tpl_immutable' => 1
    ],
    'lib_people' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'people',
        'tpl_immutable' => 1
    ],
    'lib_person_form' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'person_form',
        'tpl_immutable' => 1
    ],
    'lib_routes' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'routes',
        'tpl_immutable' => 1
    ],
    'lib_urls' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'urls',
        'tpl_immutable' => 1
    ],
    'lib_error' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'error',
        'tpl_immutable' => 1
    ],
    'lib_tail' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'tail',
        'tpl_immutable' => 1
    ],
    'lib_twig' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'twig',
        'tpl_immutable' => 1
    ],
    'lib_test' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'test_list',
        'tpl_immutable' => 1
    ],
    'lib_test_results' => [
        'td_id'         => 'lib_pages',
        'tpl_name'      => 'test_results',
        'tpl_immutable' => 1
    ]
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
    'tp_templates'     => $a_twig_tpls
];