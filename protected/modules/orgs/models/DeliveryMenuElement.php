<?php

/**
 * This is the model class for table "delivery_menu_elements".
 *
 * The followings are the available columns in table 'delivery_menu_elements':
 * @property integer $element_id
 * @property integer $org_id
 * @property integer $category_id
 * @property string $icon
 * @property string $name
 *
 * @property DeliveryCategory $category
 * @property Organization $org
 */
class DeliveryMenuElement extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DeliveryMenuElement the static model class
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
		return 'delivery_menu_elements';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('org_id, category_id, icon, name', 'required'),
			array('org_id, category_id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>150),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('element_id, org_id, category_id, icon, name', 'safe', 'on'=>'search'),
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
			'element_id' => 'Element',
			'org_id' => 'Org',
			'category_id' => 'Категория',
			'icon' => 'Иконка',
			'name' => 'Название',
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

		$criteria->compare('element_id',$this->element_id);
		$criteria->compare('org_id',$this->org_id);
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('icon',$this->icon,true);
		$criteria->compare('name',$this->name,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public function performDelete() {
    // Удалим все товары меню
    DeliveryGood::model()->deleteAll('element_id = :id', array(':id' => $this->element_id));

    $this->delete();

    // Удалим ссылку
    $have = DeliveryMenuElement::model()->find('org_id = :id AND category_id = :cid', array(':id' => $this->org_id, ':cid' => $this->category_id));
    if (!$have) {
      $check = DeliveryCoLink::model()->find('org_id = :id AND category_id = :cid', array(':id' => $this->org_id, ':cid' => $this->category_id));
      if ($check) $check->delete();
    }
  }
}