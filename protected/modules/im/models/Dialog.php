<?php

/**
 * This is the model class for table "dialogs".
 *
 * The followings are the available columns in table 'dialogs':
 * @property string $dialog_id
 * @property integer $leader_id
 * @property integer $type
 * @property string $title
 *
 * @property User $leader
 * @property array|DialogMember|null $members
 * @property integer $membersNum
 * @property array|DialogMessage|null $messages
 * @property DialogMessage $lastMessage
 */
class Dialog extends CActiveRecord
{
    const TYPE_TET = 0;
    const TYPE_CONFERENCE = 1;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Dialog the static model class
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
		return 'dialogs';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('leader_id, type', 'required'),
			array('leader_id, type', 'numerical', 'integerOnly'=>true),
			array('dialog_id', 'length', 'max'=>10),
			array('title', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('dialog_id, leader_id, type, title', 'safe', 'on'=>'search'),
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
      'leader' => array(self::BELONGS_TO, 'User', 'leader_id'),
      'member' => array(self::HAS_ONE, 'DialogMember', 'dialog_id'),
      'members' => array(self::HAS_MANY, 'DialogMember', 'dialog_id'),
      'membersNum' => array(self::STAT, 'DialogMember', 'dialog_id'),
      'lastMessage' => array(self::HAS_ONE, 'DialogMessage', 'dialog_id', 'order' => 'creation_date DESC'),
      'messages' => array(self::HAS_MANY, 'DialogMessage', 'dialog_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'dialog_id' => 'Dialog',
			'leader_id' => 'Leader',
			'type' => 'Type',
			'title' => 'Title',
		);
	}

    public static function getDialogs($user_id, $offset = 0) {
        $limit = Yii::app()->getModule('im')->dialogsPerPage;
        $conn = self::getDbConnection();

        $conn->createCommand("SET SESSION group_concat_max_len = 4096;")->execute();

        $command = $conn->createCommand("
SELECT * FROM `dialogs` AS t
  INNER JOIN `dialog_members` AS myself ON myself.dialog_id = t.dialog_id
  LEFT JOIN
     (
        SELECT
          message_id,
          dialog_id,
          creation_date,
          author_id,
          SUBSTR(message, 1, 90) AS message,
          attaches,
          message_delete,
          p.photo,
          p.firstname,
          u.login,
          r.req_link_id,
          r.owner_id,
          r2.req_link_id AS out_req_link_id,
          r2.owner_id AS out_owner_id
        FROM `dialog_messages` AS sub
          INNER JOIN `users` AS u ON u.id = sub.author_id
          INNER JOIN `profiles` AS p ON p.user_id = u.id
          LEFT JOIN
            (
               SELECT
                 *
               FROM `profile_requests`
               WHERE req_type = ". ProfileRequest::TYPE_PM ." AND
                 (owner_id = ". Yii::app()->user->getId() .")
            )
            AS r ON r.req_link_id = sub.message_id
          LEFT JOIN
            (
               SELECT
                 *
               FROM `profile_requests`
               WHERE req_type = ". ProfileRequest::TYPE_PM ." AND
                 (owner_id != ". Yii::app()->user->getId() .")
            )
            AS r2 ON r2.req_link_id = sub.message_id
        WHERE message_delete IS NULL
        ORDER BY message_id DESC
     )
     AS lastMessage ON lastMessage.dialog_id = t.dialog_id
  INNER JOIN
     (
       SELECT
         GROUP_CONCAT(IF(p.photo = '',0,p.photo) SEPARATOR ';') AS photos,
         GROUP_CONCAT(mm.member_id SEPARATOR ';') AS members,
         GROUP_CONCAT(p.firstname SEPARATOR ';') AS firstnames,
         GROUP_CONCAT(p.lastname SEPARATOR ';') AS lastnames,
         GROUP_CONCAT(u.login SEPARATOR ';') AS logins,
         GROUP_CONCAT(u.lastvisit SEPARATOR ';') AS lastvisits,
         dialog_id
       FROM `dialog_members` AS mm
         INNER JOIN `users` AS u ON u.id = mm.member_id
         INNER JOIN `profiles` AS p ON p.user_id = u.id
       GROUP BY dialog_id
     )
     AS members ON members.dialog_id = t.dialog_id
   WHERE myself.member_id = {$user_id} GROUP BY t.dialog_id ORDER BY lastMessage.creation_date DESC LIMIT {$offset}, {$limit}");

        $result = array();
        $dataReader = $command->query();
        while (($row = $dataReader->read()) !== false) {
            $dialog = new Dialog();
            $dialog->attributes = $row;

            $dialog->lastMessage = new DialogMessage();
            $dialog->lastMessage->attributes = $row;
            $dialog->lastMessage->author = new User();
            $dialog->lastMessage->author->attributes = $row;
            $dialog->lastMessage->author->profile = new Profile();
            $dialog->lastMessage->author->profile->attributes = $row;

            $dialog->lastMessage->isNew = new ProfileRequest();
            if ($row['req_link_id']) $dialog->lastMessage->isNew->attributes = $row;
            else {
                $dialog->lastMessage->isNew->req_link_id = $row['out_req_link_id'];
                $dialog->lastMessage->isNew->owner_id = $row['out_owner_id'];
            }

            $members = array();
            $id = explode(';', $row['members']);
            $firstname = explode(';', $row['firstnames']);
            $lastname = explode(';', $row['lastnames']);
            $photo = explode(';', $row['photos']);
            $login = explode(';', $row['logins']);
            $lastvisit = explode(';', $row['lastvisits']);

            foreach ($id as $idx => $_id) {
                $member = new DialogMember();
                $member->dialog_id = $dialog->dialog_id;
                $member->member_id = $_id;

                $member->user = new User();
                $member->user->id = $_id;
                $member->user->login = $login[$idx];
                $member->user->lastvisit = $lastvisit[$idx];

                $member->user->profile = new Profile();
                $member->user->profile->user_id = $_id;
                $member->user->profile->photo = (isset($photo[$idx])) ? $photo[$idx] : null;
                $member->user->profile->firstname = $firstname[$idx];
                $member->user->profile->lastname = $lastname[$idx];

                $members[] = $member;
            }

            $dialog->members = $members;

            $result[] = $dialog;
        }

        return $result;
    }
}