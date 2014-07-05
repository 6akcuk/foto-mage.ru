<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 26.12.12
 * Time: 8:15
 * To change this template use File | Settings | File Templates.
 */
Yii::import('zii.widgets.CPortlet');

class Paginator extends CPortlet {
  public $url;
  public $offsets;
  public $offset;
  public $delta;
  public $nopages = false;
  public $forceUrl = false;

    protected function renderContent() {
        $this->render('paginator');
    }
}