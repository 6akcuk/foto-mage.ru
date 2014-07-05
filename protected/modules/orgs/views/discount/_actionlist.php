<?php /** @var $action DiscountAction */ ?>
<?php foreach ($actions as $action): ?>
  <div id="discount_action<?php echo $action->action_id ?>" class="event_row clear_fix">
    <div class="fl_l event_row_photo">
      <?php echo ActiveHtml::showUploadImage($action->banner, 'b') ?>
    </div>
    <div class="event_row_info fl_l">
      <div class="event_row_title"><?php if ($action->type == DiscountAction::TYPE_ACTION): ?>Акция <?php endif; ?><?php echo $action->name ?></div>
      <?php if ($action->start_time): ?>
        <?php $now = new DateTime('now') ?>
        <?php $cur = new DateTime($action->start_time) ?>
        <div class="event_row_labeled">
          Акция <?php if ($cur->diff($now)->invert > 0) echo "стартует"; else echo "стартовала" ?> <?php echo ActiveHtml::date($action->start_time, false) ?>
        </div>
      <?php endif; ?>
      <?php if ($action->end_time): ?>
        <div class="event_row_labeled">
          Акция заканчивается <?php echo ActiveHtml::date($action->end_time, false) ?>
        </div>
      <?php else: ?>
        <div class="event_row_labeled">
          <?php if ($action->type == DiscountAction::TYPE_ACTION) echo "Акция"; else echo "Карта" ?> бессрочна
        </div>
      <?php endif; ?>
      <?php if ($action->type == DiscountAction::TYPE_ACTION): ?>
      <div class="event_row_labeled">
        Лимит кодов: <?php echo ($action->pc_limits) ? $action->pc_limits : 'не установлен' ?>
      </div>
      <?php endif; ?>
      <div class="button_blue event_row_labeled">
        <button onclick="return nav.go('/orgs/discount/codes?id=<?php echo $action->action_id ?>', this)">
        <?php if ($action->type == DiscountAction::TYPE_ACTION): ?>
          Просмотреть все коды
        <?php elseif ($action->type == DiscountAction::TYPE_DISCOUNT_CARD): ?>
          Просмотреть владельца карты
        <?php endif; ?>
        </button>
      </div>
    </div>
    <div class="event_row_actions fl_r">
      <?php if ($action->type == DiscountAction::TYPE_ACTION): ?>
      <div>
        <div class="button_blue">
          <button onclick="Discount.editAction(<?php echo $action->action_id ?>)">Редактировать</button>
        </div>
      </div>
      <?php endif; ?>
      <div>
        <div class="button_blue">
          <button onclick="Discount.<?php if ($action->type == DiscountAction::TYPE_ACTION): ?>deleteAction<?php elseif ($action->type == DiscountAction::TYPE_DISCOUNT_CARD): ?>deleteCard<?php endif; ?>(<?php echo $action->action_id ?>)">Удалить</button>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>