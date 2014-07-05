<?php

/**
 * This is the model class for table "cities".
 *
 * The followings are the available columns in table 'cities':
 * @property integer $id
 * @property string $name
 * @property string $timezone
 * @property integer $published
 */
class City extends CActiveRecord
{
    private static $_cities = null;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return City the static model class
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
		return 'cities';
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
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'name' => 'Название города',
      'timezone' => 'Часовой пояс',
      'published' => 'Город опубликован',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public static function getListArray()
  {
    $arr = array(0 => 'Нет города');
    $criteria = new CDbCriteria();
    $criteria->order = 'name';

    $data = self::model()->findAll($criteria);
    foreach ($data as $dt) {
      $arr[$dt->id] = $dt->name;
    }

    return $arr;
  }

  public static function getDataArray()
  {
    if (!self::$_cities) {
      $arr = array();
      $criteria = new CDbCriteria();
      $criteria->order = 'name';

      $data = self::model()->findAll($criteria);
      foreach ($data as $dt) {
        $arr[$dt->name] = $dt->id;
      }

      self::$_cities = $arr;
    }

    return self::$_cities;
  }
}