<?php

/**
 * This is the model class for table "advert_categories".
 *
 * The followings are the available columns in table 'advert_categories':
 * @property integer $category_id
 * @property integer $parent_id
 * @property string $name
 * @property integer $no_title
 * @property string $title_form
 * @property integer $no_price
 *
 * @property AdvertCategory $parent
 * @property AdvertCategory|Array $childs
 */
class AdvertCategory extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AdvertCategory the static model class
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
		return 'advert_categories';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('parent_id, no_title, no_price', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>60),
      array('title_form', 'length', 'max'=>250),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('category_id, parent_id, name', 'safe', 'on'=>'search'),
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
      'parent' => array(self::BELONGS_TO, 'AdvertCategory', 'parent_id'),
      'childs' => array(self::HAS_MANY, 'AdvertCategory', 'parent_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'category_id' => 'Category',
			'parent_id' => 'Родительская категория',
			'name' => 'Название',
      'no_title' => 'Заголовок объявления не нужен',
      'title_form' => 'Синтаксис формирования заголовка',
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

		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('name',$this->name,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public function performDelete() {
    /** @var CDbConnection $db */
    $db = Yii::app()->db;

    // Удалим все поисковые данные объявлений
    if ($this->parent_id) {
      $command1 = $db->createCommand("
        DELETE FROM `advert_post_params` WHERE post_id IN (SELECT post_id FROM `advert_posts` WHERE category_id = ". $this->category_id .")");
      $command1->query();
    } else {
      $command1 = $db->createCommand("
        DELETE FROM `advert_post_params` WHERE post_id IN (SELECT post_id FROM `advert_posts` WHERE category_id IN (SELECT category_id FROM `advert_categories` WHERE parent_id = ". $this->category_id ."))");
      $command1->query();
    }

    // Удалим все объявления категории
    if ($this->parent_id) {
      $command2 = $db->createCommand("
        DELETE FROM `advert_posts` WHERE category_id = ". $this->category_id ."");
      $command2->query();
    } else {
      $command2 = $db->createCommand("
        DELETE FROM `advert_posts` WHERE category_id IN (SELECT category_id FROM `advert_categories` WHERE parent_id = ". $this->category_id .")");
      $command2->query();
    }

    // Удалим все параметры
    if ($this->parent_id) {
      $command3 = $db->createCommand("
        DELETE FROM `advert_params` WHERE category_id = ". $this->category_id ."");
      $command3->query();
    } else {
      $command3 = $db->createCommand("
        DELETE FROM `advert_params` WHERE category_id IN (SELECT category_id FROM `advert_categories` WHERE parent_id = ". $this->category_id .")");
      $command3->query();
    }

    // Удалим все подкатегории
    if (!$this->parent_id) {
      $command3 = $db->createCommand("
        DELETE FROM `advert_categories` WHERE parent_id = ". $this->category_id ."");
      $command3->query();
    }

    // Удалим саму категорию
    $this->delete();
  }
}