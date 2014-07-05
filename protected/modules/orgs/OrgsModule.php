<?php

class OrgsModule extends CWebModule
{
  public $orgsPerPage = 20;
  public $orgTypesPerPage = 20;
  public $eventTypesPerPage = 20;
  public $deliveryCategoriesPerPage = 20;
  public $deliveryGoodsPerPage = 20;
  public $deliveryOrdersPerPage = 20;
  public $discountActionsPerPage = 10;
  public $discountPromoCodesPerPage = 30;
  public $importPerPage = 20;

  public function init()
  {
    // this method is called when the module is being created
    // you may place code here to customize the module or the application

    // import the module-level models and components
    $this->setImport(array(
      'orgs.models.*',
      'orgs.components.*',
      'orgs.components.views.*',
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
