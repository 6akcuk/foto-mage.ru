<?php

/**
 * Class CustomData
 * @property integer $id;
 * @property string $data;
 */
class CustomData extends CActiveRecord {
  /**
   * Returns the static model of the specified AR class.
   * @param string $className active record class name.
   * @return CustomData the static model class
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
    return 'custom_data';
  }
}

class CustomCommand extends CConsoleCommand {
  public function actionM() {
    Yii::import('orgs.models.*');
    Yii::import('application.vendors.*');

    $orgs = Organization::model()->findAll('org_type_id > 0');
    /** @var Organization $org */
    foreach ($orgs as $org) {
      $link = new OrganizationTypeLink();
      $link->org_id = $org->org_id;
      $link->org_type_id = $org->org_type_id;
      $link->save();

      $org->org_type_id = 0;
      $org->save(true, array('org_type_id'));
    }
  }

  public function actionLena3() {
    Yii::import('news.models.*');
    Yii::import('application.vendors.*');
    require_once 'Zend/Http/Client.php';
    require_once 'Zend/Dom/Query.php';

    $yupi = new Yupi(array(
      'enable_retries' => true,
    ));

    header("Content-Type: text/html; charset=utf-8");

    $fed_levels = array(
      'F' => 'Федеральный уровень',
      'S' => 'Уровень Субъекта РФ',
      'M' => 'Муниципальный уровень',
    );


    $page_param = "d-442831-p=";
    $fed_pages = 5;
    $pages = 1400;
    $counter = 1;

    $rdata = explode(" ", file_exists('/var/www/protected/runtime/cache.custom.lena3.run') ? file_get_contents('/var/www/protected/runtime/cache.custom.lena3.run') : "F 1 0 1");

    foreach ($fed_levels as $agencyLevel => $fedLevel) {
      if ($rdata[0] == 'S' && $agencyLevel == 'F') continue;
      if ($rdata[0] == 'M' && ($agencyLevel == 'F' || $agencyLevel == 'S')) continue;

      for ($i = 1; $i <= $fed_pages; $i++) {
        if ($i < $rdata[1]) continue;

        $url = "http://bus.gov.ru/public/analytics/agencies/summary.html?searchType=ALL&_districtIDs=on&_regionIDs=on&agencyTypes=A&agencyTypes=B&agencyTypes=C&_agencyTypes=on&agencyLevels=". $agencyLevel ."&_agencyLevels=on&vguSubElements=true&_vguSubElements=on&withBranches=true&selectedAgencyIDs=&selectedOkvedIDs=&vguIds=";
        $url .= "&". $page_param . $i;

        $content = $yupi->get($url);
        $federal_rows = $yupi->parseToHtml($content, '#agency tbody tr');
        if (!is_array($federal_rows)) $federal_rows = array($federal_rows);

        foreach ($federal_rows as $federal_id => $federal_row) {
          if ($i == $rdata[1] && $federal_id < $rdata[2]) continue;

          $county = trim($yupi->parseToValue($federal_row, 'td.secondColumn'));
          $subject = trim($yupi->parseToValue($federal_row, 'td.thirdColumn'));
          $href = $yupi->parseToElement($federal_row, 'td.lastColumn a');

          if (!$href) break;
          $dataPages = ceil($href->nodeValue / 20);

          echo "Найдено страниц: ". $dataPages ."\n";

          for ($j = 1; $j <= $dataPages; $j++) {
            if ($i == $rdata[1] && $federal_id == $rdata[2] && $j < $rdata[3]) continue;

            echo "Страница ". $j ."\n";
            echo "-----------\n";

            $dataUrl = "http://bus.gov.ru". $href->getAttribute('href');
            $dataUrl .= "&". $page_param . $j;
            $content = $yupi->get($dataUrl);

            $agency_rows = $yupi->parseToHtml($content, '#agency > tbody > tr');
            if (!is_array($agency_rows)) $agency_rows = array($agency_rows);

            foreach ($agency_rows as $agency_row) {
              $agency_href = $yupi->parseToElement($agency_row, 'td a');
              if (is_array($agency_href)) $agency_href = $agency_href[0];
              preg_match("/agency=(\d+)$/ui", $agency_href->getAttribute('href'), $agency_id);
              if (!isset($agency_id[1])) exit;

              $agency_id = $agency_id[1];

              $record = CustomData::model()->findByPk($agency_id);
              if ($record) {
                //echo "ID ". $agency_id ." найден в кэше\n";
                continue;
              }

              $tds = $yupi->parseToValue($agency_row, 'td');

              $nmo = trim($tds[1]);
              $nu = trim($tds[2]);
              $tu = trim($tds[3]);
              $site = trim($tds[4]);
              $vd = trim($tds[5]);

              $snu = "-";
              $oof = "-";
              $ngr = "-";
              $nrbs = "-";
              $vu = "-";
              $ovd = "-";
              $ivd = "-";
              $vs = "-";
              $phone = "-";
              $email = "-";
              $zakupki = "-";

              $content = $yupi->get("http://bus.gov.ru". $agency_href->getAttribute('href'));
              $container = $yupi->parseToHtml($content, 'div.agencyDocumentTab > table tbody');
              if (!is_array($container)) $container = array($container);

              $rows = $yupi->parseToHtml($container[0], 'tr');

              if (!is_array($rows)) {
                echo $agency_href->getAttribute('href') ."\n";
                echo $rows;
              }

              foreach ($rows as $row) {
                $cols = $yupi->parseToValue($row, 'td');

                switch (trim($cols[0])) {
                  case "Сокращенное наименование учреждения":
                    $snu = trim($cols[1]);
                    break;
                  case "Органы, осуществляющие функции и полномочия учредителя":
                    $oof = trim($cols[1]);
                    break;
                  case "Наименование главного распорядителя бюджетных средств":
                    $ngr = trim($cols[1]);
                    break;
                  case "Наименование распорядителя бюджетных средств":
                    $nrbs = trim($cols[1]);
                    break;
                  case "Вид учреждения":
                    $vu = trim($cols[1]);
                    break;
                  case "Основные виды деятельности по ОКВЭД":
                    $ovd = trim($cols[1]);
                    break;
                  case "Иные виды деятельности по ОКВЭД":
                    $ivd = trim($cols[1]);
                    break;
                  case "Вид собственности (по ОКФС)":
                    $vs = trim($cols[1]);
                    break;
                  case "Адрес фактического местонахождения":
                    $address = explode(",", trim($cols[1]));
                    $index = trim($address[0]);
                    $addr = trim($cols[1]);
                    $sub = trim($address[1]);
                    $city = (isset($address[2])) ? trim($address[2]) : "-";
                    $street = (isset($address[3])) ? trim($address[3]) : "-";
                    break;
                  case "Руководитель":
                    $header = trim($cols[1]);
                    break;
                  case "Контактный телефон":
                    $phone = trim($cols[1]);
                    break;
                  case "Адрес электронной почты":
                    $email = trim($cols[1]);
                    break;
                }
              }

              if (isset($container[1])) {
                $rows = $yupi->parseToHtml($container[1], 'tr');
                if (!is_array($rows)) $rows = array($rows);

                foreach ($rows as $row) {
                  $cols = $yupi->parseToValue($row, 'td');

                  switch (trim($cols[0])) {
                    case "Заказы учреждения":
                      $zak = $yupi->parseToElement($row, 'td a');
                      $zakupki = $zak->getAttribute('href');
                      break;
                  }
                }
              }

              //$xcache['ids'][] = $agency_id;
              $record = new CustomData();
              $record->id = $agency_id;
              $record->data = json_encode( array(
                $county, $subject, $nmo, $nu, $tu, $fedLevel, $site, $vd, $snu, $oof, $ngr, $nrbs, $tu, $vu, $ovd, $ivd,
                $vs, $index, $addr, $header, $phone, $site, $email, $zakupki,
              ) );
              $record->save();

              echo $counter .". ". $nu ."\n";
              $counter++;

              usleep(100000);
            }

            echo "------------\n";
            file_put_contents('/var/www/protected/runtime/cache.custom.lena3.run', $agencyLevel ." ". $i ." ". $federal_id ." ". $j);
          }
        }
      }
    }

    $excel = new Excel();
    $excel->line(array(
      "Федеральный округ", "Субъект РФ", "Наименование муниципального образования",	"Наименование учреждения", "Тип учреждения",
      "Уровень учреждения",	"Сайт учреждения", "Виды деятельности", "Сокращенное наименование учреждения",
      "Органы, осуществляющие функции и полномочия учредителя", "Наименование главного распорядителя бюджетных средств",
      "Наименование распорядителя бюджетных средств", "Тип учреждения", "Вид учреждения", "Основные виды деятельности по ОКВЭД",
      "Иные виды деятельности по ОКВЭД", "Вид собственности (по ОКФС)", "Индекс", "Адрес", "Руководитель",
      "Контактный телефон", "Сайт учреждения", "Адрес электронной почты", "Заказы учреждения",
    ));

    /** @var CDbConnection $db */
    $db = Yii::app()->db;
    $command = $db->createCommand("SELECT * FROM custom_data");
    $reader = $command->query();

    while (($row = $reader->read()) !== false) {
      $line = json_decode($row['data'], true);
      $excel->line($line);
    }

    file_put_contents('/var/www/protected/runtime/bus3.xls', $excel->close());
  }

  public function actionLena2() {
    Yii::import('news.models.*');
    Yii::import('application.vendors.*');
    require_once 'Zend/Http/Client.php';
    require_once 'Zend/Dom/Query.php';

    $yupi = new Yupi(array(
      'enable_retries' => true,
    ));

    header("Content-Type: text/html; charset=utf-8");

    $page_param = "d-442831-p=";
    $fed_pages = 5;
    $pages = 1400;
    $counter = 1;

    $rdata = explode(" ", file_exists('/var/www/protected/runtime/cache.custom.lena2.run') ? file_get_contents('/var/www/protected/runtime/cache.custom.lena2.run') : "1 0 1");

    for ($i = 1; $i <= $fed_pages; $i++) {
      if ($i < $rdata[0]) continue;

      $url = "http://bus.gov.ru/public/analytics/agencies/summary.html?searchType=ALL&_districtIDs=on&_regionIDs=on&agencyTypes=A&agencyTypes=B&agencyTypes=C&_agencyTypes=on&agencyLevels=S&agencyLevels=M&_agencyLevels=on&vguSubElements=true&_vguSubElements=on&withBranches=true&selectedAgencyIDs=&selectedOkvedIDs=&vguIds=855%2C891%2C951%2C967%2C970%2C981%2C1142%2C1167%2C1230%2C1234%2C1436%2C1446%2C1453%2C1457%2C15786";
      $url .= "&". $page_param . $i;

      $content = $yupi->get($url);
      $federal_rows = $yupi->parseToHtml($content, '#agency tbody tr');
      if (!is_array($federal_rows)) $federal_rows = array($federal_rows);

      foreach ($federal_rows as $federal_id => $federal_row) {
        if ($i == $rdata[0] && $federal_id < $rdata[1]) continue;

        $county = trim($yupi->parseToValue($federal_row, 'td.secondColumn'));
        $subject = trim($yupi->parseToValue($federal_row, 'td.thirdColumn'));
        $href = $yupi->parseToElement($federal_row, 'td.lastColumn a');

        if (!$href) break;
        $dataPages = ceil($href->nodeValue / 20);

        echo "Найдено страниц: ". $dataPages ."\n";

        for ($j = 1; $j <= $dataPages; $j++) {
          if ($i == $rdata[0] && $federal_id == $rdata[1] && $j < $rdata[2]) continue;

          echo "Страница ". $j ."\n";
          echo "-----------\n";

          $dataUrl = "http://bus.gov.ru". $href->getAttribute('href');
          $dataUrl .= "&". $page_param . $j;
          $content = $yupi->get($dataUrl);

          $agency_rows = $yupi->parseToHtml($content, '#agency > tbody > tr');
          if (!is_array($agency_rows)) $agency_rows = array($agency_rows);

          foreach ($agency_rows as $agency_row) {
            $agency_href = $yupi->parseToElement($agency_row, 'td a');
            if (is_array($agency_href)) $agency_href = $agency_href[0];
            preg_match("/agency=(\d+)$/ui", $agency_href->getAttribute('href'), $agency_id);
            if (!isset($agency_id[1])) exit;

            $agency_id = $agency_id[1];

            $record = CustomData::model()->findByPk($agency_id);
            if ($record) {
              //echo "ID ". $agency_id ." найден в кэше\n";
              continue;
            }

            $tds = $yupi->parseToValue($agency_row, 'td');

            $nmo = trim($tds[1]);
            $nu = trim($tds[2]);
            $tu = trim($tds[3]);
            $site = trim($tds[4]);
            $vd = trim($tds[5]);

            $snu = "-";
            $oof = "-";
            $ngr = "-";
            $nrbs = "-";
            $vu = "-";
            $ovd = "-";
            $ivd = "-";
            $vs = "-";
            $phone = "-";
            $email = "-";
            $zakupki = "-";

            $content = $yupi->get("http://bus.gov.ru". $agency_href->getAttribute('href'));
            $container = $yupi->parseToHtml($content, 'div.agencyDocumentTab > table tbody');
            if (!is_array($container)) $container = array($container);

            $rows = $yupi->parseToHtml($container[0], 'tr');

            if (!is_array($rows)) {
              echo $agency_href->getAttribute('href') ."\n";
              echo $rows;
            }

            foreach ($rows as $row) {
              $cols = $yupi->parseToValue($row, 'td');

              switch (trim($cols[0])) {
                case "Сокращенное наименование учреждения":
                  $snu = trim($cols[1]);
                  break;
                case "Органы, осуществляющие функции и полномочия учредителя":
                  $oof = trim($cols[1]);
                  break;
                case "Наименование главного распорядителя бюджетных средств":
                  $ngr = trim($cols[1]);
                  break;
                case "Наименование распорядителя бюджетных средств":
                  $nrbs = trim($cols[1]);
                  break;
                case "Вид учреждения":
                  $vu = trim($cols[1]);
                  break;
                case "Основные виды деятельности по ОКВЭД":
                  $ovd = trim($cols[1]);
                  break;
                case "Иные виды деятельности по ОКВЭД":
                  $ivd = trim($cols[1]);
                  break;
                case "Вид собственности (по ОКФС)":
                  $vs = trim($cols[1]);
                  break;
                case "Адрес фактического местонахождения":
                  $address = explode(",", trim($cols[1]));
                  $index = trim($address[0]);
                  $addr = trim($cols[1]);
                  $sub = trim($address[1]);
                  $city = trim($address[2]);
                  $street = (isset($address[3])) ? trim($address[3]) : "-";
                  break;
                case "Руководитель":
                  $header = trim($cols[1]);
                  break;
                case "Контактный телефон":
                  $phone = trim($cols[1]);
                  break;
                case "Адрес электронной почты":
                  $email = trim($cols[1]);
                  break;
              }
            }

            if (isset($container[1])) {
              $rows = $yupi->parseToHtml($container[1], 'tr');
              if (!is_array($rows)) $rows = array($rows);

              foreach ($rows as $row) {
                $cols = $yupi->parseToValue($row, 'td');

                switch (trim($cols[0])) {
                  case "Заказы учреждения":
                    $zak = $yupi->parseToElement($row, 'td a');
                    $zakupki = $zak->getAttribute('href');
                    break;
                }
              }
            }

            //$xcache['ids'][] = $agency_id;
            $record = new CustomData();
            $record->id = $agency_id;
            $record->data = json_encode( array(
              $county, $subject, $nmo, $nu, $tu, '-', $site, $vd, $snu, $oof, $ngr, $nrbs, $tu, $vu, $ovd, $ivd,
              $vs, $index, $addr, $header, $phone, $site, $email, $zakupki,
            ) );
            $record->save();
            /*
            echo $county ."\n";
            echo $subject ."\n";
            echo $nmo ."\n";
            echo $nu ."\n";
            echo $tu ."\n";
            echo '-';
            echo $site ."\n";
            echo $vd ."\n";
            echo $snu ."\n";
            echo $oof ."\n";
            echo $ngr ."\n";
            echo $nrbs ."\n";
            echo $tu ."\n";
            echo $vu ."\n";
            echo $ovd ."\n";
            echo $ivd ."\n";
            echo $vs ."\n";
            echo $index ."\n";
            echo $sub ."\n";
            echo $city ."\n";
            echo $street ."\n";
            echo $header ."\n";
            echo $phone ."\n";
            echo $site ."\n";
            echo $email ."\n";
            echo $zakupki ."\n";
            */

            echo $counter .". ". $nu ."\n";
            $counter++;

            //file_put_contents('/var/www/protected/runtime/cache.custom.lena2.data', json_encode($xcache));

            usleep(100000);
          }

          echo "------------\n";
          file_put_contents('/var/www/protected/runtime/cache.custom.lena2.run', $i ." ". $federal_id ." ". $j);
        }
      }
    }

    $excel = new Excel();
    $excel->line(array(
      "Федеральный округ", "Субъект РФ", "Наименование муниципального образования",	"Наименование учреждения", "Тип учреждения",
      "Уровень учреждения",	"Сайт учреждения", "Виды деятельности", "Сокращенное наименование учреждения",
      "Органы, осуществляющие функции и полномочия учредителя", "Наименование главного распорядителя бюджетных средств",
      "Наименование распорядителя бюджетных средств", "Тип учреждения", "Вид учреждения", "Основные виды деятельности по ОКВЭД",
      "Иные виды деятельности по ОКВЭД", "Вид собственности (по ОКФС)", "Индекс", "Адрес", "Руководитель",
      "Контактный телефон", "Сайт учреждения", "Адрес электронной почты", "Заказы учреждения",
    ));

    /** @var CDbConnection $db */
    $db = Yii::app()->db;
    $command = $db->createCommand("SELECT * FROM custom_data");
    $reader = $command->query();

    while (($row = $reader->read()) !== false) {
      $line = json_decode($row['data'], true);
      $excel->line($line);
    }

/*
    foreach ($xcache['data'] as $xc) {
      $excel->line($xc);
    }
*/
    file_put_contents('/var/www/protected/runtime/bus2.xls', $excel->close());
  }

  public function actionLena1() {
    Yii::import('news.models.*');
    Yii::import('application.vendors.*');
    require_once 'Zend/Http/Client.php';
    require_once 'Zend/Dom/Query.php';

    $yupi = new Yupi(array(
      'enable_retries' => true,
    ));

    header("Content-Type: text/html; charset=utf-8");

    $content = $yupi->get("http://www.bankgorodov.ru/region/fo.php", "windows-1251");
    $thread_list = $yupi->parseToElement($content, 'td > a[href*="region/region"]');

    $excel = new Excel();
    $excel->line(array(
      'Наименование компании', 'Федеральный округ', 'Субъект', 'Город', 'Индекс', 'Адрес', 'Телефон', 'Фамилия',
      'Имя', 'Отчество', 'Должность', 'Сайт', 'Ссылка BG'
    ));

    $rdata = explode(" ", file_exists('/var/www/protected/runtime/cache.custom.lena1.run') ? file_get_contents('/var/www/protected/runtime/cache.custom.lena1.run') : "-1 0");

    /** @var Zend_Dom_Query $thread */
    foreach ($thread_list as $idx => $thread) {
      if ($rdata[0] > $idx) continue;

      $thread_url = str_replace("..", "http://www.bankgorodov.ru", $thread->getAttribute('href'));
      echo $thread_url ." - ". $thread->nodeValue ."\n";

      $content = $yupi->get($thread_url, "windows-1251");
      $places = $yupi->parseToElement($content, 'td[width="20%"] > a[href*="place/inform"]');
      $districts = $yupi->parseToElement($content, 'td[width="20%"] > a[href*="region/raion"]');

      $subjects = array_merge((is_array($places)) ? $places : array($places), (is_array($districts)) ? $districts : array($districts));

      $federal = "";
      $federals = $yupi->parseToHtml($content, 'table[width="370"] tr');
      foreach ($federals as $row_id => $rows) {
        $td = $yupi->parseToValue($rows, 'td');

        if ($td[0] == "Федеральный округ:") {
          $federal = trim($td[3]);
        }
      }

      foreach ($subjects as $sub_id => $subject) {
        if ($subject == null) continue;
        if ($rdata[0] == $idx && $rdata[1] > $sub_id) continue;

        $subject_url = str_replace("..", "http://www.bankgorodov.ru", $subject->getAttribute('href'));
        echo $subject_url ." - ". $subject->nodeValue ."\n";

        $content = $yupi->get($subject_url, "windows-1251");
        $trs = $yupi->parseToHtml($content, 'td[class="right"] > table[width="100%"] > tr > td[style*="margin:0"] > table[width="100%"] tr');

        $lastname = "";
        $firstname = "";
        $middlename = "";
        $index = "";
        $address = "";
        $phones = "";
        $site = "";

        foreach ($trs as $tr_id => $tr) {
          $td = $yupi->parseToValue($tr, 'td');
          if (!isset($td[1]) || !isset($td[0])) continue;

          if (
            preg_match("/Глава\s+/ui", trim($td[0])) ||
            preg_match("/Префект/ui", trim($td[0])) ||
            preg_match("/Мэр/ui", trim($td[0]))
          ) {
            preg_match("/([А-я\-]*) ([А-я\-]*) ([А-я\-]*).*/ui", trim($td[1]), $headquarter);
            if (!isset($headquarter[1])) preg_match("/([А-я\-]*) ([А-я\-]*).*/ui", trim($td[1]), $headquarter);

            if (!trim($td[1])) continue;

            $lastname = $headquarter[1];
            $firstname = $headquarter[2];
            $middlename = (isset($headquarter[3])) ? $headquarter[3] : "";
          } elseif (trim($td[0]) == "Адрес администрации:") {
            $address = trim($td[1]);
            preg_match("/^(\d+)/ui", $address, $index);
            if (isset($index[1])) $index = $index[1];
            else $index = "";
          } elseif (trim($td[0]) == "Телефоны администрации:") {
            $phones = trim($td[1]);
          } elseif (trim($td[0]) == "Официальный сайт:") {
            $site = trim($td[1]);
          }
        }
/*
        $excel->line(array(
          'Администрация', $federal, $thread->nodeValue, $subject->nodeValue, $index, $address, $phones, $lastname,
          $firstname, $middlename, 'глава', $subject_url,
        ));
*/
        $cache = array(
          'Администрация', $federal, $thread->nodeValue, $subject->nodeValue, $index, $address, $phones, $lastname,
          $firstname, $middlename, 'глава', $site, $subject_url,
        );

        $xcache = file_exists('/var/www/protected/runtime/cache.custom.lena1.data') ? file_get_contents('/var/www/protected/runtime/cache.custom.lena1.data') : "[]";
        $cachedata = json_decode($xcache, true);
        $cachedata[] = $cache;
        file_put_contents('/var/www/protected/runtime/cache.custom.lena1.data', json_encode($cachedata));

        file_put_contents('/var/www/protected/runtime/cache.custom.lena1.run', $idx ." ". $sub_id);
      }

      echo "-----------\n";
    }

    $xcache = file_get_contents('/var/www/protected/runtime/cache.custom.lena1.data');
    $cachedata = json_decode($xcache, true);

    foreach ($cachedata as $data) {
      $excel->line($data);
    }

    file_put_contents('/var/www/protected/runtime/bg.xls', $excel->close());
  }
}