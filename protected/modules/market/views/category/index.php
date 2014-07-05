<?php
$this->pageTitle = Yii::app()->name . ' - Категории товаров';

Yii::app()->getClientScript()->registerCssFile('/css/market.css');
Yii::app()->getClientScript()->registerScriptFile('/js/market_categories.js');

$delta = Yii::app()->getModule('market')->marketCategoriesPerPage;
?>
  <div class="market_search_wrap clear_fix">
    <div rel="filters" class="fl_l">
      <?php echo ActiveHtml::textField('c[name]', (isset($c['name'])) ? $c['name'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить название категории')) ?>
    </div>
    <div class="fl_r">
      <div class="button_blue">
        <button onclick="MarketCategory.add()">Добавить категорию</button>
      </div>
    </div>
  </div>
  <div class="summary_wrap">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/market/category/index',
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="summary"><?php echo Yii::t('app', '{n} категория|{n} категории|{n} категорий', $offsets) ?></div>
  </div>

  <div class="results_wrap">
    <table class="market_table">
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
MarketCategory.init();
HTML;
