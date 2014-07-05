<?php

/**
 * This is the model class for table "feed".
 *
 * The followings are the available columns in table 'feed':
 * @property string $feed_id
 * @property string $feed_ondelete
 * @property string $add_date
 * @property string $owner_type
 * @property string $owner_id
 * @property string $event_type
 * @property string $event_link_id
 * @property string $event_text
 *
 * @property mixed $content
 * @property integer $viewed
 */
class Feed extends CActiveRecord
{
  public $content;
  public $viewed;
  public $goods_num;
  public $goods;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Feed the static model class
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
		return 'feed';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('owner_type, owner_id, event_type, event_link_id', 'required'),
			array('owner_type, event_type', 'length', 'max'=>40),
			array('owner_id, event_link_id', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('feed_id, add_date, owner_type, owner_id, event_type, event_link_id', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'feed_id' => 'Feed',
			'add_date' => 'Add Date',
			'owner_type' => 'Owner Type',
			'owner_id' => 'Owner',
			'event_type' => 'Event Type',
			'event_link_id' => 'Event Link',
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

		$criteria->compare('feed_id',$this->feed_id,true);
		$criteria->compare('add_date',$this->add_date,true);
		$criteria->compare('owner_type',$this->owner_type,true);
		$criteria->compare('owner_id',$this->owner_id,true);
		$criteria->compare('event_type',$this->event_type,true);
		$criteria->compare('event_link_id',$this->event_link_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public static function countFeeds($user_id, $c = array()) {
    /** @var CDbConnection $db */
    $db = Yii::app()->db;
    /*
    $command1 = $db->createCommand("
    SELECT
    (
      (
        SELECT COUNT(f.feed_id) AS num FROM `subscriptions` s
          INNER JOIN `feed` f ON f.owner_type = s.sub_type AND f.owner_id = s.sub_link_id
          WHERE s.user_id = ". $user_id ." AND f.feed_ondelete IS NULL
            AND s.sub_type NOT IN ('post')
            AND f.event_type NOT IN ('new theme post', 'new comment', 'new good')
      )
      +
      (
        SELECT SUM(num) FROM (
          SELECT COUNT(DISTINCT HOUR(f.add_date)) AS num FROM `subscriptions` s
            INNER JOIN `feed` f ON f.owner_type = s.sub_type AND f.owner_id = s.sub_link_id
            INNER JOIN `goods` g ON g.good_id = f.event_link_id
            WHERE s.user_id = ". $user_id ." AND f.feed_ondelete IS NULL
              AND s.sub_type NOT IN ('post')
              AND f.event_type = 'new good'
            GROUP BY f.owner_id
        ) AS num
      )
    ) AS num");
    */
    $command1 = $db->createCommand("
      SELECT COUNT(f.feed_id) AS num FROM `subscriptions` s
          INNER JOIN `feed` f ON f.owner_type = s.sub_type AND f.owner_id = s.sub_link_id
          WHERE s.user_id = ". $user_id ." AND f.feed_ondelete IS NULL
            AND s.sub_type NOT IN ('post')
            AND f.event_type NOT IN ('new theme post', 'new comment', 'new good')
      ");

    $command2 = $db->createCommand("
        SELECT COALESCE(SUM(num), 0) AS num FROM (
          SELECT COUNT(DISTINCT HOUR(f.add_date)) AS num FROM `subscriptions` s
            INNER JOIN `feed` f ON f.owner_type = s.sub_type AND f.owner_id = s.sub_link_id
            INNER JOIN `goods` g ON g.good_id = f.event_link_id
            WHERE s.user_id = ". $user_id ." AND f.feed_ondelete IS NULL
              AND s.sub_type NOT IN ('post')
              AND f.event_type = 'new good'
            GROUP BY f.owner_id
        ) AS t");

    $result1 = $command1->queryRow();
    $result2 = $command2->queryRow();
    return $result1['num'] + $result2['num'];
  }

  public static function getFeeds($user_id, $offset = 0, $c = array()) {
    $result = array();
    /** @var CDbConnection $db */
    $db = Yii::app()->db;

    $command = $db->createCommand("
    SELECT * FROM
    (
      (
        SELECT f.*, 0 AS goods_num, '' AS goods_ids FROM `subscriptions` s
          INNER JOIN `feed` f ON f.owner_type = s.sub_type AND f.owner_id = s.sub_link_id
          WHERE s.user_id = ". $user_id ." AND f.feed_ondelete IS NULL
            AND s.sub_type NOT IN ('post')
            AND f.event_type NOT IN ('new theme post', 'new comment', 'new good')
      )
      UNION
      (
        SELECT f.*, COUNT(g.good_id) AS goods_num, GROUP_CONCAT(DISTINCT g.good_id SEPARATOR ';') AS goods_ids FROM `subscriptions` s
          INNER JOIN `feed` f ON f.owner_type = s.sub_type AND f.owner_id = s.sub_link_id
          INNER JOIN `goods` g ON g.good_id = f.event_link_id
          WHERE s.user_id = ". $user_id ." AND f.feed_ondelete IS NULL
            AND s.sub_type NOT IN ('post')
            AND f.event_type = 'new good'
          GROUP BY f.owner_id, HOUR(f.add_date)
      )
    ) t
    ORDER BY add_date DESC
    LIMIT ". $offset .", ". Yii::app()->getModule('feed')->newsPerPage);

    /** @var CDbDataReader $reader */
    $reader = $command->query();
    while (($row = $reader->read()) !== false) {
      $feed = new Feed();
      $feed->feed_id = $row['feed_id'];
      $feed->add_date = $row['add_date'];
      $feed->feed_ondelete = $row['feed_ondelete'];
      $feed->event_text = $row['event_text'];
      $feed->event_type = $row['event_type'];
      $feed->event_link_id = $row['event_link_id'];
      $feed->owner_id = $row['owner_id'];
      $feed->owner_type = $row['owner_type'];

      switch ($feed->event_type) {
        case 'new post':
          $post = ProfileWallPost::model()->with('author', 'author.profile', array('last_replies.replyTo' => array('limit' => 3)), 'repliesNum')->findByPk($feed->event_link_id);
          $feed->content = $post;
          break;
        case 'new purchase':
          $purchase = Purchase::model()->with('author.profile')->findByPk($feed->event_link_id);
          $feed->content = $purchase;
          break;
        case 'new good':
          $purchase = Purchase::model()->with('author.profile')->findByPk($feed->owner_id);
          $ids = explode(';', $row['goods_ids']);

          $criteria = new CDbCriteria();
          $criteria->addInCondition('t.good_id', $ids);
          $criteria->limit = 8;

          $feed->content = $purchase;
          $feed->goods_num = $row['goods_num'];
          $feed->goods = Good::model()->with('image')->findAll($criteria);
          break;
        case 'new status':
          $user = User::model()->with('profile')->findByPk($feed->event_link_id);
          $feed->content = $user;
          break;
      }

      $result[] = $feed;
    }

    return $result;
  }

  public static function countAnswerFeeds($user_id, $c = array()) {
    /** @var CDbConnection $db */
    $db = Yii::app()->db;

    $command = $db->createCommand("
    SELECT (
    (SELECT COUNT(f.feed_id) AS num FROM `profile_requests` r
      INNER JOIN `feed` f ON f.event_type = 'new reply' AND f.event_link_id = r.req_link_id
      WHERE r.req_type = ". ProfileRequest::TYPE_WALL_ANSWER ." AND r.owner_id = ". $user_id .")
    +
    (SELECT COUNT(f.feed_id) AS num FROM `profile_requests` r
      INNER JOIN `feed` f ON f.event_type = 'new post' AND f.event_link_id = r.req_link_id
      WHERE r.req_type = ". ProfileRequest::TYPE_POST_ON_WALL ." AND r.owner_id = ". $user_id .")
    +
    (SELECT COUNT(f.feed_id) AS num FROM `profile_requests` r
      INNER JOIN `feed` f ON f.event_type = 'new comment' AND f.event_link_id = r.req_link_id
      WHERE r.req_type IN (". ProfileRequest::TYPE_COMMENT_ANSWER .", ". ProfileRequest::TYPE_COMMENT .") AND r.owner_id = ". $user_id .")
    ) AS num");

    $result = $command->queryRow();
    return $result['num'];
  }

  public static function getAnswerFeeds($user_id, $offset = 0, $c = array()) {
    $result = array();
    $ids = array();
    /** @var CDbConnection $db */
    $db = Yii::app()->db;

    $command = $db->createCommand("
    SELECT * FROM
    (
      SELECT f.*, r.req_id, r.viewed FROM `profile_requests` r
        INNER JOIN `feed` f ON f.event_type = 'new reply' AND f.event_link_id = r.req_link_id
        WHERE r.req_type = ". ProfileRequest::TYPE_WALL_ANSWER ." AND r.owner_id = ". $user_id ."
      UNION ALL
      SELECT f.*, r.req_id, r.viewed FROM `profile_requests` r
        INNER JOIN `feed` f ON f.event_type = 'new post' AND f.event_link_id = r.req_link_id
        WHERE r.req_type = ". ProfileRequest::TYPE_POST_ON_WALL ." AND r.owner_id = ". $user_id ."
      UNION ALL
      SELECT f.*, r.req_id, r.viewed FROM `profile_requests` r
        INNER JOIN `feed` f ON f.event_type = 'new comment' AND f.event_link_id = r.req_link_id
        WHERE r.req_type IN (". ProfileRequest::TYPE_COMMENT_ANSWER .", ". ProfileRequest::TYPE_COMMENT .") AND r.owner_id = ". $user_id ."
    ) t
    ORDER BY add_date DESC
    LIMIT ". $offset .", ". Yii::app()->getModule('feed')->newsPerPage);

    /** @var CDbDataReader $reader */
    $reader = $command->query();
    while (($row = $reader->read()) !== false) {
      $feed = new Feed();
      $feed->feed_id = $row['feed_id'];
      $feed->add_date = $row['add_date'];
      $feed->feed_ondelete = $row['feed_ondelete'];
      $feed->event_text = $row['event_text'];
      $feed->event_type = $row['event_type'];
      $feed->event_link_id = $row['event_link_id'];
      $feed->owner_id = $row['owner_id'];
      $feed->owner_type = $row['owner_type'];

      $feed->viewed = $row['viewed'];
      if ($feed->viewed == 0) $ids[] = $row['req_id'];

      switch ($feed->event_type) {
        case 'new post':
          $post = ProfileWallPost::model()->with('author.profile')->findByPk($feed->event_link_id);
          if (!$post) continue 2;
          $feed->content = $post;
          break;
        case 'new reply':
          $reply = ProfileWallPost::model()->with('author.profile', 'replyPost')->findByPk($feed->event_link_id);
          if (!$reply) continue 2;
          $feed->content = $reply;
          break;
        case 'new comment':
          $comment = Comment::model()->with('author.profile')->findByPk($feed->event_link_id);
          if (!$comment) continue 2;
          $feed->content = $comment;
          break;
      }

      $result[] = $feed;
    }

    $criteria = new CDbCriteria();
    $criteria->addInCondition('req_id', $ids);

    ProfileRequest::model()->updateAll(array('viewed' => 1), $criteria);

    return $result;
  }

  public static function countCommentFeeds($user_id, $c = array()) {
    /** @var CDbConnection $db */
    $db = Yii::app()->db;

    $command = $db->createCommand("
    SELECT COUNT(feed_id) AS num FROM `subscriptions` s
      INNER JOIN `feed` mf ON mf.owner_type = s.sub_type AND mf.owner_id = s.sub_link_id
      WHERE s.user_id = ". $user_id ." AND mf.event_type IN ('new comment', 'new reply', 'new theme post')
        AND mf.feed_ondelete IS NULL
      GROUP BY mf.owner_id");

    $result = $command->queryRow();
    return $result['num'];
  }

  public static function getCommentFeeds($user_id, $offset = 0, $c = array()) {
    $result = array();
    $ids = array();
    /** @var CDbConnection $db */
    $db = Yii::app()->db;

    $sub_id = array();

    $subcommand = $db->createCommand("
    SELECT MAX(feed_id) AS feed_id FROM `subscriptions` s
      INNER JOIN `feed` mf ON mf.owner_type = s.sub_type AND mf.owner_id = s.sub_link_id
      WHERE s.user_id = ". $user_id ." AND mf.event_type IN ('new comment', 'new reply', 'new theme post')
        AND mf.feed_ondelete IS NULL
      GROUP BY mf.owner_id
      LIMIT ". $offset .", ". Yii::app()->getModule('feed')->newsPerPage);
    $subreader = $subcommand->query();
    while (($row = $subreader->read()) !== false) {
      $sub_id[] = $row['feed_id'];
    }

    $command = $db->createCommand("
    SELECT * FROM `feed`
      WHERE feed_id IN (". implode(", ", $sub_id) .")
      ORDER BY add_date DESC");

    /** @var CDbDataReader $reader */
    $reader = $command->query();
    while (($row = $reader->read()) !== false) {
      $feed = new Feed();
      $feed->feed_id = $row['feed_id'];
      $feed->add_date = $row['add_date'];
      $feed->feed_ondelete = $row['feed_ondelete'];
      $feed->event_text = $row['event_text'];
      $feed->event_type = $row['event_type'];
      $feed->event_link_id = $row['event_link_id'];
      $feed->owner_id = $row['owner_id'];
      $feed->owner_type = $row['owner_type'];

      switch ($feed->event_type) {
        case 'new theme post':
          $criteria = new CDbCriteria();
          $criteria->compare('post_id', $feed->event_link_id);
          $criteria->select = 'forum_id, theme_id';

          $nullpost = DiscussPost::model()->with('theme', 'theme.forum')->findByPk($feed->event_link_id);
          if (!$nullpost) continue 2;

          $criteria = new CDbCriteria();
          $criteria->order = 'add_date DESC';
          $criteria->compare('forum_id', $nullpost->forum_id);
          $criteria->compare('theme_id', $nullpost->theme_id);

          $offsets = DiscussPost::model()->count($criteria);
          $criteria->limit = 3;

          $posts = array_reverse(DiscussPost::model()->with('author.profile')->findAll($criteria));

          $feed->content = array('nullpost' => $nullpost, 'offsets' => $offsets, 'posts' => $posts);
          break;
        case 'new reply':
          $reply = ProfileWallPost::model()->with('replyPost', array('replyPost.last_replies' => array('limit' => 3)), 'replyPost.author.profile')->findByPk($feed->event_link_id);
          if (!$reply) continue 2;
          $feed->content = $reply->replyPost;
          break;
        case 'new comment':
          $nullcomment = Comment::model()->findByPk($feed->event_link_id);
          if (!$nullcomment) continue 2;

          $criteria = new CDbCriteria();
          $criteria->order = 'creation_date DESC';
          $criteria->compare('hoop_id', $nullcomment->hoop_id);
          $criteria->compare('hoop_type', $nullcomment->hoop_type);

          $commentsNum = Comment::model()->with('reply')->count($criteria);
          $criteria->limit = 3;

          $comments = array_reverse(Comment::model()->with('author', 'author.profile', 'reply')->findAll($criteria));

          $feed->content = array('hoop_id' => $nullcomment->hoop_id, 'hoop_type' => $nullcomment->hoop_type, 'offsets' => $commentsNum, 'comments' => $comments);
          break;
      }

      $result[] = $feed;
    }

    return $result;
  }

  public function beforeSave() {
    if (parent::beforeSave()) {
      if ($this->getIsNewRecord())
        $this->add_date = date("Y-m-d H:i:s");

      return true;
    }
    else return false;
  }

  public function markAsDeleted() {
    $this->feed_ondelete = date("Y-m-d H:i:s");
    $this->save(true, array('feed_ondelete'));
  }

  public function restore() {
    $this->feed_ondelete = null;
    $this->save(true, array('feed_ondelete'));
  }
}