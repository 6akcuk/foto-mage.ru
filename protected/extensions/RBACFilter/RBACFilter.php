<?php
/**
 * Created by JetBrains PhpStorm.
 * User: denis
 * Date: 08.11.12
 * Time: 14:32
 * To change this template use File | Settings | File Templates.
 */

class RBACFilter extends CFilter {
  protected function preFilter($filterChain) {
    $access = Yii::app()->user->checkAccess(self::getHierarchy());

    $date = new DateTime();

    /*$activity = new Activity();
    $activity->author_id = Yii::app()->user->getId();
    $activity->ip = ip2long($_SERVER['REMOTE_ADDR']);
    $activity->timestamp = $date->format("YmdHis") . substr((string)microtime(), 1, 4);
    $activity->request = substr($_SERVER['REQUEST_URI'], 0, 255);
    $activity->accepted = intval($access);
    $activity->save();*/

    if (!$access) {
      throw new CHttpException(403, 'В доступе отказано');
    }
    return $access;
  }

  public static function getHierarchy() {
    $ctrl = Yii::app()->controller;
    $action = $ctrl->action;
    $module = $ctrl->module;

    return (($module) ? $module->name .'.' : ''). $ctrl->id .'.'. $action->id;
  }
}