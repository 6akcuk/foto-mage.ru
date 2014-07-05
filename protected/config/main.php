<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Магия фото',
  'language' => 'ru',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
    'application.modules.users.models.*',
    'application.modules.users.components.*',
    'application.modules.users.components.views.*',
    'application.modules.orgs.models.*',
    'application.modules.advert.models.*',
    'application.modules.news.models.*',
    'application.modules.market.models.*',
    'ext.*',
    'ext.ActiveHtml.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'s1a55j7',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1','95.105.75.166'),
		),
    'users' => array(
        'onlineInterval' => 10, // сколько минут считать пользователя онлайн
    ),
    'search',
    'news',
    'im',
    'mail',
    'feed',
    'discuss',
    'unify',
    'photoarchive',
  ),

	// application components
	'components'=>array(
        'clientScript' => array(
            'class' => 'ext.ActiveHtml.ClientScript',
        ),

		'user'=>array(
            'class' => 'application.modules.users.components.WebUser',
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format

    'authManager' => array(
      'class' => 'CDbAuthManager',
      'itemTable' => 'rbac_items',
      'itemChildTable' => 'rbac_item_childs',
      'assignmentTable' => 'rbac_assignments',
      'defaultRoles' => array('Гость'),
    ),

		'urlManager'=>array(
			'urlFormat'=>'path',
      'showScriptName' => false,
			'rules'=>array(
        'support' => 'site/support',
        'privacy' => 'site/privacy',
        'iphone' => 'site/index',
        'android' => 'site/index',
        'nullForm' => 'site/nullForm',
        'setcity' => 'site/setcity',
        'login' => 'users/base/login',
        'logout' => 'users/base/logout',
        'register' => 'users/base/register',
        'forgot' => 'users/base/forgot',
        'invite<id:\d+>' => 'site/invite',
        'inviteBySMS' => 'users/profiles/inviteBySMS',
        'vklogin' => 'users/base/vklogin',
        'id<id:\d+>' => 'users/profiles',
        'friends' => 'users/friends',
        'friends<id:\d+>' => 'users/friends',
        'wall<id:\d+>_<post_id:\d+>' => 'users/profiles/wall',
        'wall<id:\d+>' => 'users/profiles/wall',
        'wall' => 'users/profiles/wall',
        'edit' => 'users/profiles/edit',
        'settings' => 'users/profiles/settings',
        'notify' => 'users/profiles/notify',
        'write<id:\d+>' => 'mail/default/write',
        'reputation<id:\d+>' => 'users/profiles/reputation',
        'photoarchive<id:\d+>' => 'photoarchive/photos/index',
        'photoarchive' => 'photoarchive/albums/index',
        '<controller:(friends)>' => 'users/friends/',
        '<controller:(friends)>/<action:\w+>' => 'users/friends/<action>',
        '<controller:(users)>' => 'users/users/index',
        '<controller:(users)>/<action:\w+>' => 'users/users/<action>',
        '<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
/*
		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),*/
		// uncomment the following to use a MySQL database
    /*'cache'=>array(
      'class'=>'system.caching.CMemCache',
      'useMemcached' => true,
      'servers'=>array(
        array('host'=>'127.0.0.1', 'port'=>11211, 'weight'=>100),
      ),
    ),*/

		'db'=>array(
      'class'=>'system.db.CDbConnection',
			'connectionString' => 'mysql:host=localhost;dbname=common',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => 'oh64pb39Ac',
			'charset' => 'utf8',
      'schemaCachingDuration' => 3600,
      // включаем профайлер
      'enableProfiling' => true,
		),

		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
        /*array(
          // направляем результаты профайлинга в ProfileLogRoute (отображается
          // внизу страницы)
          'class'=>'CProfileLogRoute',
          'levels'=>'profile',
          'enabled'=>true,
        ),*/
				// uncomment the following to show log messages on web pages
        /*
				array(
					'class'=>'CWebLogRoute',
				),
        */
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
    'noreplymail' => 'noreply@foto-mage.ru',
    'noreplyname' => 'foto-mage.ru',
    'domain' => 'foto-mage.ru',
    'smsUsername' => '26984_lavenue',
    'smsPassword' => '@Hwo?y6',
    'smsNumber' => 'e-Bash',

    'vk_client_id' => '4193898',
    'vk_client_secret' => 'Hw70MA458YfIzHs5xHYc',

    'androidDensity' => array(
      '0.75' => 'ldpi',
      '1.0' => 'mdpi',
      '1.5' => 'hdpi',
      '2.0' => 'xhdpi',
      '3.0' => 'xxhdpi',
    ),
	),
);