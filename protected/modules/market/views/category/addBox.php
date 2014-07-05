<?php
/** @var AdvertCategory $category
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/market.css');
Yii::app()->getClientScript()->registerScriptFile('/js/market_categories.js');

$categoriesJs = array();
foreach ($categories as $cat) {
  $categoriesJs[] = "[". $cat->category_id .",'". $cat->name ."']";
}
$categoriesJs = implode(",", $categoriesJs);

$this->pageTitle = 'Добавить новую категорию товаров';
?>
  <div class="market_category_content">
    <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
      'id' => 'type_form'
    )); ?>
    <div id="market_category_result"></div>
    <div id="market_category_error" class="error"></div>
    <div class="market_category_header"><?php echo $category->getAttributeLabel('parent_id') ?></div>
    <div class="market_category_param"><?php echo ActiveHtml::hiddenField('parent_id', $category->parent_id) ?></div>
    <div class="market_category_header"><?php echo $category->getAttributeLabel('name') ?></div>
    <?php echo ActiveHtml::textField('name', $category->name, array('class' => 'text')) ?>
    <div class="market_category_param"><?php echo ActiveHtml::hiddenField('no_title', $category->no_title) ?></div>
    <div class="market_category_param"><?php echo ActiveHtml::hiddenField('no_price', $category->no_price) ?></div>
    <div id="aac_title_label" class="market_category_header"<?php if (!$category->no_title): ?> style="display: none"<?php endif; ?>><?php echo $category->getAttributeLabel('title_form') ?></div>
    <div id="aac_title" class="market_category_param"<?php if (!$category->no_title): ?> style="display: none"<?php endif; ?>><?php echo ActiveHtml::textField('title_form', $category->title_form, array('class' => 'text')) ?></div>
    <?php $this->endWidget(); ?>
  </div>
<?php
$this->pageJS = <<<HTML
MarketCategory.initForm({
  categories: [$categoriesJs]
});
HTML;
