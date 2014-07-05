<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 10.06.2013
 * Time: 12:39
 * To change this template use File | Settings | File Templates.
 */

class JSONFilter extends CFilter {
  protected function postFilter($filterChain) {
    header('Content-type: application/json');
    echo json_encode(
      array(
        'versionCode' => '10',
        'result' => Yii::app()->controller->json,
        'message' => Yii::app()->controller->pageHtml
      )
    );
    Yii::app()->end();
  }
}