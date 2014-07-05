<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'E-Bash Console',
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
    'application.modules.purchases.models.*',
    'ext.ActiveHtml.*',
    'ext.SmsDelivery.*',
    'ext.Excel.*',
    'ext.*',
  ),

  'modules'=>array(
    'users' => array(
      'onlineInterval' => 10, // сколько минут считать пользователя онлайн
    ),
    'orgs',
    'events',
    'api',
    'advert',
    'news',
    'unify',
    'market',
    'taxi',
  ),

	// application components
	'components'=>array(
    'mutex' => array(
      'class' => 'application.extensions.EMutex',
    ),
    'db'=>array(
      'class'=>'system.db.CDbConnection',
      'connectionString' => 'mysql:host=localhost;dbname=common',
      'emulatePrepare' => true,
      'username' => 'root',
      'password' => 'oh64pb39Ac',
      'charset' => 'utf8',
    ),
    'taxi34'=>array(
      'class'=>'system.db.CDbConnection',
      'connectionString' => 'mysql:host=localhost;dbname=taxi_34',
      'emulatePrepare' => true,
      'username' => 'root',
      'password' => 'oh64pb39Ac',
      'charset' => 'utf8',
      'schemaCachingDuration' => 0,
      // включаем профайлер
      'enableProfiling' => true,
    ),
    'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
	),

  // application-level parameters that can be accessed
  // using Yii::app()->params['paramName']
  'params'=>array(
    // this is used in contact page
    'adminEmail'=>'webmaster@example.com',
    'noreplymail' => 'noreply@e-bash.me',
    'noreplyname' => 'E-Bash.me',
    'domain' => 'e-bash.me',
    'smsUsername' => '24314_spmix',
    'smsPassword' => 'jbS!D?z',
    'smsNumber' => 'SPMIX',
  ),
);