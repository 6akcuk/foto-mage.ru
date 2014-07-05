<?php
$this->pageTitle = Yii::app()->name . ' - Категории объявлений';

Yii::app()->getClientScript()->registerCssFile('/css/advert.css');
Yii::app()->getClientScript()->registerScriptFile('/js/advert_categories.js');

$delta = Yii::app()->getModule('advert')->advertCategoriesPerPage;
?>
  <div class="advert_search_wrap clear_fix">
    <div rel="filters" class="fl_l">
      <?php echo ActiveHtml::textField('c[name]', (isset($c['name'])) ? $c['name'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить название категории')) ?>
    </div>
    <div class="fl_r">
      <div class="button_blue">
        <button onclick="AdvertCategory.add()">Добавить категорию</button>
      </div>
    </div>
  </div>
  <div class="summary_wrap">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/advert/category/index',
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="summary"><?php echo Yii::t('app', '{n} категория|{n} категории|{n} категорий', $offsets) ?></div>
  </div>

  <div class="results_wrap">
    <table class="advert_table">
      <thead>
      <tr>
        <th width="">Название</th>
        <th>Родительская категория</th>
        <th>Действия</th>
      </tr>
      </thead>
      <tbody rel="pagination">
      <?php echo $this->renderPartial('_categorylist', array('categories' => $categories, 'offset' => $offset)) ?>
      </tbody>
    </table>
  </div>
<?php
$this->pageJS = <<<HTML
AdvertCategory.init();
HTML;
