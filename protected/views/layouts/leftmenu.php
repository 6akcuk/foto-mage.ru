<ul>
  <li class="clearfix">
    <?php echo ActiveHtml::link('Моя страница', '/id'. Yii::app()->user->getId(), array('class' => 'left')) ?>
    <?php echo ActiveHtml::link('ред.', '/edit', array('class' => 'right')) ?>
  </li>
  <li id="friends_link" class="clearfix">
    <?php echo ActiveHtml::link('Мои друзья', '/friends', array('class' => 'left')) ?>
    <?php
    if ($this->pageCounters['friends'])
      echo ActiveHtml::link('+'. $this->pageCounters['friends'], '/friends?section=requests', array('class' => 'right lm-counter'))
    ?>
  </li>
  <li id="pm_link" class="clearfix">
    <?php echo ActiveHtml::link('Мои сообщения', '/mail', array('class' => 'left')) ?>
    <?php
    if ($this->pageCounters['pm'])
      echo ActiveHtml::link('+'. $this->pageCounters['pm'], '/mail', array('class' => 'right lm-counter'))
    ?>
  </li>
  <li id="orders_link" class="clearfix">
    <?php echo ActiveHtml::link('Мои покупки', '/orders', array('class' => 'left')) ?>
    <?php
    if ($this->pageCounters['orders'])
      echo ActiveHtml::link('+'. $this->pageCounters['orders'], '/orders', array('class' => 'right lm-counter'))
    ?>
  </li>
  <li id="news_link" class="clearfix">
    <?php echo ActiveHtml::link('Мои новости', '/feed', array('class' => 'left')) ?>
    <?php
    if ($this->pageCounters['news'])
      echo ActiveHtml::link('+'. $this->pageCounters['news'], '/feed?section=notifications', array('class' => 'right lm-counter'))
    ?>
  </li>
  <li class="clearfix">
    <?php echo ActiveHtml::link('Мои настройки', '/settings', array('class' => 'left')) ?>
  </li>
</ul>
<?php if (in_array(Yii::app()->user->model->role->itemname, array('Администратор', 'Модератор'))): ?>
<ul>
<?php endif; ?>
<?php if(Yii::app()->user->checkAccess('users.users.index')): ?>
  <li id="users_link" class="clearfix">
    <?php echo ActiveHtml::link('Пользователи', '/users', array('class' => 'left')) ?>
    <?php
    if ($this->pageCounters['users'])
      echo ActiveHtml::link($this->pageCounters['users'], '/users', array('class' => 'right lm-counter'))
    ?>
  </li>
<?php endif; ?>
<?php if (Yii::app()->user->checkAccess('discuss.forum.manage')): ?>
  <li class="clearfix">
    <?php echo ActiveHtml::link('Обсуждения', '/discuss?act=manage', array('class' => 'left')) ?>
  </li>
<?php endif; ?>
<?php if (Yii::app()->user->checkAccess('purchases.purchases.sitelist')): ?>
  <li class="clearfix">
    <?php echo ActiveHtml::link('Список сайтов', '/purchases/sitelist', array('class' => 'left')) ?>
  </li>
<?php endif; ?>
<?php if (Yii::app()->user->checkAccess('purchases.purchases.acquire')): ?>
  <li id="ac_purchase_link" class="clearfix">
    <?php echo ActiveHtml::link('Одобрить закупки', '/purchases/acquire', array('class' => 'left')) ?>
    <?php
    if ($this->pageCounters['purchases'])
      echo ActiveHtml::link('+'. $this->pageCounters['purchases'], '/purchases/acquire', array('class' => 'right lm-counter'))
    ?>
  </li>
<?php endif; ?>
<?php if (in_array(Yii::app()->user->model->role->itemname, array('Администратор', 'Модератор'))): ?>
</ul>
<?php endif; ?>
