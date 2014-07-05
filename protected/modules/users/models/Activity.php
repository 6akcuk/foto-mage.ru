<?php

/**
 * This is the model class for table "activities".
 *
 * The followings are the available columns in table 'activities':
 * @property string $act_id
 * @property string $ip
 * @property integer $author_id
 * @property integer $accepted
 * @property string $timestamp
 * @property string $request
 */
class Activity extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Activity the static model class
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
		return 'activities';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ip, author_id, timestamp, request', 'required'),
			array('author_id, accepted', 'numerical', 'integerOnly'=>true),
			array('ip', 'length', 'max'=>10),
			array('timestamp', 'length', 'max'=>18),
			array('request', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('act_id, ip, author_id, timestamp, request', 'safe', 'on'=>'search'),
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
			'act_id' => 'Act',
			'ip' => 'Ip',
			'author_id' => 'Author',
      'accepted' => 'Accepted',
			'timestamp' => 'Timestamp',
			'request' => 'Request',
		);
	}

  public function getTimestamp() {
    return preg_replace("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})(.*)/ui", "$1-$2-$3 $4:$5:$6$7", $this->timestamp);
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

		$criteria->compare('act_id',$this->act_id,true);
		$criteria->compare('ip',$this->ip,true);
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('timestamp',$this->timestamp,true);
		$criteria->compare('request',$this->request,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}