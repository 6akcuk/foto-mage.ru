<?php

/**
 * This is the model class for table "discuss_themes".
 *
 * The followings are the available columns in table 'discuss_themes':
 * @property integer $theme_id
 * @property integer $forum_id
 * @property integer $author_id
 * @property string $title
 * @property integer $fixed
 * @property integer $closed
 *
 * @property User $author
 * @property DiscussPost $lastPost
 * @property integer $postsNum
 */
class DiscussTheme extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DiscussTheme the static model class
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
		return 'discuss_themes';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('forum_id, author_id, title', 'required'),
			array('forum_id, author_id, fixed, closed', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>200),
      array('title', 'filter', 'filter' => array($obj = new CHtmlPurifier(),'purify')),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('theme_id, forum_id, author_id, title, fixed, closed', 'safe', 'on'=>'search'),
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
      'forum' => array(self::BELONGS_TO, 'DiscussForum', 'forum_id'),
      'author' => array(self::BELONGS_TO, 'User', 'author_id'),
      'lastPost' => array(self::HAS_ONE, 'DiscussPost', 'theme_id'),
      'postsNum' => array(self::STAT, 'DiscussPost', 'theme_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'theme_id' => 'Theme',
			'forum_id' => 'Forum',
			'author_id' => 'Author',
			'title' => 'Title',
			'fixed' => 'Fixed',
			'closed' => 'Closed',
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

		$criteria->compare('theme_id',$this->theme_id);
		$criteria->compare('forum_id',$this->forum_id);
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('fixed',$this->fixed);
		$criteria->compare('closed',$this->closed);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public function destroyTheme()
  {
    /** @var CDbConnection $db */
    $db = Yii::app()->db;

    // Удалить фиды в ленте новостей
    $command2 = $db->createCommand("
      DELETE FROM `feed`
        WHERE event_type = 'new theme post'
        AND event_link_id IN (SELECT post_id FROM `discuss_posts` WHERE theme_id = ". $this->theme_id .")");
    $command2->query();

    // Удалить все посты в теме
    $command3 = $db->createCommand("
      DELETE FROM `discuss_posts` WHERE theme_id = ". $this->theme_id ."");
    $command3->query();

    $this->delete();
  }
}