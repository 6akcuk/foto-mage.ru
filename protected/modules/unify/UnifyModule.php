<?php

class UnifyModule extends CWebModule
{
  public $citiesPerPage = 20;
  public $parserLogsPerPage = 30;
  public $supportsPerPage = 20;

  public $timezones = array(
    'Europe/London'			=>	'(Greenwich) Лондон',
    'Europe/Berlin'			=>	'(GMT +1:00) Берлин, Париж',
    'Europe/Kiev'			=>	'(GMT +2:00) Киев, Минск, Калининград',
    'Europe/Moscow'			=>	'(GMT +3:00) Москва, Санкт-Петербург',
    'Europe/Samara'			=>	'(GMT +4:00) Ереван',
    'Asia/Kabul'			=>	'(GMT +4:30) Кабул',
    'Asia/Yekaterinburg'	=>	'(GMT +5:00) Екатеринбург, Ташкент',
    'Asia/Colombo'			=>	'(GMT +5:30) Дели, Коломбо',
    'Asia/Katmandu'			=>	'(GMT +5:45) Катманду',
    'Asia/Novosibirsk'		=>	'(GMT +6:00) Новосибирск, Алматы',
    'Asia/Rangoon'			=>	'(GMT +6:30) Янгон',
    'Asia/Krasnoyarsk'		=>	'(GMT +7:00) Красноярск, Бангкок',
    'Asia/Irkutsk'			=>	'(GMT +8:00) Иркутск, Пекин',
    'Asia/Yakutsk'			=>	'(GMT +9:00) Якутск, Токио',
    'Asia/Vladivostok'		=>	'(GMT +10:00) Владивосток',
    'Asia/Magadan'			=>	'(GMT +11:00) Магадан'
  );

  public function init()
  {
    // this method is called when the module is being created
    // you may place code here to customize the module or the application

    // import the module-level models and components
    $this->setImport(array(
      'unify.models.*',
      'unify.components.*',
      'unify.components.views.*',
    ));
  }

  public function beforeControllerAction($controller, $action)
  {
    if(parent::beforeControllerAction($controller, $action))
    {
      // this method is called before any module controller action is performed
      // you may place customized code here
      return true;
    }
    else
      return false;
  }
}
