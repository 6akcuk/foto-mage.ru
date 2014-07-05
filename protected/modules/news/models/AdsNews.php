<?php

/**
 * This is the model class for table "ads_news".
 *
 * The followings are the available columns in table 'ads_news':
 * @property integer $ads_id
 * @property integer $city_id
 * @property integer $author_id
 * @property string $banner
 * @property integer $weight
 * @property string $add_date
 * @property integer $views_num
 *
 * @property User $author
 * @property City $city
 */
class AdsNews extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AdsNews the static model class
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
		return 'ads_news';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('city_id, author_id, banner, weight', 'required'),
			array('city_id, weight, views_num', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('ads_id, city_id, banner, weight, add_date, views_num', 'safe', 'on'=>'search'),
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
			'ads_id' => 'Ads',
			'city_id' => 'Город',
			'banner' => 'Баннер',
			'weight' => 'Вес',
			'add_date' => 'Add Date',
			'views_num' => 'Просмотров',
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

		$criteria->compare('ads_id',$this->ads_id);
		$criteria->compare('city_id',$this->city_id);
		$criteria->compare('banner',$this->banner,true);
		$criteria->compare('weight',$this->weight);
		$criteria->compare('add_date',$this->add_date,true);
		$criteria->compare('views_num',$this->views_num);

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