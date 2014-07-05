<?php
/**
 * Created by JetBrains PhpStorm.
 * User: denis
 * Date: 09.07.2013
 * Time: 18:18
 * To change this template use File | Settings | File Templates.
 */

class ParserSterlitamakCommand extends CConsoleCommand {
  /**
   * Новости с портала Стерлитамак.ру
   */
  public function actionStrRu() {
    if (Yii::app()->mutex->lock('parser-sterlitamak-strru', 30)) {
      Yii::import('news.models.*');
      Yii::import('application.vendors.*');
      require_once 'Zend/Http/Client.php';
      require_once 'Zend/Dom/Query.php';

      $poet = Poet::getInstance();
      $yupi = new Yupi(array(
        'enable_retries' => true,
      ));

      header("Content-Type: text/html; charset=utf-8");

      $log = new ParserLog();
      $log->start_dt = date("Y-m-d H:i:s");
      $log->message = "Парсер новостного портала Стерлитамак.ру";
      $log->save();

      $log_events = 0;

      $content = $yupi->get("http://sterlitamak.ru/newscity/");
      $container = $yupi->parseToHtml($content, 'div.new-news-list');
      $city_id = 34;
      $author_id = 32;

      $source_tz = new DateTimeZone('Asia/Yekaterinburg');
      $target_tz = new DateTimeZone('Europe/Moscow');
      $month_table = array(
        'января' => '01',
        'февраля' => '02',
        'марта' => '03',
        'апреля' => '04',
        'мая' => '05',
        'июня' => '06',
        'июля' => '07',
        'августа' => '08',
        'сентября' => '09',
        'октября' => '10',
        'ноября' => '11',
        'декабря' => '12',
      );

      $film_caches = array();

      $elements = $yupi->parseToHtml($container, 'div.new-news-list div.new-news-item');

      $now = new DateTime('now');
      $now->setTimezone($source_tz);
      $now->setTime(0, 0);

      for ($i = 0; $i < sizeof($elements); $i++) {
        if ($log_events == 7) break; // protection from crash

        $href = $yupi->parseToElement($elements[$i], 'div.new-news-name a');
        $name = $yupi->parseToValue($elements[$i], 'div.new-news-name a');
        $date = $yupi->parseToValue($elements[$i], 'div.new-news-date');
        preg_match("/(\d+\.\d+\.\d+[ ]{1}\d+:\d+)/ui", $date, $publish);

        $add_date = new DateTime($publish[1], $source_tz);
        $add_date->setTimezone($target_tz);

        // Проверим, не добавлена ли была уже новость
        $criteria = new CDbCriteria();
        $criteria->compare('city_id', $city_id);
        $criteria->compare('title', $name);
        $criteria->compare('add_date', $add_date->format('Y-m-d H:i:s'));

        $check = News::model()->find($criteria);
        if ($check) continue;

        $page = $yupi->get("http://sterlitamak.ru". $href->getAttribute('href'));
        $facephoto_el = $yupi->parseToElement($page, "div.news-detail img.detail_picture");
        if (!$facephoto_el) {
          $facephoto_el = $yupi->parseToElement($page, "div.news-detail img");
          $facephoto_el = $facephoto_el[0];
        }

        $photos = $yupi->parseToElement($page, 'div.news-detail img');
        $camee = trim($yupi->parseToHtml($page, 'div.news-detail'));
        $camee = html_entity_decode(html_entity_decode_utf8($camee));
        $camee = preg_replace('/<img.*>/i', "", $camee);
        // удалим нижнюю часть новости
        $camee = preg_replace('/<div style="clear:both">.*/is', "", $camee);
        // удалим верхнюю часть новости
        $camee = preg_replace('/.*<br>Опубликовано:\\s<a\\shref="[^\"]*">[^<]*<\/a><br><br>/is', '', $camee);
        // удалим пустые блоки div с br и ссылками
        $camee = preg_replace('/<div[^>]*>\s*((<br>){1,}|<a[^>]*>.*<\/a>|\s{1,}\&nbsp;)<\/div>/is', '', $camee);
        // преобразуем блоки div в br
        $camee = preg_replace('/<div[^>]*>([^>]*)<\/div>/is', '$1<br>', $camee);
        // удаление длинных пустых строк
        $camee = preg_replace('/<br><br>([\s+ ]{1,})<br><br>/is', '<br><br>', $camee);
        //$camee = preg_replace('/rel="nofollow">.*/is', "", $camee);
        //$camee = preg_replace("/^.*<\/a>/is", "", $camee);
        $camee = strip_tags($camee, '<br>');
        //$camee = preg_replace("/^(<br>)(\\1+)/", "", $camee);
        //$camee = preg_replace("/(<br>)(\\1+)$/", "", trim($camee));
        //$camee = preg_replace("/(<br>)(\\1+)$/", "", trim($camee));
        //$camee = preg_replace("/(<br>)(\\1+)/", "", trim($camee));

        if (mb_strlen($camee) < 10) continue;

        $src = $facephoto_el->getAttribute('src');
        if (!stristr($src, 'sterlitamak.ru')) $src = 'http://sterlitamak.ru'. $src;
        if (!stristr($src, 'http:')) $src = 'http:'. $src;

        $fp = json_decode(
          $yupi->captureByCS('http://cs1.e-bash.me/capture.php', 'photo', $src),
          true
        );

        $cleaning = explode("<br>", $camee);
        foreach ($cleaning as &$line) {
          $line = trim($line);
        }
        $camee = implode("<br>", $cleaning);

        $breaks = array("<br />","<br>","<br/>");
        $camee = str_ireplace($breaks, "\r\n", $camee);

        $news = new News();
        $news->title = $name;
        $news->fullstory = $camee;
        $news->author_id = $author_id;
        $news->city_id = $city_id;
        $news->facephoto = json_encode($fp['result']);

        //echo '<h4>'. $name .'</h4>';
        //echo $camee;

        if ($news->save()) $log_events++;

        $news->add_date = $add_date->format("Y-m-d H:i:s");
        $news->save(true, array('add_date'));
      }

      $log->end_dt = date("Y-m-d H:i:s");
      $log->message .= " добавил новых новостей: ". $log_events;
      $log->save(true, array('end_dt', 'message'));

      Yii::app()->mutex->unlock();
      return 0;
    }
    else return 1;
  }
  /**
   * Мираж Синема
   *
   * @return int
   */
  public function actionMirage() {
    if (Yii::app()->mutex->lock('parser-sterlitamak-mirage', 1)) {
      set_time_limit(240);

      Yii::import('orgs.models.*');
      Yii::import('application.vendors.*');
      require_once 'Zend/Http/Client.php';
      require_once 'Zend/Dom/Query.php';

      $poet = Poet::getInstance();
      $yupi = new Yupi(array(
        'enable_retries' => true,
      ));

      header("Content-Type: text/html; charset=utf-8");

      $log = new ParserLog();
      $log->start_dt = date("Y-m-d H:i:s");
      $log->message = "Парсер кинотеатра Мираж синема";
      $log->save();

      $log_events = 0;

      $content = $yupi->get("http://mirage.ru/kinoteatr/9/kinoteatr_9.htm", "windows-1251");
      $container = $yupi->parseToHtml($content, 'div.main > div.static_content > div.static_left');
      $org_id = 3;
      $event_type_id = 1;

      $source_tz = new DateTimeZone('Asia/Yekaterinburg');
      $target_tz = new DateTimeZone('Europe/Moscow');

      $film_caches = array();

      $events = $yupi->parseToHtml($container, 'table.cinema > tr');
      foreach ($events as $event) {
        $el = $yupi->parseToElement($event, 'td > a');
        $vl = $yupi->parseToValue($event, 'td');

        $criteria = new CDbCriteria();
        $criteria->compare('org_id', $org_id);
        $criteria->compare('name', trim($vl[2]));
        $room = Room::model()->find($criteria);

        $now = new DateTime('now', $source_tz);
        $time = explode(":", trim($vl[3]));

        if ($time[0] <= 5) $now->add(DateInterval::createFromDateString('1 day'));

        $now->setTime($time[0], $time[1]);
        $now->setTimezone($target_tz);

        $found = Event::model()->find('org_id = :oid AND room_id = :rid AND title = :title AND start_time = :start', array(
          ':oid' => $org_id,
          ':rid' => $room->room_id,
          ':title' => $vl[0],
          ':start' => $now->format('Y-m-d H:i')
        ));
        if ($found) continue;

        if (!isset($film_caches[$el->getAttribute('href')])) {
          $film_content = $yupi->get("http://mirage.ru". $el->getAttribute('href'), "windows-1251");
          $photo_element = $yupi->parseToElement($film_content, '#example1');

          $return = json_decode(
            $yupi->captureByCS('http://cs1.e-bash.me/capture.php', 'photo', 'http://mirage.ru'. $photo_element[0]->getAttribute('href')),
            true
          );

          $description = $yupi->parseToValue($film_content, 'div[style="text-align:justify;"]');

          $film_caches[$el->getAttribute('href')] = array('photo' => json_encode($return['result']), 'description' => $description);
        }

        $eve = new Event();
        $eve->room_id = $room->room_id;
        $eve->org_id = $org_id;
        $eve->title = $vl[0];
        $eve->photo = $film_caches[$el->getAttribute('href')]['photo'];
        $eve->shortstory = $film_caches[$el->getAttribute('href')]['description'];
        $eve->event_type_id = $event_type_id;
        $eve->price = 'От '. trim(preg_replace("/(\d+).*/ui", "$1", $vl[4])) .' руб.';
        $eve->start_time = $now->format('Y-m-d H:i');
        if ($eve->save()) $log_events++;

        //usleep(500 * 1000);
      }

      $log->end_dt = date("Y-m-d H:i:s");
      $log->message .= " добавил новых событий: ". $log_events;
      $log->save(true, array('end_dt', 'message'));

      Yii::app()->mutex->unlock();
      return 0;
    }
    else return 1;
  }

