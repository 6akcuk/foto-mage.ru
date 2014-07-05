<?php

/**
 * This is the model class for table "delivery_co_links".
 *
 * The followings are the available columns in table 'delivery_co_links':
 * @property integer $category_id
 * @property integer $org_id
 *
 * @property DeliveryMenuElement $elements
 * @property DeliveryCategory $category
 * @property Organization $org
 */
class DeliveryCoLink extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DeliveryCoLink the static model class
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
		return 'delivery_co_links';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('category_id, org_id', 'required'),
			array('category_id, org_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('category_id, org_id', 'safe', 'on'=>'search'),
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
      'category' => array(self::BELONGS_TO, 'DeliveryCategory', 'category_id'),
      'org' => array(self::BELONGS_TO, 'Organization', 'org_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'category_id' => 'Category',
			'org_id' => 'Org',
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
		$criteria->compare('org_id',$this->org_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}