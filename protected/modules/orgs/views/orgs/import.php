<?php
$this->pageTitle = Yii::app()->name . ' - Импорт организаций';

Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/orgs.js');

$delta = Yii::app()->getModule('orgs')->importPerPage;

$url_suffix = array();
?>

  <div class="data_table_filters clear_fix">
    <div class="fl_r" style="padding: 15px 15px 0px 0px">
      <div class="button_blue">
        <button onclick="Org.import()">Импортировать данные</button>
      </div>
    </div>
  </div>

  <div class="summary_wrap clear_fix">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/orgs/orgs/import'. ((sizeof($url_suffix) > 0) ? '?'. implode('&', $url_suffix) : ''),
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="fl_r progress"></div>
  </div>

  <div class="results_wrap">
    <table class="data_table">
      <thead>
      <tr>
        <th>Файл <span id="data_table_count"><?php echo $offsets ?></span></th>
        <th>Город</th>
        <th>
          Данные
          <span class="data_table_hint" onmouseover="tooltips.show(this, 'Загружено данных / Всего данных', [10,5])">[?]</span>
        </th>
        <th>Статус</th>
        <th>Действия</th>
      </tr>
      </thead>
      <tbody rel="pagination">
      <?php if ($offsets == 0): ?>
        <tr>
          <td class="not_found" colspan="4">
            <div class="data_not_found table">Не было загружено еще ни одного файла данных.</div>
          </td>
        </tr>
      <?php else: ?>
      <?php echo $this->renderPartial('_importlist', array('import' => $import, 'offset' => $offset)) ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
<?php
$this->pageJS = <<<HTML
HTML;
