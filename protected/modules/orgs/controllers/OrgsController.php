<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 16.10.12
 * Time: 20:41
 * To change this template use File | Settings | File Templates.
 */

class OrgsController extends Controller {
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

  public function actionIndex($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('orgs')->orgsPerPage;
    $criteria->offset = $offset;

    if (isset($c['name']) && $c['name']) {
      $criteria->addSearchCondition('t.name', $c['name'], true);
    }

    if (isset($c['city_id']) && $c['city_id']) {
      $criteria->compare('t.city_id', $c['city_id']);
    }

    if (isset($c['org_type_id']) && $c['org_type_id']) {
      $types = explode(",", $c['org_type_id']);
      if (!is_array($types)) $types = array($types);

      $criteria->addInCondition('typelink.org_type_id', $types);

      $tCriteria = new CDbCriteria();
      $tCriteria->addInCondition('type_id', $types);
      $curTypes = OrganizationType::model()->findAll($tCriteria);
    } else $curTypes = array();

    // Ограничение по городу
    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City')) {
      $criteria->compare('t.city_id', Yii::app()->user->model->profile->city_id);
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Moderate')) {
      $orgs_list = array();
      $links = UserOrgLink::model()->findAll('user_id = :id', array(':id' => Yii::app()->user->getId()));
      foreach ($links as $link) {
        $orgs_list[] = $link->org_id;
      }

      $criteria->addInCondition('t.org_id', $orgs_list);
    }

    $criteria->order = 't.name';

