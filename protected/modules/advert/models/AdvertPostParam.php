<?php

/**
 * This is the model class for table "advert_post_params".
 *
 * The followings are the available columns in table 'advert_post_params':
 * @property string $post_id
 * @property integer $param_id
 * @property string $param_value
 *
 * @property AdvertParam $param_name
 * @property AdvertParam $value
 */
class AdvertPostParam extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AdvertPostParam the static model class
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
		return 'advert_post_params';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('post_id, param_id, param_value', 'required'),
			array('param_id', 'numerical', 'integerOnly'=>true),
			array('post_id', 'length', 'max'=>10),
			array('param_value', 'length', 'max'=>250),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('post_id, param_id, param_value', 'safe', 'on'=>'search'),
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
      'param_name' => array(self::BELONGS_TO, 'AdvertParam', 'param_id'),
      'value' => array(self::BELONGS_TO, 'AdvertParam', 'param_value'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'post_id' => 'Post',
			'param_id' => 'Param',
			'param_value' => 'Param Value',
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
		$criteria->compare('param_id',$this->param_id);
		$criteria->compare('param_value',$this->param_value,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}