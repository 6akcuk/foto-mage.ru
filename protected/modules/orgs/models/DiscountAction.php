<?php

/**
 * This is the model class for table "discount_actions".
 *
 * The followings are the available columns in table 'discount_actions':
 * @property integer $action_id
 * @property integer $org_id
 * @property integer $type
 * @property string $fullstory
 * @property string $name
 * @property string $start_time
 * @property string $end_time
 * @property string $banner
 * @property integer $pc_limits
 * @property integer $cur_pc
 *
 * @property Organization $org
 * @property integer $codesNum
 * @property integer $hasCode
 */
class DiscountAction extends CActiveRecord
{
  const TYPE_ACTION = 0;
  const TYPE_DISCOUNT_CARD = 1;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DiscountAction the static model class
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
		return 'discount_actions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('org_id, name, banner', 'required'),
			array('org_id, pc_limits', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>200),
      array('fullstory', 'length', 'max' => 4096),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('action_id, org_id, name, start_time, end_time, banner, pc_limits', 'safe', 'on'=>'search'),
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
      'org' => array(self::BELONGS_TO, 'Organization', 'org_id'),
      'codesNum' => array(self::STAT, 'DiscountPromoCode', 'action_id'),
      'hasCode' => array(self::STAT, 'DiscountPromoCode', 'action_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'action_id' => 'Action',
			'org_id' => 'Org',
			'name' => 'Название акции',
      'fullstory' => 'Описание акции',
			'start_time' => 'Дата начала',
			'end_time' => 'Дата окончания',
			'banner' => 'Изображение',
			'pc_limits' => 'Лимит кодов',
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

		$criteria->compare('action_id',$this->action_id);
		$criteria->compare('org_id',$this->org_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('start_time',$this->start_time,true);
		$criteria->compare('end_time',$this->end_time,true);
		$criteria->compare('banner',$this->banner,true);
		$criteria->compare('pc_limits',$this->pc_limits);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public function performDelete() {
    // Удалим все выданные промо-коды
    DiscountPromoCode::model()->deleteAll('action_id = :id', array(':id' => $this->action_id));

    $this->delete();
  }
}