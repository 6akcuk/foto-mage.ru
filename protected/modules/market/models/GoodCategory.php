<?php

/**
 * This is the model class for table "goods_categories".
 *
 * The followings are the available columns in table 'goods_categories':
 * @property integer $category_id
 * @property integer $parent_id
 * @property string $name
 * @property integer $no_title
 * @property string $title_form
 * @property integer $no_price
 *
 * @property GoodCategory $parent
 * @property GoodCategory|Array $childs
 */
class GoodCategory extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return GoodCategory the static model class
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
		return 'goods_categories';
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
			array('category_id, parent_id, name, no_title, title_form, no_price', 'safe', 'on'=>'search'),
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
      'parent' => array(self::BELONGS_TO, 'GoodCategory', 'parent_id'),
      'childs' => array(self::HAS_MANY, 'GoodCategory', 'parent_id'),
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
			'no_title' => 'No Title',
			'title_form' => 'Title Form',
			'no_price' => 'No Price',
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
		$criteria->compare('no_title',$this->no_title);
		$criteria->compare('title_form',$this->title_form,true);
		$criteria->compare('no_price',$this->no_price);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public function performDelete() {
    /** @var CDbConnection $db */
    $db = Yii::app()->db;

    // Удалим саму категорию
    $this->delete();
  }
}