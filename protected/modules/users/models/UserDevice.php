<?php
/**
 * Created by PhpStorm.
 * User: Sum
 * Date: 06.03.14
 * Time: 20:55
 *
 * @property integer $user_id
 * @property string $iOSDeviceToken
 * @property string $AndroidPushToken
 */

class UserDevice extends CActiveRecord {
  /**
   * Returns the static model of the specified AR class.
   * @param string $className active record class name.
   * @return User the static model class
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
    return 'user_devices';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    // NOTE: you should only define rules for those attributes that
    // will receive user inputs.
    return array(
      array('user_id', 'required'),
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
      'user' => array(self::BELONGS_TO, 'User', 'user_id'),
    );
  }

  /**
   * @return array customized attribute labels (name=>label)
   */
  public function attributeLabels()
  {
    return array(

    );
  }
}