<?php
/**
 * @var Organization $org
 */
$this->pageTitle = Yii::app()->name . ' - Товары и услуги';

Yii::app()->getClientScript()->registerCssFile('/css/market.css');
Yii::app()->getClientScript()->registerScriptFile('/js/market.js');

$categoriesJs = array();
foreach ($categories as $category) {
  $categoriesJs[] = "[". $category->category_id .",'". $category->name ."',true,true]";
  foreach ($category->childs as $child) {
    $categoriesJs[] = "[". $child->category_id .",'". $child->name ."',false,false,2]";
  }
}
$categoriesJs = implode(",", $categoriesJs);

$delta = Yii::app()->getModule('market')->marketGoodsPerPage;

$url_suffix = array();
if (sizeof($c) > 0) {
  foreach ($c as $c_key => $c_value) {
    $url_suffix[] = "c[". $c_key ."]=". urlencode($c_value);
  }
}
?>
  <div id="header">
    <a href="/org<?php echo $org->org_id ?>" onclick="return nav.go(this, event)"><?php echo $org->name ?></a> &raquo;
    Товары и услуги
  </div>
  <div class="orgs_search_wrap clear_fix">
    <div rel="filters" class="fl_l">
      <?php echo ActiveHtml::textField('c[name]', (isset($c['name'])) ? $c['name'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить название товара или услуги')) ?>
    </div>
    <div class="fl_l" style="margin-left: 10px">
      <input type="hidden" id="c[category_id]" name="c[category_id]" value="<?php echo (isset($c['category_id'])) ? $c['category_id'] : '' ?>">
    </div>
    <div class="fl_r">
      <div class="button_blue">
        <button onclick="Market.addGood(<?php echo $org->org_id ?>)">Добавить товар или услугу</button>
      </div>
    </div>
  </div>
  <div class="summary_wrap">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/orgs/market/index?id='. $org->org_id . ((sizeof($url_suffix) > 0) ? '&'. implode('&', $url_suffix) : ''),
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="fl_r progress"></div>
    <div class="summary"><?php echo Yii::t('app', '{n} товар|{n} товара|{n} товаров', $offsets) ?></div>
  </div>

  <div class="results_wrap">
    <?php echo $this->renderPartial('_goodlist', array('goods' => $goods, 'offset' => $offset)) ?>
  </div>
<?php
$this->pageJS = <<<HTML
Market.init({categories: [$categoriesJs]});
HTML;
