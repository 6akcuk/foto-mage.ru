<?php
$this->pageTitle = Yii::app()->name . ' - '. $org->name;

Yii::app()->getClientScript()->registerCssFile('/css/page.css');
Yii::app()->getClientScript()->registerCssFile('/css/delivery.css');
Yii::app()->getClientScript()->registerScriptFile('/js/delivery.js');

?>
  <div id="header">
    <a href="/org<?php echo $org->org_id ?>" onclick="return nav.go(this, event)"><?php echo $org->name ?></a> &raquo;
    Доставка
  </div>
  <div id="public" class="clear_fix">
    <div class="fl_l wide_column">
      <div class="page_info">
        <div class="clear module">
          <a href="/orgs/delivery/menu?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)" class="module_header">
            <div class="header_top clear_fix">
              <span class="fl_r right_link">ред.</span>
              Меню доставки
            </div>
          </a>
        <?php if(sizeof($categories)): ?>
          <?php foreach ($categories as $data): ?>
          <div class="p_header_bottom"><?php echo $data['model']->category->name ?></div>
          <div class="clear_fix">
            <?php $this->renderPartial('_menuelementlist', array('elements' => $data['items'])) ?>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="no_results">
            Меню доставки не оформлено. <a href="/orgs/delivery/menu?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)">Оформить</a>
          </div>
        <?php endif; ?>
        </div>
        <div class="clear module">
          <a href="/orgs/delivery/goods?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)" class="module_header">
            <div class="header_top clear_fix">
              <span class="fl_r right_link">ред.</span>
              Товары доставки
            </div>
            <div class="p_header_bottom"><?php echo Yii::t('app', '{n} товар|{n} товара|{n} товаров', $goodsNum) ?></div>
          </a>
          <div class="clear_fix">
          <?php if($goods): ?>
            <?php $this->renderPartial('_goodlist', array('goods' => $goods)) ?>
          <?php else: ?>
            <div class="no_results">
              Товары не добавлены в меню. <a href="/orgs/delivery/goods?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)">Добавить</a>
            </div>
          <?php endif; ?>
          </div>
        </div>
        <div class="clear module">
          <a href="/orgs/delivery/orders?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)" class="module_header">
            <div class="header_top clear_fix">
              <span class="fl_r right_link">ред.</span>
              Заказы
            </div>
            <div class="p_header_bottom"><?php echo Yii::t('app', '{n} заказ|{n} заказа|{n} заказов', $ordersNum) ?></div>
          </a>
          <div>
          <?php if($orders): ?>
            <?php $this->renderPartial('_orderlistshort', array('orders' => $orders)) ?>
          <?php else: ?>
            <div class="no_results">
              Здесь будут отображаться последние 10 заказов
            </div>
          <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <div class="fl_r narrow_column">
      <div id="page_avatar"<?php if (!$org->photo && (!$settings || ($settings && !$settings->logo))): ?> class="no_page_avatar"<?php endif; ?>>
        <?php if ($settings && $settings->logo): echo ActiveHtml::showUploadImage($settings->logo, 'a') ?>
        <?php elseif ($org->photo): echo ActiveHtml::showUploadImage($org->photo, 'a') ?>
        <?php endif; ?>
      </div>
      <div id="page_actions" class="page_actions">
        <a href="/org<?php echo $org->org_id ?>">Вернуться назад</a>
        <a onclick="Delivery.showSettingsBox(<?php echo $org->org_id ?>)">Настройки доставки</a>
      </div>
    </div>
  </div>
<?php
$this->pageJS = <<<HTML

HTML;
