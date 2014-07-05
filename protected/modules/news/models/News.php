<?php

/**
 * This is the model class for table "news".
 *
 * The followings are the available columns in table 'news':
 * @property string $news_id
 * @property integer $city_id
 * @property integer $author_id
 * @property string $add_date
 * @property string $title
 * @property string $fullstory
 * @property string $facephoto
 * @property string $photo
 * @property string $video
 * @property string $document
 * @property integer $views_num
 *
 * @property User $author
 * @property City $city
 */
class News extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return News the static model class
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
		return 'news';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('city_id, author_id, title, fullstory, facephoto', 'required'),
			array('city_id, author_id', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('news_id, city_id, author_id, add_date, title, fullstory, facephoto, photo, video, document', 'safe', 'on'=>'search'),
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
      'city' => array(self::BELONGS_TO, 'City', 'city_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'news_id' => 'News',
			'city_id' => 'Город',
			'author_id' => 'Author',
			'add_date' => 'Add Date',
			'title' => 'Заголовок',
			'fullstory' => 'Содержание',
			'facephoto' => 'Основная фотография',
			'photo' => 'Фотографии',
			'video' => 'Видеозаписи',
			'document' => 'Документы',
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

		$criteria->compare('news_id',$this->news_id,true);
		$criteria->compare('city_id',$this->city_id);
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('add_date',$this->add_date,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('fullstory',$this->fullstory,true);
		$criteria->compare('facephoto',$this->facephoto,true);
		$criteria->compare('photo',$this->photo,true);
		$criteria->compare('video',$this->video,true);
		$criteria->compare('document',$this->document,true);

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
}