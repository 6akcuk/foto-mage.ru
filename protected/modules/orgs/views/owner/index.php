<?php
$this->pageTitle = Yii::app()->name . ' - '. $org->name;

Yii::app()->getClientScript()->registerCssFile('/css/page.css');
Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/orgs.js');

?>
<div id="header">Страница</div>
<div id="public" class="clear_fix">
  <div class="fl_l wide_column">
    <h4 class="simple page_top">
      <div class="top_header page_name"><?php echo $org->name ?></div>
    </h4>
    <div class="page_info">
      <div class="clear_fix">
        <div class="label fl_l">Описание:</div>
        <div class="labeled fl_l"><?php echo $org->shortstory ?></div>
      </div>
      <div class="clear_fix">
        <div class="label fl_l">Город:</div>
        <div class="labeled fl_l"><?php echo $org->city->name ?></div>
      </div>
      <div class="clear_fix">
        <div class="label fl_l">Адрес:</div>
        <div class="labeled fl_l"><?php echo $org->address ?></div>
      </div>
      <div class="clear_fix">
        <div class="label fl_l">Контактный телефон:</div>
        <div class="labeled fl_l"><?php echo ActiveHtml::phone($org->phone) ?></div>
      </div>
      <div class="clear_fix">
        <div class="label fl_l">Время работы:</div>
        <div class="labeled fl_l"><?php echo $org->worktimes ?></div>
      </div>
    </div>
  </div>
  <div class="fl_r narrow_column">
    <div id="page_avatar"<?php if (!$org->photo): ?> class="no_page_avatar"<?php endif; ?>><?php if ($org->photo) echo ActiveHtml::showUploadImage($org->photo, 'a') ?></div>
    <div id="page_actions" class="page_actions">
      <a onclick="Org.edit(<?php echo $org->org_id ?>)">Редактировать данные</a>
      <a href="/events<?php echo $org->org_id ?>" onclick="return nav.go(this, event)">Просмотреть события</a>
      <a onclick="Org.showModules(<?php echo $org->org_id ?>)">Дополнительные модули</a>
    <?php if ($org->modules && $org->modules->enable_delivery): ?>
      <a href="/orgs/delivery/index?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)">Управление доставкой</a>
    <?php endif; ?>
    <?php if ($org->modules && $org->modules->enable_discount): ?>
      <a href="/orgs/discount/index?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)">Управление дисконтами</a>
    <?php endif; ?>
    <?php if ($org->modules && $org->modules->enable_market): ?>
      <a href="/orgs/market/index?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)">Управление товарами</a>
    <?php endif; ?>
    </div>

    <div class="clear module<?php if (!$org->rooms): ?> empty<?php endif; ?>" id="org_rooms">
      <a onclick="Org.editRooms(<?php echo $org->org_id ?>)" class="module_header">
        <div class="header_top clear_fix">
          <?php if ($org->rooms): ?><span class="fl_r right_link">ред.</span><?php endif; ?>
          Помещения
        </div>
        <?php if ($org->rooms): ?>
        <div class="p_header_bottom"><?php echo Yii::t('app', '{n} помещение|{n} помещения|{n} помещений', sizeof($org->rooms)) ?></div>
        <?php endif; ?>
      </a>
      <div class="module_body clear_fix page_list_module">
      <?php if ($org->rooms): ?>
        <?php foreach($org->rooms as $room): ?>
        <div class="line_cell clear_fix">
          <div class="fl_l thumb"><img src="/images/camera_c.gif" /></div>
          <div class="fl_l info"><?php echo $room->name ?></div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        Помещения организации для более точной адресации событий
        <div class="add_link">
          <a onclick="Org.addRoom(<?php echo $org->org_id ?>)">Добавить помещения</a>
        </div>
      <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php
$this->pageJS = <<<HTML

HTML;