    $orgs = Organization::model()->with('types', 'typelink')->findAll($criteria);
    $orgsNum = Organization::model()->with('types', 'typelink')->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_orglist', array('orgs' => $orgs, 'offset' => $offset), true);
      }
      else $this->pageHtml = $this->renderPartial('index', array(
        'orgs' => $orgs,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $orgsNum,
        'types' => $curTypes,
      ), true);
    }
    else $this->render('index', array(
      'orgs' => $orgs,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $orgsNum,
      'types' => $curTypes,
    ));
  }

  public function actionAdd() {
    $org = new Organization('add');

    // collect user input data
    if(isset($_POST['name']))
    {
      //$org->org_type_id = $_POST['org_type_id'];
      $org->name = $_POST['name'];
      $org->city_id =
        (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
          ? Yii::app()->user->model->profile->city_id
          : $_POST['city_id'];
      $org->address = $_POST['address'];
      $org->phone = preg_replace('#[^0-9]#', '', $_POST['phone']);
      $org->worktimes = $_POST['worktimes'];
      $org->shortstory = $_POST['shortstory'];
      $org->photo = $_POST['photo'];

      $result = array();

      if($org->save()) {
        $new_types = explode(",", $_POST['org_type_id']);
        foreach ($new_types as $nt) {
          $type = new OrganizationTypeLink();
          $type->org_id = $org->org_id;
          $type->org_type_id = $nt;
          $type->save();
        }

        $result['success'] = true;
        $result['message'] = 'Организация успешно добавлена';
      }
      else {
        $errors = array();
        foreach ($org->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('addBox', array('org' => $org), true);
      }
      else $this->pageHtml = $this->renderPartial('add', array('org' => $org), true);
    }
    else $this->render('add', array('org' => $org));
  }

  public function actionEdit($id) {
    /** @var Organization $org */
    $org = Organization::model()->with('types')->findByPk($id);

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на редактирование данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на редактирование данной организации');
    }

    // collect user input data
    if(isset($_POST['name']))
    {
      //$org->org_type_id = $_POST['org_type_id'];
      $new_types = explode(",", $_POST['org_type_id']);
      /** @var OrganizationTypeLink $type */
      foreach ($org->types as $type) {
        if (!in_array($type->org_type_id, $new_types)) {
          $type->delete();
          array_splice($new_types, array_search($type->org_type_id, $new_types), 1);
        } elseif (in_array($type->org_type_id, $new_types)) {
          array_splice($new_types, array_search($type->org_type_id, $new_types), 1);
        }
      }

      foreach ($new_types as $nt) {
        $type = new OrganizationTypeLink();
        $type->org_id = $org->org_id;
        $type->org_type_id = $nt;
        $type->save();
      }

      $org->name = $_POST['name'];
      $org->city_id =
        (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
          ? Yii::app()->user->model->profile->city_id
          : $_POST['city_id'];
      $org->address = $_POST['address'];
      $org->phone = preg_replace('#[^0-9]#', '', $_POST['phone']);
      $org->worktimes = $_POST['worktimes'];
      $org->shortstory = $_POST['shortstory'];
      $org->photo = $_POST['photo'];

      $result = array();

      if($org->save(true, array('name', 'city_id', 'address', 'phone', 'worktimes', 'shortstory', 'photo'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($org->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $types = array();
    foreach ($org->types as $type) {
      $types[] = $type->org_type_id;
    }

    $tCriteria = new CDbCriteria();
    $tCriteria->addInCondition('type_id', $types);
    $orgTypes = OrganizationType::model()->findAll($tCriteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editBox', array('org' => $org, 'types' => $types, 'orgTypes' => $orgTypes), true);
      }
      else $this->pageHtml = $this->renderPartial('edit', array('org' => $org, 'types' => $types, 'orgTypes' => $orgTypes), true);
    }
    else $this->render('edit', array('org' => $org, 'types' => $types, 'orgTypes' => $orgTypes));
  }

  public function actionDelete($id) {
    $org = Organization::model()->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на удаление данной организации');
    }

    $org->delete();

    echo json_encode(array('message' => 'Организация успешно удалена'));
    exit;
  }

  public function actionImport($offset = 0) {
    $criteria = new CDbCriteria();
    $criteria->compare('type', ImportData::TYPE_ORG);
    $criteria->offset = $offset;
    $criteria->limit = Yii::app()->getModule('orgs')->importPerPage;
    $criteria->order = 'id DESC';

    $import = ImportData::model()->findAll($criteria);
    $importNum = ImportData::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('import', array(
        'import' => $import,
        'offsets' => $importNum,
        'offset' => $offset,
      ), true);
    }
    else $this->render('import', array(
      'import' => $import,
      'offsets' => $importNum,
      'offset' => $offset,
    ));
  }

  public function actionImportRun($id) {
    /** @var ImportData $import */
    $import = ImportData::model()->findByPk($id);
    if (!$import)
      throw new CHttpException(404, 'Файл не найден');

    $import->status = ImportData::STATUS_PROCESS;
    $import->save(true, array('status'));

    $data = json_decode($import->data, true);
    if ($data['source'] == '2gis.csv') {
      $catcache = array();

      try {
        $file = new SplFileObject('/var/www/protected/data/import_org/'. $import->filename);
      }
      catch (RuntimeException $e) {
        throw new CHttpException(500, 'Не удается открыть файл данных');
      }

      $file->seek($import->completed + 1);
      $counter = $import->completed;
      while (!$file->eof()) {
        if ($file->key() == 0) {
          $file->next();
          continue;
        }

        $org_data = explode(";", $file->current());
        $categories = explode(",", $org_data[2]);

        $org_name = iconv("windows-1251", "utf-8", $org_data[0]);
        $org_address = iconv("windows-1251", "utf-8", $org_data[4]) .", ". iconv("windows-1251", "utf-8", $org_data[5]);
        $lat = str_replace(",", ".", $org_data[8]);
        $lon = str_replace(",", ".", $org_data[9]);

        $sql = "INSERT INTO `organizations` (`name`, `city_id`, `address`, `lat`, `lon`)";
        $sql .= "VALUES ('". $org_name ."', ". $data['city_id'] .", '". $org_address ."', '". $lat ."', '". $lon ."')";

        /** @var $db CDbConnection */
        $db = Yii::app()->db;
        $db->createCommand($sql)->execute();

        $org_id = $db->lastInsertID;

        foreach ($categories as $category) {
          $category = iconv("windows-1251", "utf-8", $category);

          if (!isset($catcache[$category])) {
            /** @var OrganizationType $cat */
            $cat = OrganizationType::model()->find('type_name = :name', array(':name' => $category));
            if (!$cat) {
              $cat = new OrganizationType();
              $cat->type_name = $category;
              $cat->afisha = 0;
              $cat->save();
            }

            $catcache[$category] = $cat->type_id;
          }

          $link = new OrganizationTypeLink();
          $link->org_id = $org_id;
          $link->org_type_id = $catcache[$category];
          $link->save();
        }

        $db->createCommand("UPDATE `import_data` SET `completed` = `completed` + 1 WHERE id = ". $id)->execute();

        $file->next();

        /*echo ++$counter ."<!>";
        ob_flush();
        flush();*/

        //if ($counter == 5) Yii::app()->end();
      }
    }
  }

  public function actionImportStat($id) {
    /** @var ImportData $import */
    $import = ImportData::model()->findByPk($id);
    if (!$import)
      throw new CHttpException(404, 'Файл не найден');

    echo json_encode(array(
      'completed' => $import->completed,
    ));
    exit;
  }

  public function actionImportBox() {
    $cities = City::model()->findAll();
    $import = new ImportData();

    if (isset($_POST['city_id'])) {
      $city_id = intval($_POST['city_id']);
      $source = trim($_POST['source']);

      if (move_uploaded_file($_FILES['filedata']['tmp_name'], '/var/www/protected/data/import_org/'. $_FILES['filedata']['name'])) {
        $import->author_id = Yii::app()->user->getId();
        $import->data = json_encode(array(
          'city_id' => $city_id,
          'source' => $source,
        ));
        $import->filename = $_FILES['filedata']['name'];
        $import->type = ImportData::TYPE_ORG;

        if ($source == '2gis.csv') {
          $linecount = 0;
          $handle = fopen('/var/www/protected/data/import_org/'. $_FILES['filedata']['name'], "r");
          while(!feof($handle)){
            $line = fgets($handle);
            $linecount++;
          }
          fclose($handle);

          $import->total = $linecount - 2;
        }

        $import->save();
      }

      $this->redirect('/orgs/orgs/import');
    }

    $this->pageHtml = $this->renderPartial('importBox', array(
      'cities' => $cities,
      'import' => $import,
    ), true);
  }
}