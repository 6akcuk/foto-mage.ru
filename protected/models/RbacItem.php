<?php

/**
 * This is the model class for table "rbac_items".
 *
 * The followings are the available columns in table 'rbac_items':
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $bizrule
 * @property string $data
 *
 * The followings are the available model relations:
 * @property RbacAssignments[] $rbacAssignments
 * @property RbacItemChilds[] $rbacItemChilds
 * @property RbacItemChilds[] $rbacItemChilds1
 */
class RbacItem extends CActiveRecord
{
    const TYPE_OPERATION = 0;
    const TYPE_TASK = 1;
    const TYPE_ROLE = 2;

    private static $_sroles = null;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return RbacItem the static model class
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
		return 'rbac_items';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, type', 'required'),
			array('type', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>64),
			array('description, bizrule, data', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('name, type, description, bizrule, data', 'safe', 'on'=>'search'),
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
			'rbacAssignments' => array(self::HAS_MANY, 'RbacAssignments', 'itemname'),
			'rbacItemChilds' => array(self::HAS_MANY, 'RbacItemChilds', 'parent'),
			'rbacItemChilds1' => array(self::HAS_MANY, 'RbacItemChilds', 'child'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'name' => 'Name',
			'type' => 'Type',
			'description' => 'Description',
			'bizrule' => 'Bizrule',
			'data' => 'Data',
		);
	}

    public static function getSearchRoleArray()
    {
        if (!self::$_sroles) {
            $arr = array();
            $data = self::model()->findAll('type = :type', array(':type' => self::TYPE_ROLE));
            foreach ($data as $dt) {
                if ($dt->name == 'Гость') continue;
                $arr[$dt->name] = $dt->name;
            }

            self::$_sroles = $arr;
        }

        return self::$_sroles;
    }
}