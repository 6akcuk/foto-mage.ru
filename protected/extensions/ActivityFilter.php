<?php
/**
 * Created by JetBrains PhpStorm.
 * User: denis
 * Date: 21.03.13
 * Time: 14:55
 * To change this template use File | Settings | File Templates.
 */

class ActivityFilter extends CFilter {
  protected function preFilter($filterChain) {
    $date = new DateTime();

    $activity = new Activity();
    $activity->author_id = Yii::app()->user->getId();
    $activity->ip = ip2long($_SERVER['REMOTE_ADDR']);
    $activity->timestamp = $date->format('YmdHis.u');
    $activity->request = $_SERVER['REQUEST_URI'];

    return true;
  }
}