<?php
$this->pageTitle = Yii::app()->name . ' - Категории доставки';

Yii::app()->getClientScript()->registerCssFile('/css/delivery.css');
Yii::app()->getClientScript()->registerScriptFile('/js/delivery_categories.js');

$delta = Yii::app()->getModule('orgs')->deliveryCategoriesPerPage;
?>
  <div class="orgs_search_wrap clear_fix">
    <div rel="filters" class="fl_l">
    </div>
    <div class="fl_r">
      <div class="button_blue">
        <button onclick="DeliveryCategory.add()">Добавить категорию</button>
      </div>
    </div>
  </div>
  <div class="summary_wrap clear_fix">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/orgs/delivery/categories',
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="summary"><?php echo Yii::t('app', '{n} категория|{n} категории|{n} категорий', $offsets) ?></div>
  </div>

  <div class="results_wrap">
    <table class="delivery_categories_table">
      <thead>
      <tr>
        <th width="">Имя</th>
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
DeliveryCategory.init();
HTML;
