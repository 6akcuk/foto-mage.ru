<?php

/**
 * This is the model class for table "discuss_posts".
 *
 * The followings are the available columns in table 'discuss_posts':
 * @property string $post_id
 * @property integer $forum_id
 * @property integer $theme_id
 * @property integer $author_id
 * @property string $post
 * @property integer $reply_to
 * @property string $add_date
 * @property string $attaches
 * @property string $post_ondelete
 *
 * @property User $author
 * @property DiscussTheme $theme
 * @property DiscussPost $reply
 */
class DiscussPost extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DiscussPost the static model class
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
		return 'discuss_posts';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('forum_id, theme_id, author_id, post', 'required'),
			array('theme_id, author_id, reply_to', 'numerical', 'integerOnly'=>true),
			array('post_ondelete', 'safe'),
      array('post', 'filter', 'filter' => array($obj = new CHtmlPurifier(),'purify')),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('post_id, theme_id, author_id, post, reply_to, add_date, attaches, post_ondelete', 'safe', 'on'=>'search'),
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
      'theme' => array(self::BELONGS_TO, 'DiscussTheme', 'theme_id'),
      'reply' => array(self::BELONGS_TO, 'DiscussPost', 'reply_to'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'post_id' => 'Post',
			'theme_id' => 'Theme',
			'author_id' => 'Author',
			'post' => 'Post',
			'reply_to' => 'Reply To',
			'add_date' => 'Add Date',
			'attaches' => 'Attaches',
			'post_ondelete' => 'Post Ondelete',
		);
	}

  public function defaultScope() {
    return array(
      'condition' => 'post_ondelete IS NULL',
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

		$criteria->compare('post_id',$this->post_id,true);
		$criteria->compare('theme_id',$this->theme_id);
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('post',$this->post,true);
		$criteria->compare('reply_to',$this->reply_to);
		$criteria->compare('add_date',$this->add_date,true);
		$criteria->compare('attaches',$this->attaches,true);
		$criteria->compare('post_ondelete',$this->post_ondelete,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public function beforeSave() {
    if (parent::beforeSave()) {
      if ($this->isNewRecord)
        $this->add_date = date("Y-m-d H:i:s");

      return true;
    }
    else
      return false;
  }

  public function afterSave() {
    if ($this->isNewRecord) {
      /* Лучше не захламлять ответами форума
      if ($this->reply_to) {
        $req = new ProfileRequest();
        $req->req_type = ProfileRequest::TYPE_DISCUSS_POST_ANSWER;
        $req->owner_id = $this->reply->author_id;
        $req->req_link_id = $this->post_id;
        $req->save();
      }
      */

      $feed = new Feed();
      $feed->owner_type = 'theme';
      $feed->owner_id = $this->theme_id;
      $feed->event_type = 'new theme post';
      $feed->event_link_id = $this->post_id;
      $feed->save();

      $c = new CDbCriteria();
      $c->compare('user_id', Yii::app()->user->getId());
      $c->compare('sub_type', Subscription::TYPE_THEME_POST);
      $c->compare('sub_link_id', $this->theme_id);

      $sub = Subscription::model()->find($c);
      if (!$sub) {
        $sub = new Subscription();
        $sub->user_id = Yii::app()->user->getId();
        $sub->sub_type = Subscription::TYPE_THEME_POST;
        $sub->sub_link_id = $this->theme_id;
        $sub->save();
      }
    }
  }

  public function markAsDeleted() {
    $this->post_ondelete = date("Y-m-d H:i:s");
    $result = $this->save(true, array('post_ondelete'));

    $cr = new CDbCriteria();
    $cr->compare('owner_type', 'theme');
    $cr->compare('owner_id', $this->theme_id);
    $cr->compare('event_type', 'new theme post');
    $cr->compare('event_link_id', $this->post_id);

    $feed = Feed::model()->find($cr);
    if ($feed) $feed->markAsDeleted();
    /*
    if ($this->reply_to) {
      $cr = new CDbCriteria();
      $cr->compare('req_type', ProfileRequest::TYPE_DISCUSS_POST_ANSWER);
      $cr->compare('owner_id', $this->reply->author_id);
      $cr->compare('req_link_id', $this->post_id);

      $req = ProfileRequest::model()->find($cr);
      if ($req) $req->delete();
    }
    */

    return $result;
  }

  public function restore() {
    $this->post_ondelete = null;
    $result = $this->save(true, array('post_ondelete'));

    $cr = new CDbCriteria();
    $cr->compare('owner_type', 'theme');
    $cr->compare('owner_id', $this->theme_id);
    $cr->compare('event_type', 'new theme post');
    $cr->compare('event_link_id', $this->post_id);

    $feed = Feed::model()->find($cr);
    if ($feed) $feed->restore();

    /*
    if ($this->reply_to) {
      $req = new ProfileRequest();
      $req->req_type = ProfileRequest::TYPE_DISCUSS_POST_ANSWER;
      $req->owner_id = $this->reply->author_id;
      $req->req_link_id = $this->post_id;
      $req->save();
    }
    */

    return $result;
  }

  public function massDeletePostsByAuthor() {
    /** @var CDbConnection $db */
    $db = Yii::app()->db;

    // Удалить все ответы на форуме
    /*$command1 = $db->createCommand('
      DELETE FROM `profile_requests`
        WHERE req_type = '. ProfileRequest::TYPE_DISCUSS_POST_ANSWER .'
        AND req_link_id IN (SELECT post_id FROM `discuss_posts` WHERE add_date >= (NOW() - INTERVAL 1 DAY) AND theme_id = '. $this->theme_id .' AND author_id = '. $this->author_id .')');
    $command1->query();
    */

    // Удалить фиды в ленте новостей
    $command2 = $db->createCommand("
      DELETE FROM `feed`
        WHERE event_type = 'new theme post'
        AND event_link_id IN (SELECT post_id FROM `discuss_posts` WHERE add_date >= (NOW() - INTERVAL 1 DAY) AND theme_id = ". $this->theme_id ." AND author_id = ". $this->author_id .")");
    $command2->query();

    // Удалить все посты на форуме
    $command3 = $db->createCommand("
      DELETE FROM `discuss_posts` WHERE add_date >= (NOW() - INTERVAL 1 DAY) AND theme_id = ". $this->theme_id ." AND author_id = ". $this->author_id ."");
    $command3->query();
  }
}