<?php

Yii::app()->getClientScript()->registerCssFile('/css/console.css');
Yii::app()->getClientScript()->registerScriptFile('/js/unify.console.js');

$this->pageTitle = Yii::app()->name . ' - Управление системой';

?>
<div class="console_container">
  <h2>Список ботов</h2>

  <table class="data_table">
    <tr>
      <th class="console_bot_name">Имя бота</th>
      <th>Описание</th>
      <th>Действия</th>
    </tr>
    <tr>
      <td>Парсер погоды</td>
      <td>
        Осуществляет сбор информации о погоде по всем городам с API Яндекс.Погода
      </td>
      <td>
        <div class="button_blue">
          <button onclick="UnifyConsole.runBot(this, 'weather')">Выполнить</button>
        </div>
      </td>
    </tr>
    <tr class="even">
      <td>Кинотеатр "Мираж синема"</td>
      <td>
        Сбор информации по киносеансам с сайта кинотеатра "Мираж синема" http://mirage.ru/
      </td>
      <td>
        <div class="button_blue">
          <button onclick="UnifyConsole.runBot(this, 'sterlitamak.mirage')">Выполнить</button>
        </div>
      </td>
    </tr>
    <tr>
      <td>Кинотеатр "Салават"</td>
      <td>
        Собирает информацию по киносеансам кинотеатра "Салават"
      </td>
      <td>
        <div class="button_blue">
          <button onclick="UnifyConsole.runBot(this, 'sterlitamak.salavat')">Выполнить</button>
        </div>
      </td>
    </tr>
    <tr class="even">
      <td>Новости Стерлитамака</td>
      <td>
        Вытягивание новостей города Стерлитамак с портала sterlitamak.ru
      </td>
      <td>
        <div class="button_blue">
          <button onclick="UnifyConsole.runBot(this, 'sterlitamak.news')">Выполнить</button>
        </div>
      </td>
    </tr>
  </table>
</div>
<?php
$this->pageJS = <<<HTML

HTML;
