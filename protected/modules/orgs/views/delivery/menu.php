<?php
$this->pageTitle = Yii::app()->name . ' - '. $org->name;

Yii::app()->getClientScript()->registerCssFile('/css/page.css');
Yii::app()->getClientScript()->registerCssFile('/css/delivery.css');
Yii::app()->getClientScript()->registerScriptFile('/js/delivery.js');

?>
  <div id="header" class="clear_fix">
    <a href="/org<?php echo $org->org_id ?>" onclick="return nav.go(this, event)"><?php echo $org->name ?></a> &raquo;
    <a href="/orgs/delivery/index?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)">Доставка</a> &raquo;
    Меню доставки
    <a onclick="Delivery.addMenuElement(<?php echo $org->org_id ?>)" class="fl_r header_right_link">Добавить элемент меню</a>
  </div>
  <div id="public" class="clear_fix">
  <?php if (sizeof($categories)): ?>
    <?php foreach ($categories as $cid => $data): ?>
    <div class="p_header_bottom"><?php echo $data['model']->category->name ?></div>
    <div class="clear_fix">
      <?php $this->renderPartial('_menuelementlist', array('elements' => $data['items'])) ?>
    </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div id="no_results">Меню доставки еще не заполнено. Начните с нажатия по ссылке в правом верхнем углу.</div>
  <?php endif; ?>
  </div>
<?php
$this->pageJS = <<<HTML

HTML;
