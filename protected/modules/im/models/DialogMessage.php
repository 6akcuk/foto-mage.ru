<?php

/**
 * This is the model class for table "dialog_messages".
 *
 * The followings are the available columns in table 'dialog_messages':
 * @property string $message_id
 * @property string $dialog_id
 * @property string $creation_date
 * @property integer $author_id
 * @property string $message
 * @property string $photo
 * @property string $document
 * @property string $attaches
 * @property string $message_delete
 *
 * @property User $author
 * @property Dialog $dialog
 * @property ProfileRequest $isNew
 * @property ProfileRequest $isNewIn
 * @property ProfileRequest $isNewOut
 */
class DialogMessage extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DialogMessage the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'dialog_messages';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('dialog_id, author_id, message', 'required'),
			array('author_id', 'numerical', 'integerOnly'=>true),
			array('message_id', 'length', 'max'=>20),
			array('dialog_id', 'length', 'max'=>10),
			array('creation_date, attaches, message_delete', 'safe'),
      array('message', 'filter', 'filter' => array($obj = new CHtmlPurifier(),'purify')),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('message_id, dialog_id, creation_date, author_id, message, attaches, message_delete', 'safe', 'on'=>'search'),
		);
	}

    public function defaultScope() {
        return array(
            'condition' => 'message_delete IS NULL',
        );
    }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            'author' => array(self::BELONGS_TO, 'User', 'author_id'),
            'dialog' => array(self::BELONGS_TO, 'Dialog', 'dialog_id'),
            'isNew' => array(self::HAS_ONE, 'ProfileRequest', 'req_link_id', 'condition' => 'req_type = '. ProfileRequest::TYPE_PM .' AND viewed = 0'),
            'isNewIn' => array(self::HAS_ONE, 'ProfileRequest', 'req_link_id', 'condition' => 'owner_id = '. Yii::app()->user->getId() .' AND req_type = '. ProfileRequest::TYPE_PM .' AND viewed = 0'),
            'isNewOut' => array(self::HAS_ONE, 'ProfileRequest', 'req_link_id', 'condition' => 'owner_id != '. Yii::app()->user->getId() .' AND req_type = '. ProfileRequest::TYPE_PM .' AND viewed = 0'),
        );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'message_id' => 'Message',
			'dialog_id' => 'Dialog',
			'creation_date' => 'Creation Date',
			'author_id' => 'Author',
			'message' => 'Message',
			'attaches' => 'Attaches',
			'message_delete' => 'Message Delete',
		);
	}

    public function beforeSave() {
        if (parent::beforeSave()) {
            if ($this->isNewRecord)
                $this->creation_date = date("Y-m-d H:i:s");

            return true;
        }
        else return false;
    }

  public function isIncome() {
    return $this->author_id != Yii::app()->user->getId();
  }
  public function isOutgoing() {
    return $this->author_id == Yii::app()->user->getId();
  }

  protected static function _messageReader(CDbDataReader $dataReader) {
    $result = array();

    while (($row = $dataReader->read()) !== false) {
      $message = new DialogMessage();
      $message->author_id = $row['author_id'];
      $message->creation_date = $row['creation_date'];
      $message->photo = $row['msg_photo'];
      $message->document = $row['document'];
      $message->dialog_id = $row['dialog_id'];
      $message->message = $row['message'];
      $message->message_id = $row['message_id'];
      $message->message_delete = $row['message_delete'];

      $dialog = new Dialog();
      $dialog->title = $row['title'];
      $dialog->dialog_id = $row['dialog_id'];
      $dialog->leader_id = $row['leader_id'];
      $dialog->type = $row['type'];

      /* Отправленные сообщения самому себе возвращают NULL */
      $user = (!$row['id']) ? Yii::app()->user->model : new User();
      if ($row['id']) {
        $user->id = $row['id'];
        $user->email = $row['email'];
        $user->login = $row['login'];
        $user->lastvisit = $row['lastvisit'];
      }

      $profile = (!$row['id']) ? $user->profile : new Profile();
      if ($row['id']) {
        $profile->user_id = $row['user_id'];
        $profile->photo = $row['photo'];
        $profile->firstname = $row['firstname'];
        $profile->lastname = $row['lastname'];
      }

      $request = new ProfileRequest();
      $request->req_id = $row['req_id'];
      $request->req_link_id = $row['req_link_id'];
      $request->req_type = $row['req_type'];
      $request->owner_id = $row['owner_id'];
      $request->viewed = $row['viewed'];

      $user->profile = $profile;
      $message->dialog = $dialog;
      $message->author = $user;
      $message->isNew = ($request->req_id) ? $request : null;

      $result[] = $message;
    }

    return $result;
  }

  public static function getInboxMessages($recipient_id, $offset, $c = array()) {
    /** @var $conn CDbConnection */
    $conn = Yii::app()->db;
    $command = $conn->createCommand();

    $where = $params = array();
    $where[] = 'and';
    $where[] = 'm.member_id = :mid';
    $where[] = '(msg.author_id != :aid OR (msg.author_id = :aid AND (SELECT COUNT(member_id) FROM dialog_members WHERE dialog_id = m.dialog_id AND member_id != :aid) = 0))';
    $where[] = 'msg.message_delete IS NULL';

    $params[':mid'] = $recipient_id;
    $params[':aid'] = $recipient_id;

    if (isset($c['msg'])) {
      $keyword = strtr($c['msg'], array('%'=>'\%', '_'=>'\_'));
      $where[] = array('like', 'msg.message', '%'. $keyword .'%');
    }

    if ($c['filter'] == 'new') {
      $where[] = 'req.owner_id = :oid';
      $where[] = 'req.req_type = '. ProfileRequest::TYPE_PM;

      $params[':oid'] = $recipient_id;
    }

    $command->select('*, msg.photo AS msg_photo')
      ->from('dialog_members m')
      ->join('dialogs d', 'd.dialog_id = m.dialog_id')
      ->join('dialog_messages msg', 'msg.dialog_id = m.dialog_id')
      ->join('users u', 'u.id = msg.author_id')
      ->join('profiles p', 'p.user_id = u.id')
      ->leftJoin('profile_requests req', 'req.req_link_id = msg.message_id AND req.owner_id = '. $recipient_id)
      ->where($where, $params)
      ->group('msg.message_id')
      ->order('msg.creation_date DESC')
      ->limit(Yii::app()->getModule('mail')->messagesPerPage, $offset);

    return self::_messageReader($command->query());
  }

  public static function countInboxMessages($recipient_id, $c = array()) {
    /** @var $conn CDbConnection */
    $conn = Yii::app()->db;
    $command = $conn->createCommand();

    $where = $params = array();
    $where[] = 'and';
    $where[] = 'm.member_id = :mid';
    $where[] = '(msg.author_id != :aid OR (msg.author_id = :aid AND (SELECT COUNT(member_id) FROM dialog_members WHERE dialog_id = m.dialog_id AND member_id != :aid) = 0))';
    $where[] = 'msg.message_delete IS NULL';

    $params[':mid'] = $recipient_id;
    $params[':aid'] = $recipient_id;

    if (isset($c['msg'])) {
      $keyword = strtr($c['msg'], array('%'=>'\%', '_'=>'\_'));
      $where[] = array('like', 'msg.message', '%'. $keyword .'%');
    }

    if ($c['filter'] == 'new') {
      $where[] = 'req.owner_id = :oid';
      $where[] = 'req.req_type = '. ProfileRequest::TYPE_PM;

      $params[':oid'] = $recipient_id;
    }

    $command->select('COUNT(DISTINCT msg.message_id) as num')
      ->from('dialog_members m')
      ->join('dialog_messages msg', 'msg.dialog_id = m.dialog_id')
      ->leftJoin('profile_requests req', 'req.req_link_id = msg.message_id AND req.owner_id = '. $recipient_id)
      ->where($where, $params);

    $result = $command->queryRow();
    return $result['num'];
  }

  public static function getOutboxMessages($author_id, $offset, $c = array()) {
    /** @var $conn CDbConnection */
    $conn = Yii::app()->db;
    $command = $conn->createCommand();

    $where = $params = array();
    $where[] = 'and';
    $where[] = 'msg.author_id = :aid';
    $where[] = 'msg.message_delete IS NULL';

    $params[':aid'] = $author_id;

    if (isset($c['msg'])) {
      $keyword = strtr($c['msg'], array('%'=>'\%', '_'=>'\_'));
      $where[] = array('like', 'msg.message', '%'. $keyword .'%');
    }

    $command->select('*, msg.photo AS msg_photo')
      ->from('dialog_messages msg')
      ->join('dialogs d', 'd.dialog_id = msg.dialog_id')
      ->leftJoin('dialog_members m', 'm.dialog_id = d.dialog_id AND m.member_id != '. Yii::app()->user->getId())
      ->leftJoin('users u', 'u.id = m.member_id')
      ->leftJoin('profiles p', 'p.user_id = u.id')
      ->leftJoin('profile_requests req', 'req.req_link_id = msg.message_id')
      ->where($where, $params)
      ->group('msg.message_id')
      ->order('msg.creation_date DESC')
      ->limit(Yii::app()->getModule('mail')->messagesPerPage, $offset);

    return self::_messageReader($command->query());
  }

  public static function countOutboxMessages($author_id, $c = array()) {
    /** @var $conn CDbConnection */
    $conn = Yii::app()->db;
    $command = $conn->createCommand();

    $where = $params = array();
    $where[] = 'and';
    $where[] = 'msg.author_id = :aid';
    $where[] = 'msg.message_delete IS NULL';

    $params[':aid'] = $author_id;

    if (isset($c['msg'])) {
      $keyword = strtr($c['msg'], array('%'=>'\%', '_'=>'\_'));
      $where[] = array('like', 'msg.message', '%'. $keyword .'%');
    }

    $command->select('COUNT(*) as num')
      ->from('dialog_messages msg')
      ->join('dialogs d', 'd.dialog_id = msg.dialog_id')
      ->leftJoin('dialog_members m', 'm.dialog_id = d.dialog_id AND m.member_id != '. Yii::app()->user->getId())
      ->leftJoin('users u', 'u.id = m.member_id')
      ->leftJoin('profiles p', 'p.user_id = u.id')
      ->leftJoin('profile_requests req', 'req.req_link_id = msg.message_id')
      ->where($where, $params);

    $result = $command->queryRow();
    return $result['num'];
  }

  /**
   * Отправить сообщение в существующий диалог
   *
   * @param $id               Диалог
   * @param $msg              Сообщение
   * @param array $attaches   Прикрепления к сообщению
   * @return bool|integer
   */
  public static function mail($id, $msg, $attaches = array()) {
    $message = new DialogMessage();
    $message->dialog_id = $id;
    $message->author_id = Yii::app()->user->getId();
    $message->message = $msg;
    $message->photo = (sizeof($attaches['photo'])) ? json_encode($attaches['photo']) : null;
    $message->document = (sizeof($attaches['doc'])) ? json_encode($attaches['doc']) : null;

    if ($message->save()) {
      $dialogMembers = DialogMember::model()->findAll('dialog_id = :id', array(':id' => $id));

      /** @var $member DialogMember */
      $selfRecipient = 0;
      foreach ($dialogMembers as $member) {
        // Можно отправлять самому себе сообщения
        if ($selfRecipient == 0 && $member->member_id == Yii::app()->user->getId()) {
          $selfRecipient++;
        }
        else {
          $request = new ProfileRequest();
          $request->owner_id = $member->member_id;
          $request->req_type = ProfileRequest::TYPE_PM;
          $request->req_link_id = $message->message_id;
          $request->save();
        }
      }

      return $message->message_id;
    }
    else return false;
  }
  /**
   * Отправить сообщение (Instant Message)
   *
   * @param $recipients       Получатели
   * @param $message          Текст сообщения
   * @param string $title     Заголовок беседы (конференции)
   * @param array $attaches   Прикрепления к сообщению
   */
  public static function send($recipients, $message, $title = '', $attaches = array()) {
    // Создание беседы (конференции)
    if (sizeof($recipients) > 1) {
      $dialog = new Dialog();
      $dialog->leader_id = Yii::app()->user->getId();
      $dialog->title = $title;
      $dialog->type = Dialog::TYPE_CONFERENCE;
      if (!$dialog->save())
        return array('success' => false, 'message' => 'Не удалось создать беседу');

      // Members
      $dialogMembers = array();
      $memberSuccessful = false;

      for ($idx = 0; $idx < sizeof($recipients); $idx++) {
        $dialogMembers[$idx] = new DialogMember();
        $dialogMembers[$idx]->dialog_id = $dialog->dialog_id;
        $dialogMembers[$idx]->member_id = $recipients[$idx];

        $memberSuccessful = $dialogMembers[$idx]->validate();
      }

      $idx = sizeof($recipients);
      $dialogMembers[$idx] = new DialogMember();
      $dialogMembers[$idx]->dialog_id = $dialog->dialog_id;
      $dialogMembers[$idx]->member_id = Yii::app()->user->getId();

      $memberSuccessful = $dialogMembers[$idx]->validate();

      if (!$memberSuccessful) {
        $dialog->delete();
        return array('success' => false, 'message' => 'Собеседники не прошли проверку валидации');
      }

      foreach ($dialogMembers as &$member) {
        $member->save();
      }
    }
    elseif (sizeof($recipients) == 1) {
      $criteria = new CDbCriteria();
      $criteria->condition = 'twin.member_id = :user AND t.member_id = :id AND dialog.type = :type';
      $criteria->params[':user'] = Yii::app()->user->getId();
      $criteria->params[':id'] = $recipients[0];
      $criteria->params[':type'] = Dialog::TYPE_TET;

      /** @var $db CDbConnection */
      $db = Yii::app()->db;
      $command = $db->createCommand("
SELECT dialog.* FROM `dialog_members` AS t
  INNER JOIN `dialog_members` AS twin ON twin.dialog_id = t.dialog_id
  INNER JOIN `dialogs` AS dialog ON dialog.dialog_id = t.dialog_id
WHERE twin.member_id = ". Yii::app()->user->getId() ." AND t.member_id = ". intval($recipients[0]) ."
  AND dialog.type = ". Dialog::TYPE_TET);
      $row = $command->queryRow();

      //$dialogMembers = DialogMember::model()->with('twin', 'dialog')->findAll($criteria);
      if (!$row) {
        $dialog = new Dialog();
        $dialog->leader_id = Yii::app()->user->getId();
        $dialog->type = Dialog::TYPE_TET;
        if (!$dialog->save())
          return array('success' => false, 'message' => 'Не удалось создать диалог с пользователем');

        // Members
        $dialogMembers = array();
        $memberSuccessful = false;

        $dialogMembers[0] = new DialogMember();
        $dialogMembers[0]->dialog_id = $dialog->dialog_id;
        $dialogMembers[0]->member_id = $recipients[0];

        $memberSuccessful = $dialogMembers[0]->validate();

        $idx = sizeof($dialogMembers);
        $dialogMembers[$idx] = new DialogMember();
        $dialogMembers[$idx]->dialog_id = $dialog->dialog_id;
        $dialogMembers[$idx]->member_id = Yii::app()->user->getId();

        $memberSuccessful = $dialogMembers[$idx]->validate();

        if (!$memberSuccessful) {
          $dialog->delete();
          return array('success' => false, 'message' => 'Собеседники не прошли проверку валидации');
        }

        foreach ($dialogMembers as &$member) {
          $member->save();
        }
      }
      else {
        $dialog = new Dialog();
        $dialog->dialog_id = $row['dialog_id'];

        $dialogMembers = DialogMember::model()->findAll('dialog_id = :id', array(':id' => $dialog->dialog_id));
      }
    }
    else
      return array('success' => false, 'message' => 'Вы не выбрали получателя сообщения');

    $dialogMessage = new DialogMessage();
    $dialogMessage->dialog_id = $dialog->dialog_id;
    $dialogMessage->author_id = Yii::app()->user->getId();
    $dialogMessage->message = $message;
    $dialogMessage->photo = (sizeof($attaches['photo'])) ? json_encode($attaches['photo']) : null;
    $dialogMessage->document = (sizeof($attaches['doc'])) ? json_encode($attaches['doc']) : null;

    if (!$dialogMessage->save()) {
      return array('success' => false, 'message' => 'Сообщение не было доставлено');
    }
    else {
      /** @var $member DialogMember */
      $selfRecipient = 0;
      $length = sizeof($dialogMembers);
      for ($i=0; $i < $length; $i++) {
        $member = $dialogMembers[$i];

        // Можно отправлять самому себе сообщения
        if ($member->member_id == Yii::app()->user->getId()) {
          $selfRecipient++;
        }
        else {
          $request = new ProfileRequest();
          $request->owner_id = $member->member_id;
          $request->req_type = ProfileRequest::TYPE_PM;
          $request->req_link_id = $dialogMessage->message_id;
          $request->save();
        }
      }

      return array(
        'success' => true,
        'url' => '/im?sel='. $dialog->dialog_id,
        'msg' => 'Ваше сообщение успешно отправлено',
      );
    }
  }
}