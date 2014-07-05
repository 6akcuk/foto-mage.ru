<?php

/**
 * This is the model class for table "events".
 *
 * The followings are the available columns in table 'events':
 * @property string $event_id
 * @property integer $event_type_id
 * @property string $photo
 * @property string $title
 * @property string $shortstory
 * @property string $start_time
 * @property string $end_time
 * @property integer $org_id
 * @property integer $room_id
 * @property string $price
 * @property string $weekly
 *
 * @property EventType $event_type
 * @property Organization $org
 */
class Event extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Event the static model class
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
		return 'events';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('event_type_id, title, shortstory, org_id', 'required'),
			array('event_type_id, org_id, room_id', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>200),
			array('price', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('event_id, event_type_id, title, shortstory, start_time, end_time, org_id, room_id, price', 'safe', 'on'=>'search'),
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
      'event_type' => array(self::BELONGS_TO, 'EventType', 'event_type_id'),
      'org' => array(self::BELONGS_TO, 'Organization', 'org_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'event_id' => 'Event',
			'event_type_id' => 'Тип события',
      'photo' => 'Афиша',
			'title' => 'Название',
			'shortstory' => 'Описание',
			'start_time' => 'Время начала',
			'end_time' => 'Время окончания',
			'org_id' => 'Организатор',
			'room_id' => 'Помещение',
			'price' => 'Стоимость',
		);
	}

  public function getLStartTime($timezone = null) {
    $source_tz = new DateTimeZone('Europe/Moscow');
    $target_tz = new DateTimeZone(($timezone) ?: $this->org->city->timezone);

    $now = new DateTime('now', $source_tz);
    $now->setTimezone($target_tz);

    $start = new DateTime($this->start_time, $source_tz);
    $start->setTimezone($target_tz);

    if ($this->event_type_id == 1) return $start->format('H:i');
    else {
      if ($this->weekly) {
        $weekdays = explode(",", $this->weekly);
        if (!is_array($weekdays)) $weekdays = array($weekdays);

        if ($weekdays[0] == 'Sun') $num = 5;
        elseif (in_array($weekdays[0], array('Wed', 'Fri', 'Sat'))) $num = 2;
        else $num = 1;

        $return = Yii::t('app', 'Каждый|Каждую|Каждое', $num) .' ';
        foreach ($weekdays as &$weekday) {
          $weekday = Yii::t('app', $weekday .'_2');
        }

        return $return . implode(", ", $weekdays);
      } else {
        if ($this->start_time) {
          if ($start->format('Y-m-d') == $now->format('Y-m-d')) return $start->format('H:i');
          else return ActiveHtml::date($start->format('Y-m-d H:i:s'));
        }
        else return "Ежедневно";
      }
    }
  }
}