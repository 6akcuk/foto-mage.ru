<?php

class NewsModule extends CWebModule
{
  public $newsPostsPerPage = 20;
  public $adsPerPage = 20;

  public function init()
  {
    // this method is called when the module is being created
    // you may place code here to customize the module or the application

    // import the module-level models and components
    $this->setImport(array(
      'news.models.*',
      'news.components.*',
      'news.components.views.*',
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
