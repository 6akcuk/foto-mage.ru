<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout = '//layouts/intro';
  public $breadcrumbs = array();
  public $menu = array();

  public $pageCounters = null;
  public $pageHtml = '';
  public $pageWidth = 791;
  public $pageJS = '';
  public $boxWidth = 0;

  public $json = array();

  public function init() {
    // Ticket authorization
    if (isset($_GET['ticket_id']) && isset($_GET['token'])) {
      $ticket = UserTicket::model()->findByPk($_GET['ticket_id']);
      if ($ticket->token == $_GET['token']) {
        $identity = new UserIdentity('', '');
        $identity->id = $ticket->user_id;
        Yii::app()->user->login($identity);

        $ticket->delete();

        if (isset($_GET['url'])) $this->redirect($_GET['url']);
      }
    }

    if (!Yii::app()->user->getIsGuest()) {
      if (Yii::app()->user->checkAccess('users.users.index')) {
        //$this->pageCounters['users'] = User::model()->count();
      }

      $criteria = new CDbCriteria();
      $criteria->addCondition('owner_id = :id');
      $criteria->addCondition('viewed = 0');
      $criteria->addCondition('req_type = :type');
      $criteria->params[':id'] = Yii::app()->user->getId();

      $criteria->params[':type'] = ProfileRequest::TYPE_FRIEND;
      $this->pageCounters['friends'] = ProfileRequest::model()->count($criteria);

      $criteria->params[':type'] = ProfileRequest::TYPE_PM;
      $this->pageCounters['pm'] = ProfileRequest::model()->count($criteria);

      $this->layout = '//layouts/main';
    }
  }
}