  /**
   * Кинотеатр Салават
   */
  public function actionSalavat() {
    if (Yii::app()->mutex->lock('parser-sterlitamak-salavat', 55)) {
      set_time_limit(240);

      Yii::import('orgs.models.*');
      Yii::import('application.vendors.*');
      require_once 'Zend/Http/Client.php';
      require_once 'Zend/Dom/Query.php';

      $poet = Poet::getInstance();
      $yupi = new Yupi(array(
        'enable_retries' => true,
      ));

      header("Content-Type: text/html; charset=utf-8");

      $log = new ParserLog();
      $log->start_dt = date("Y-m-d H:i:s");
      $log->message = "Парсер кинотеатра Салават";
      $log->save();

      $log_events = 0;

      $content = $yupi->get("http://cityopen.ru/?page_id=8831");
      $container = $yupi->parseToHtml($content, '#post-8831');
      $org_id = 7;
      $event_type_id = 1;

      $source_tz = new DateTimeZone('Asia/Yekaterinburg');
      $target_tz = new DateTimeZone('Europe/Moscow');
      $month_table = array(
        'января' => '01',
        'февраля' => '02',
        'марта' => '03',
        'апреля' => '04',
        'мая' => '05',
        'июня' => '06',
        'июля' => '07',
        'августа' => '08',
        'сентября' => '09',
        'октября' => '10',
        'ноября' => '11',
        'декабря' => '12',
      );

      $film_caches = array();

      $elements = $yupi->parseToHtml($container, '#post-8831 > table');

      $now = new DateTime('now');
      $now->setTimezone($source_tz);
      $now->setTime(0, 0);

      for ($i = 0; $i < sizeof($elements); $i += 3) {
        $events_date = $yupi->parseToValue($elements[$i], 'td > strong');
        $date_range = array();
        $founded = false;

        if (preg_match("/с[ ]{1}(\d+)[ ]{1}([А-я]+)[ ]{1}по[ ]{1}(\d+)[ ]{1}([А-я]+)[ ]{1}(\d+)/ui", $events_date, $date_match)) {
          $step_date = new DateTime($date_match[1] .".". $month_table[$date_match[2]] .".". $date_match[5], $source_tz);
          $end_date = new DateTime($date_match[3] .".". $month_table[$date_match[4]] .".". $date_match[5], $source_tz);

          while ($step_date <= $end_date) {
            if ($now == $step_date) $founded = true;

            $date_range[] = clone $step_date;
            $step_date->add(DateInterval::createFromDateString('1 day'));
          }
        }
        elseif (preg_match("/с[ ]{1}(\d+)[ ]{1}по[ ]{1}(\d+)[ ]{1}([А-я]+)[ ]{1}(\d+)/ui", $events_date, $date_match)) {
          $step_date = new DateTime($date_match[1] .".". $month_table[$date_match[3]] .".". $date_match[4], $source_tz);
          $end_date = new DateTime($date_match[2] .".". $month_table[$date_match[3]] .".". $date_match[4], $source_tz);

          while ($step_date <= $end_date) {
            if ($now == $step_date) $founded = true;

            $date_range[] = clone $step_date;
            $step_date->add(DateInterval::createFromDateString('1 day'));
          }
        }
        elseif (preg_match("/c\\s+(\\d+)\\s+по\\s+(\\d+)\\s+([А-я]+)\\s+(\\d+)/uis", $events_date, $date_match)) {
          $step_date = new DateTime($date_match[1] .".". $month_table[$date_match[3]] .".". $date_match[4], $source_tz);
          $end_date = new DateTime($date_match[2] .".". $month_table[$date_match[3]] .".". $date_match[4], $source_tz);

          while ($step_date <= $end_date) {
            if ($now == $step_date) $founded = true;

            $date_range[] = clone $step_date;
            $step_date->add(DateInterval::createFromDateString('1 day'));
          }
        }
        elseif (preg_match("/с[ ]{1}(\d+)[ ]{1}по[ ]{1}(\d+)[ ]{1}([А-я]+)[ ]{1}и[ ]{1}(\d+)[ ]{1}([А-я]+)[ ]{1}(\d+)/ui", $events_date, $date_match)) {
          $step_date = new DateTime($date_match[1] .".". $month_table[$date_match[3]] .".". $date_match[6], $source_tz);
          $end_date = new DateTime($date_match[4] .".". $month_table[$date_match[5]] .".". $date_match[6], $source_tz);

          while ($step_date <= $end_date) {
            if ($now == $step_date) $founded = true;

            $date_range[] = clone $step_date;
            $step_date->add(DateInterval::createFromDateString('1 day'));
          }
        }
        else {
          preg_match("/(\d+)[ ]{1}([А-я]+)[ ]{1}(\d+)/ui", $events_date, $date_match);
          $step_date = new DateTime($date_match[1] .".". $month_table[$date_match[2]] .".". $date_match[3], $source_tz);

          if ($now == $step_date) $founded = true;
          $date_range[] = clone $step_date;
        }

        if (!$founded) continue;

        // Area 01
        $events = $yupi->parseToHtml($elements[$i+1], 'tr');
        $room_name = $yupi->parseToValue($events[0], 'td');

        $criteria = new CDbCriteria();
        $criteria->compare('org_id', $org_id);
        $criteria->compare('name', trim($room_name[0]));
        $room = Room::model()->find($criteria);

        for ($j = 1; $j < sizeof($events); $j++) {
          $event_data = $yupi->parseToValue($events[$j], 'td');
          $film_link = $yupi->parseToElement($events[$j], 'td > a[href*="cityopen"]');

          $now = new DateTime('now', $source_tz);
          $time = explode(":", trim($event_data[2]));

          if (!isset($time[1])) continue;
          if ($time[0] <= 5) $now->add(DateInterval::createFromDateString('1 day'));

          $now->setTime($time[0], $time[1]);
          $now->setTimezone($target_tz);

          $found = Event::model()->find('org_id = :oid AND room_id = :rid AND title = :title AND start_time = :start', array(
            ':oid' => $org_id,
            ':rid' => $room->room_id,
            ':title' => $event_data[0],
            ':start' => $now->format('Y-m-d H:i')
          ));
          if ($found) continue;

          if ($film_link) {
            //var_dump($event_data);
            if (is_array($film_link)) $film_link = $film_link[0];

            if (!isset($film_caches[$film_link->getAttribute('href')])) {
              $film_content = $yupi->get($film_link->getAttribute('href'));
              $photo_element = $yupi->parseToElement($film_content, 'div.one-post > p img');
              $description = $yupi->parseToValue($film_content, 'div.one-post > p');

              $return = json_decode(
                $yupi->captureByCS('http://cs1.e-bash.me/capture.php', 'photo', $photo_element[0]->getAttribute('src')),
                true
              );

              $film_caches[$film_link->getAttribute('href')] = array('photo' => json_encode($return['result']), 'description' => $description[1]);
            }

            $eve = new Event();
            $eve->room_id = $room->room_id;
            $eve->org_id = $org_id;
            $eve->title = $event_data[0];
            $eve->photo = $film_caches[$film_link->getAttribute('href')]['photo'];
            $eve->shortstory = $film_caches[$film_link->getAttribute('href')]['description'];
            $eve->event_type_id = $event_type_id;
            $eve->price = $event_data[4] .' руб.';

            $now = new DateTime('now', $source_tz);
            $time = explode(":", trim($event_data[2]));

            if ($time[0] <= 5) $now->add(DateInterval::createFromDateString('1 day'));

            $now->setTime($time[0], $time[1]);
            $now->setTimezone($target_tz);

            $eve->start_time = $now->format('Y-m-d H:i');
            if ($eve->save()) $log_events++;
          }
        }

        // Area 02
        $events = $yupi->parseToHtml($elements[$i+2], 'tr');
        $room_name = $yupi->parseToValue($events[0], 'td');

        $criteria = new CDbCriteria();
        $criteria->compare('org_id', $org_id);
        $criteria->compare('name', trim($room_name[0]));
        $room = Room::model()->find($criteria);

        for ($j = 1; $j < sizeof($events); $j++) {
          $event_data = $yupi->parseToValue($events[$j], 'td');
          $film_link = $yupi->parseToElement($events[$j], 'td > a[href*="cityopen"]');

          $now = new DateTime('now', $source_tz);
          $time = explode(":", trim($event_data[2]));

          if (!isset($time[1])) continue;
          if ($time[0] <= 5) $now->add(DateInterval::createFromDateString('1 day'));

          $now->setTime($time[0], $time[1]);
          $now->setTimezone($target_tz);

          $found = Event::model()->find('org_id = :oid AND room_id = :rid AND title = :title AND start_time = :start', array(
            ':oid' => $org_id,
            ':rid' => $room->room_id,
            ':title' => $event_data[0],
            ':start' => $now->format('Y-m-d H:i')
          ));
          if ($found) continue;

          if ($film_link) {
            if (is_array($film_link)) $film_link = $film_link[0];

            if (!isset($film_caches[$film_link->getAttribute('href')])) {
              $film_content = $yupi->get($film_link->getAttribute('href'));
              $photo_element = $yupi->parseToElement($film_content, 'div.one-post > p img');
              $description = $yupi->parseToValue($film_content, 'div.one-post > p');

              $return = json_decode(
                $yupi->captureByCS('http://cs1.e-bash.me/capture.php', 'photo', $photo_element[0]->getAttribute('src')),
                true
              );

              $film_caches[$film_link->getAttribute('href')] = array('photo' => json_encode($return['result']), 'description' => $description[1]);
            }

            $eve = new Event();
            $eve->room_id = $room->room_id;
            $eve->org_id = $org_id;
            $eve->title = $event_data[0];
            $eve->photo = $film_caches[$film_link->getAttribute('href')]['photo'];
            $eve->shortstory = $film_caches[$film_link->getAttribute('href')]['description'];
            $eve->event_type_id = $event_type_id;
            $eve->price = $event_data[4] .' руб.';

            $now = new DateTime('now', $source_tz);
            $time = explode(":", trim($event_data[2]));

            if ($time[0] <= 5) $now->add(DateInterval::createFromDateString('1 day'));

            $now->setTime($time[0], $time[1]);
            $now->setTimezone($target_tz);

            $eve->start_time = $now->format('Y-m-d H:i');
            if ($eve->save()) $log_events++;
          }
        }
        //var_dump($events);
      }

      $log->end_dt = date("Y-m-d H:i:s");
      $log->message .= " добавил новых событий: ". $log_events;
      $log->save(true, array('end_dt', 'message'));

      Yii::app()->mutex->unlock();
      return 0;
    }
    else return 1;
  }
}