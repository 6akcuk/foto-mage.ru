<?php

/**
 * This is the model class for table "advert_params".
 *
 * The followings are the available columns in table 'advert_params':
 * @property integer $param_id
 * @property integer $parent_id
 * @property integer $category_id
 * @property string $title
 * @property string $type
 * @property string $suffix
 *
 * @property AdvertCategory $category
 * @property AdvertParam $parent
 * @property AdvertParam|Array $childs
 */
class AdvertParam extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AdvertParam the static model class
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
		return 'advert_params';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('category_id, title, type', 'required'),
			array('parent_id, category_id', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>150),
			array('type', 'length', 'max'=>20),
      array('suffix', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('param_id, parent_id, category_id, title, type', 'safe', 'on'=>'search'),
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
      'category' => array(self::BELONGS_TO, 'AdvertCategory', 'category_id'),
      'parent' => array(self::BELONGS_TO, 'AdvertParam', 'parent_id'),
      'childs' => array(self::HAS_MANY, 'AdvertParam', 'parent_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'param_id' => 'Param',
			'parent_id' => 'Вложен в параметр',
			'category_id' => 'Категория',
			'title' => 'Название или значение',
			'type' => 'Тип параметра',
      'suffix' => 'Суффикс параметра',
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

		$criteria->compare('param_id',$this->param_id);
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('type',$this->type,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  static public function buildTable($id) {
    $result = array();
    $params = self::model()->findAll('category_id = :id AND parent_id IS NULL', array(':id' => $id));
    /** @var AdvertParam $param */
    foreach ($params as $param) {
      $items = array();
      foreach ($param->childs as $child) {
        $items[] = array(intval($child->param_id), $child->title);
        // Вложенные параметры
        foreach ($child->childs as $wow) {
          $wow_items = array();
          foreach ($wow->childs as $wow_child) {
            $wow_items[] = array(intval($wow_child->param_id), $wow_child->title);
          }
          $result[] = array('id' => intval($wow->param_id), 'label' => $wow->title, 'type' => $wow->type, 'items' => $wow_items, 'dependence' => intval($wow->parent_id));
        }
      }

      $result[] = array('id' => intval($param->param_id), 'label' => $param->title, 'type' => $param->type, 'items' => $items);
    }

    return $result;
  }

  public function performDelete() {
    /** @var CDbConnection $db */
    $db = Yii::app()->db;

    // Удалим все поисковые данные параметра
    $command1 = $db->createCommand("
      DELETE FROM `advert_post_params` WHERE param_id = ". $this->param_id ."");
    $command1->query();

    // Удалим поисковые данные вложений параметра
    $command2 = $db->createCommand("
      DELETE FROM `advert_post_params` WHERE param_id IN (SELECT param_id FROM `advert_params` WHERE parent_id = ". $this->param_id ." AND type != 'value')");
    $command2->query();

    // Удалим детей вложенных параметров
    $command3 = $db->createCommand("
      DELETE FROM `advert_params` WHERE parent_id IN (SELECT param_id FROM (SELECT param_id FROM `advert_params` WHERE parent_id IN (SELECT param_id FROM `advert_params` WHERE parent_id = ". $this->param_id .")) AS c)");
    $command3->query();

    // Удалим все вложенные параметры
    $command4 = $db->createCommand("
      DELETE FROM `advert_params` WHERE parent_id IN (SELECT param_id FROM (SELECT param_id FROM `advert_params` WHERE parent_id = ". $this->param_id .") AS c)");
    $command4->query();

    // Удалим детей параметра
    $command5 = $db->createCommand("
      DELETE FROM `advert_params` WHERE parent_id = ". $this->param_id ."");
    $command5->query();

    // Удалим сам параметр
    $this->delete();
  }
}