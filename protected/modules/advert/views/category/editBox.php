<?php
/** @var AdvertCategory $category
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/advert.css');
Yii::app()->getClientScript()->registerScriptFile('/js/advert_categories.js');

$categoriesJs = array();
foreach ($categories as $cat) {
  $categoriesJs[] = "[". $cat->category_id .",'". $cat->name ."']";
}
$categoriesJs = implode(",", $categoriesJs);

$this->pageTitle = 'Редактировать категорию объявлений';
?>
  <div class="advert_category_content">
    <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
      'id' => 'type_form'
    )); ?>
    <div id="advert_category_result"></div>
    <div id="advert_category_error" class="error"></div>
    <div class="advert_category_header"><?php echo $category->getAttributeLabel('parent_id') ?></div>
    <div class="advert_category_param"><?php echo ActiveHtml::hiddenField('parent_id', $category->parent_id, array('class' => 'text')) ?></div>
    <div class="advert_category_header"><?php echo $category->getAttributeLabel('name') ?></div>
    <?php echo ActiveHtml::textField('name', $category->name, array('class' => 'text')) ?>
    <div class="advert_category_param"><?php echo ActiveHtml::hiddenField('no_title', $category->no_title) ?></div>
    <div class="advert_category_param"><?php echo ActiveHtml::hiddenField('no_price', $category->no_price) ?></div>
    <div id="aac_title_label" class="advert_category_header"<?php if (!$category->no_title): ?> style="display: none"<?php endif; ?>><?php echo $category->getAttributeLabel('title_form') ?></div>
    <div id="aac_title" class="advert_category_param"<?php if (!$category->no_title): ?> style="display: none"<?php endif; ?>><?php echo ActiveHtml::textField('title_form', $category->title_form, array('class' => 'text')) ?></div>
    <?php $this->endWidget(); ?>
  </div>
<?php
$this->pageJS = <<<HTML
AdvertCategory.initForm({
  'categories': [$categoriesJs]
});
HTML;
