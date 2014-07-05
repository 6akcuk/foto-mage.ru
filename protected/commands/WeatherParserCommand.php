<?php

class WeatherParserCommand extends CConsoleCommand {
  /**
  * Собрать информацию о погоде по городам
  */
  public function actionParse() {
    // Проверим, для всех ли городов найден Yandex ID
    $cities = City::model()->findAll();
    /** @var City $city */
    foreach ($cities as $city) {
      $link = WeatherLink::model()->find('city_id = :id', array(':id' => $city->id));
      if (!$link) {
        $xml = new SimpleXMLElement("http://weather.yandex.ru/static/cities.xml", 0, true);
        foreach ($xml->country as $country_id => $country) {
          if ($country['name'] == "Россия") {
            foreach ($country->city as $citydata) {
              if ($citydata == $city->name) {
                $link = new WeatherLink();
                $link->city_id = $city->id;
                $link->weather_id = $citydata['id'];
                $link->save();
                break 2;
              }
            }
          }
        }
      }
    }

    $log = new ParserLog();
    $log->start_dt = date("Y-m-d H:i:s");
    $log->message = "Парсер погоды с API Яндекса";
    $log->save();

    $log_events = 0;

    // Загрузим информацию о погоде в базу
    $links = WeatherLink::model()->findAll();
    foreach ($links as $link) {
      $forecast = WeatherForecast::model()->findByPk($link->weather_id);
      if (!$forecast) {
        $forecast = new WeatherForecast();
        $forecast->weather_id = $link->weather_id;
        $forecast->save();
      }

      $xml = new SimpleXMLElement("http://export.yandex.ru/weather-ng/forecasts/". $forecast->weather_id .".xml", 0, true);
      $forecast->data = $xml->asXML();
      if ($forecast->save(true, array('data'))) $log_events++;
    }

    $log->end_dt = date("Y-m-d H:i:s");
    $log->message .= " обновил информацию по ". Yii::t('app', '{n} городу|{n} городам|{n} городам', $log_events);
    $log->save(true, array('end_dt', 'message'));
  }
}