<?php

/**
 * This is the model class for table "profile_wall_posts".
 *
 * The followings are the available columns in table 'profile_wall_posts':
 * @property string $post_id
 * @property integer $wall_id
 * @property integer $author_id
 * @property string $add_date
 * @property string $reply_to
 * @property integer $reply_to_id
 * @property string $post
 * @property string $attaches
 * @property integer $reference_id
 * @property string $reference_type
 * @property string $post_delete
 *
 * @property User $author
 * @property array|ProfileWallPost $last_replies
 * @property array|ProfileWallPost $replies
 * @property Profile $replyTo
 * @property integer $repliesNum
 */
class ProfileWallPost extends CActiveRecord
{
  const REF_TYPE_PURCHASE = 'purchase';
  const REF_TYPE_GOOD = 'good';

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ProfileWallPost the static model class
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
		return 'profile_wall_posts';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
      array('wall_id, author_id', 'required'),
			array('post', 'required', 'on' => 'secure'),
			array('wall_id, author_id, reply_to, reference_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('post_id, wall_id, author_id, add_date, post, attaches', 'safe', 'on'=>'search'),
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
      'last_replies' => array(self::HAS_MANY, 'ProfileWallPost', 'reply_to', 'condition' => 'last_replies.post_delete IS NULL', 'order' => 'last_replies.add_date DESC', 'limit' => 3),
      'replies' => array(self::HAS_MANY, 'ProfileWallPost', 'reply_to', 'condition' => 'replies.post_delete IS NULL'),
      'replyTo' => array(self::BELONGS_TO, 'Profile', 'reply_to_id', 'joinType' => 'LEFT JOIN'),
      'repliesNum' => array(self::STAT, 'ProfileWallPost', 'reply_to', 'condition' => 'post_delete IS NULL'),
      'replyPost' => array(self::BELONGS_TO, 'ProfileWallPost', 'reply_to', 'condition' => 'replyPost.post_delete IS NULL'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'post_id' => 'Post',
			'wall_id' => 'Wall',
			'author_id' => 'Author',
			'add_date' => 'Add Date',
			'post' => 'Post',
			'attaches' => 'Attaches',
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
		$criteria->compare('wall_id',$this->wall_id);
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('add_date',$this->add_date,true);
		$criteria->compare('post',$this->post,true);
		$criteria->compare('attaches',$this->attaches,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public function beforeSave() {
    if (parent::beforeSave()) {
      if ($this->getIsNewRecord())
        $this->add_date = date("Y-m-d H:i:s");

      return true;
    }
    else return false;
  }

  public function afterSave() {
    if ($this->isNewRecord) {
      if ($this->reply_to_id) {
        $req = new ProfileRequest();
        $req->req_type = ProfileRequest::TYPE_WALL_ANSWER;
        $req->owner_id = $this->reply_to_id;
        $req->req_link_id = $this->post_id;
        $req->save();
      }

      if (!$this->reply_to > 0) {
        $req = new ProfileRequest();
        $req->req_type = ProfileRequest::TYPE_POST_ON_WALL;
        $req->owner_id = $this->wall_id;
        $req->req_link_id = $this->post_id;
        $req->save();
      }

      $feed = new Feed();
      $feed->owner_type = ($this->reply_to) ? 'post' : 'user';
      $feed->owner_id = ($this->reply_to) ? $this->reply_to : $this->wall_id;
      $feed->event_type = ($this->reply_to) ? 'new reply' : 'new post';
      $feed->event_link_id = $this->post_id;
      $feed->save();

      $c = new CDbCriteria();
      $c->compare('user_id', Yii::app()->user->getId());
      $c->compare('sub_type', Subscription::TYPE_WALL_POST);
      $c->compare('sub_link_id', ($this->reply_to) ? $this->reply_to : $this->post_id);

      $sub = Subscription::model()->find($c);
      if (!$sub) {
        $sub = new Subscription();
        $sub->user_id = Yii::app()->user->getId();
        $sub->sub_type = Subscription::TYPE_WALL_POST;
        $sub->sub_link_id = ($this->reply_to) ? $this->reply_to : $this->post_id;
        $sub->save();
      }
    }
  }

  public function markAsDeleted() {
    $this->post_delete = date("Y-m-d H:i:s");
    $result = $this->save(true, array('post_delete'));

    $cr = new CDbCriteria();
    $cr->compare('owner_type', 'user');
    $cr->compare('owner_id', $this->wall_id);
    $cr->compare('event_type', ($this->reply_to) ? 'new reply' : 'new post');
    $cr->compare('event_link_id', $this->post_id);

    $feed = Feed::model()->find($cr);
    if ($feed) $feed->markAsDeleted();

    if (!$this->reply_to) {
      $cr = new CDbCriteria();
      $cr->compare('req_type', ProfileRequest::TYPE_POST_ON_WALL);
      $cr->compare('owner_id', $this->wall_id);
      $cr->compare('req_link_id', $this->post_id);

      $req = ProfileRequest::model()->find($cr);
      if ($req) $req->delete();
    }

    if ($this->reply_to_id) {
      $cr = new CDbCriteria();
      $cr->compare('req_type', ProfileRequest::TYPE_WALL_ANSWER);
      $cr->compare('owner_id', $this->reply_to_id);
      $cr->compare('req_link_id', $this->post_id);

      $req = ProfileRequest::model()->find($cr);
      if ($req) $req->delete();
    }

    return $result;
  }

  public function restore() {
    $this->post_delete = null;
    $result = $this->save(true, array('post_delete'));

    $cr = new CDbCriteria();
    $cr->compare('owner_type', 'user');
    $cr->compare('owner_id', $this->wall_id);
    $cr->compare('event_type', ($this->reply_to) ? 'new reply' : 'new post');
    $cr->compare('event_link_id', $this->post_id);

    $feed = Feed::model()->find($cr);
    if ($feed) $feed->restore();

    if (!$this->reply_to) {
      $req = new ProfileRequest();
      $req->req_type = ProfileRequest::TYPE_POST_ON_WALL;
      $req->owner_id = $this->wall_id;
      $req->req_link_id = $this->post_id;
      $req->save();
    }

    if ($this->reply_to_id) {
      $req = new ProfileRequest();
      $req->req_type = ProfileRequest::TYPE_WALL_ANSWER;
      $req->owner_id = $this->reply_to_id;
      $req->req_link_id = $this->post_id;
      $req->save();
    }

    return $result;
  }
}