<?php

/**
 * This is the model class for table "discuss_forums".
 *
 * The followings are the available columns in table 'discuss_forums':
 * @property integer $forum_id
 * @property integer $parent_id
 * @property string $title
 * @property string $description
 * @property integer $access_rights
 * @property integer $access_city
 * @property string $icon
 *
 * @property array|DiscussForum $subforums
 * @property array|DiscussTheme $themes
 * @property integer $themesNum
 * @property integer $postsNum
 */
class DiscussForum extends CActiveRecord
{
  public static $rightsArray = array(
    0 => 'Нет ограничения',
    1 => 'Не ниже группы организатора',
    2 => 'Не ниже группы модератора',
    3 => 'Не ниже группы администратора',
  );

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DiscussForum the static model class
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
		return 'discuss_forums';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('title', 'required'),
			array('parent_id, access_rights', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>100),
			array('description', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('forum_id, parent_id, title, description, access_rights, icon', 'safe', 'on'=>'search'),
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
      'subforums' => array(
        self::HAS_MANY,
        'DiscussForum',
        'parent_id',
        'condition' => '(subforums.access_city = 0 OR subforums.access_city = :city) AND (subforums.access_rights <= :rights)',
        'params' => array(':city' => self::getUserCity(), ':rights' => self::getNumericRight()),
        'joinType' => 'LEFT JOIN',
      ),
      'themes' => array(self::HAS_MANY, 'DiscussTheme', 'forum_id'),
      'themesNum' => array(self::STAT, 'DiscussTheme', 'forum_id'),
      'postsNum' => array(self::STAT, 'DiscussPost', 'forum_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'forum_id' => 'Forum',
			'parent_id' => 'Parent',
			'title' => 'Title',
			'description' => 'Description',
			'access_rights' => 'Access Rights',
			'icon' => 'Icon',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('forum_id',$this->forum_id);
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('access_rights',$this->access_rights);
		$criteria->compare('icon',$this->icon,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public static function getUserCity()
  {
    $cookies = Yii::app()->getRequest()->getCookies();
    return ($cookies['cur_city']) ? $cookies['cur_city']->value : Yii::app()->user->model->profile->city_id;
  }

  public static function getNumericRight()
  {
    switch (Yii::app()->user->model->role->itemname) {
      case 'Пользователь':
        return 0;
        break;
      case 'Организатор':
        return 1;
        break;
      case 'Модератор':
        return 2;
        break;
      case 'Администратор':
        return 3;
        break;
    }
  }

  public static function getRootForums()
  {
    $dataArray = array(0 => 'Нет родительского форума');
    $forums = self::model()->findAll('parent_id IS NULL');
    foreach ($forums as $forum) {
      $dataArray[$forum->forum_id] = $forum->title;
    }

    return $dataArray;
  }

  public function destroyForum()
  {
    /** @var CDbConnection $db */
    $db = Yii::app()->db;

    // Удалить все ответы на форуме
    $command1 = $db->createCommand('
      DELETE FROM `profile_requests`
        WHERE req_type = '. ProfileRequest::TYPE_DISCUSS_POST_ANSWER .'
        AND req_link_id IN (SELECT post_id FROM `discuss_posts` WHERE forum_id = '. $this->forum_id .')');
    $command1->query();

    // Удалить фиды в ленте новостей
    $command2 = $db->createCommand("
      DELETE FROM `feed`
        WHERE event_type = 'new theme post'
        AND event_link_id IN (SELECT post_id FROM `discuss_posts` WHERE forum_id = ". $this->forum_id .")");
    $command2->query();

    // Удалить все посты на форуме
    $command3 = $db->createCommand("
      DELETE FROM `discuss_posts` WHERE forum_id = ". $this->forum_id ."");
    $command3->query();

    // Удалить все темы на форуме
    $command4 = $db->createCommand("
      DELETE FROM `discuss_themes` WHERE forum_id = ". $this->forum_id ."");
    $command4->query();

    $this->delete();
  }
}