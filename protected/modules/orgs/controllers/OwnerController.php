<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 16.10.12
 * Time: 20:41
 * To change this template use File | Settings | File Templates.
 */

class OwnerController extends Controller {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.AjaxFilter'
      ),
      array(
        'ext.RBACFilter.RBACFilter'
      )
    );
  }

  public function actionIndex($id = 0, $url = false) {
    if ($id == 0 && $url === false)
      throw new CHttpException(500, 'Нет данных по организации');

    $org = ($id > 0) ? Organization::model()->with('org_type', 'city', 'rooms', 'modules')->findByPk($id) : Organization::model()->with('org_type', 'city', 'rooms', 'modules')->find('url = :url', array(':url' => $url));
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на просмотр данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на просмотр данной организации');
    }

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('index', array(
        'org' => $org,
      ), true);
    }
    else $this->render('index', array(
      'org' => $org,
    ));
  }

  public function actionModules($id) {
    $org = Organization::model()->with('modules')->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на управление модулями данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на управление модулями данной организации');
    }

    if (isset($_POST['enable_delivery'])) {
      if (!$org->modules) {
        $org->modules = new OrganizationModule();
        $org->modules->org_id = $org->org_id;
        $org->modules->save();
      }

      $org->modules->enable_delivery = $_POST['enable_delivery'];
      $org->modules->enable_discount = $_POST['enable_discount'];
      $org->modules->enable_market = $_POST['enable_market'];

      if ($org->modules->save(true, array('enable_delivery', 'enable_discount', 'enable_market'))) {
        $result['success'] = true;
        $result['message'] = 'Все настройки успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($org->modules->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $this->pageHtml = $this->renderPartial('modulesBox', array(
      'org' => $org,
    ), true);
  }

  public function actionAddRoom($id) {
    $org = Organization::model()->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на добавление помещения в данную организацию');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на добавление помещения в данную организацию');
    }

    if (isset($_POST['name'])) {
      $room = new Room();
      $room->org_id = $id;
      $room->name = $_POST['name'];

      if ($room->save()) {
        $result['success'] = true;
        $result['message'] = 'Помещение успешно добавлено';
      }
      else {
        $errors = array();
        foreach ($room->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $this->pageHtml = $this->renderPartial('addRoomBox', array(
    ), true);
  }

  public function actionEditRoom($id) {
    $room = Room::model()->findByPk($id);
    if (!$room)
      throw new CHttpException(404, 'Помещение не найдено');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на редактирование помещения в данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на редактирование помещения в данной организации');
    }

    if (isset($_POST['name'])) {
      $room->name = $_POST['name'];

      if ($room->save(true, array('name'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($room->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $this->pageHtml = $this->renderPartial('editRoomBox', array(
      'room' => $room,
    ), true);
  }

  public function actionDeleteRoom($id) {
    $room = Room::model()->findByPk($id);
    if (!$room)
      throw new CHttpException(404, 'Помещение не найдено');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на удаление помещения в данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на удаление помещения в данной организации');
    }

    $room->delete();

    $result['success'] = true;
    $result['org_id'] = $room->org_id;
    $result['message'] = 'Помещение успешно удалено';

    echo json_encode($result);
    exit;
  }

  public function actionEditRooms($id) {
    $org = Organization::model()->with('rooms')->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на просмотр помещений в данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на просмотр помещений в данной организации');
    }

    $this->pageHtml = $this->renderPartial('editRoomsBox', array(
      'org' => $org,
    ), true);
  }
}