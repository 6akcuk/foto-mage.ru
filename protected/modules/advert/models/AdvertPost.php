<?php

/**
 * This is the model class for table "advert_posts".
 *
 * The followings are the available columns in table 'advert_posts':
 * @property string $post_id
 * @property integer $category_id
 * @property integer $author_id
 * @property integer $city_id
 * @property string $add_date
 * @property string $title
 * @property string $fullstory
 * @property string $price
 * @property integer $fixed
 * @property string $photo
 * @property string $audio
 * @property string $video
 * @property string $document
 *
 * @property User $author
 * @property City $city
 * @property AdvertCategory $category
 * @property AdvertPostParam|Array $params
 */
class AdvertPost extends CActiveRecord
{
  /**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AdvertPost the static model class
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
		return 'advert_posts';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('category_id, author_id, city_id, fullstory', 'required'),
      array('title', 'required', 'on' => 'title, title.price'),
      array('price', 'required', 'on' => 'price, title.price'),
			array('category_id, author_id, city_id, fixed', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>200),
			array('price', 'length', 'max'=>9),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('post_id, category_id, author_id, city_id, add_date, title, fullstory, price, fixed', 'safe', 'on'=>'search'),
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
      'category' => array(self::BELONGS_TO, 'AdvertCategory', 'category_id'),
      'params' => array(self::HAS_MANY, 'AdvertPostParam', array('post_id' => 'post_id')),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'post_id' => 'Post',
			'category_id' => 'Категория',
			'author_id' => 'Author',
			'city_id' => 'Город',
			'add_date' => 'Add Date',
			'title' => 'Заголовок',
			'fullstory' => 'Описание',
			'price' => 'Цена',
			'fixed' => 'Закрепить объявление',
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
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('city_id',$this->city_id);
		$criteria->compare('add_date',$this->add_date,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('fullstory',$this->fullstory,true);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('fixed',$this->fixed);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public function getTitle() {
    if ($this->category->no_title == 1) {
      $title = $this->category->title_form;
      $table = AdvertParam::buildTable($this->category_id);
      $params = array();
      //preg_match_all("/\{([A-zА-я0-9 ]*)\}/u", $this->category->title_form, $param_labels);

      foreach ($this->params as $param) {
        $params[$param->param_id] = $param;
      }

      foreach ($table as $td) {
        if (!isset($params[$td['id']])) continue;
        $value = ($td['type'] == 'select') ? $params[$td['id']]->value->title : $params[$td['id']]->param_value;
        $title = preg_replace("/\{". $td['label'] ."\}/u", $value, $title);
        /*foreach ($param_labels[1] as $label) {
          if ($label == )
        }*/
      }
    }
    else $title = $this->title;

    return $title;
  }

  public function beforeSave() {
    if (parent::beforeSave()) {
      if ($this->isNewRecord)
        $this->add_date = date("Y-m-d H:i:s");

      return true;
    }
    else return false;
  }

  public function performDelete() {
    AdvertPostParam::model()->deleteAll('post_id = :id', array(':id' => $this->post_id));
    $this->delete();
  }
}