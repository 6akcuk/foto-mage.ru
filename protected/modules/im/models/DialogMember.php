<?php

/**
 * This is the model class for table "dialog_members".
 *
 * The followings are the available columns in table 'dialog_members':
 * @property string $dialog_id
 * @property integer $member_id
 * @property string $add_date
 *
 * @property Dialog $dialog
 * @property User $user
 */
class DialogMember extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DialogMember the static model class
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
		return 'dialog_members';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('dialog_id, member_id', 'required'),
			array('member_id', 'numerical', 'integerOnly'=>true),
			array('dialog_id', 'length', 'max'=>10),
            array('add_date', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('dialog_id, member_id, add_date', 'safe', 'on'=>'search'),
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
      'twin' => array(self::BELONGS_TO, 'DialogMember', 'dialog_id'),
      'dialog' => array(self::BELONGS_TO, 'Dialog', 'dialog_id'),
      'user' => array(self::BELONGS_TO, 'User', 'member_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'dialog_id' => 'Dialog',
			'member_id' => 'Member',
			'add_date' => 'Add Date',
		);
	}

    public function beforeSave() {
        if (parent::beforeSave()) {
            if ($this->isNewRecord)
                $this->add_date = date("Y-m-d H:i:s");

            return true;
        }
        else return false;
    }
}