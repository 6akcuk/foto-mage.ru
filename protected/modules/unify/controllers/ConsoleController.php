<?php

class ConsoleController extends Controller {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.AjaxFilter'
      ),
      array(
        'ext.RBACFilter.RBACFilter',
      ),
    );
  }

  public function actionIndex() {

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml =  $this->renderPartial('index',
        array(

        ), true);
    }
    else $this->render('index', array(

    ));
  }

  public function actionRunBot($id) {
    switch ($id) {
      case 'weather':
        echo exec('/var/www/protected/./yiic weatherparser parse');
        break;
      case 'sterlitamak.mirage':
        exec('/var/www/protected/./yiic parsersterlitamak mirage');
        break;
      case 'sterlitamak.salavat':
        exec('/var/www/protected/./yiic parsersterlitamak salavat');
        break;
      case 'sterlitamak.news':
        exec('/var/www/protected/./yiic parsersterlitamak strru');
        break;
    }
  }
}