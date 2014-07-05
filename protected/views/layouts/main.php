<?php
Yii::app()->getClientScript()->registerScriptFile('/js/lang_'. Yii::app()->language .'.js');
Yii::app()->getClientScript()->registerScriptFile('/js/jquery-1.10.1.min.js');
Yii::app()->getClientScript()->registerScriptFile('/js/common.js', CClientScript::POS_HEAD, 'after jquery-');
Yii::app()->getClientScript()->registerScriptFile('/js/ui_controls.js');
Yii::app()->getClientScript()->registerScriptFile('/js/main.js');

Yii::app()->getClientScript()->registerCssFile('/css/main.css');
Yii::app()->getClientScript()->registerCssFile('/css/common.css');
Yii::app()->getClientScript()->registerCssFile('/css/ui_controls/ui_controls.css');

$app=Yii::app();
$request = $app->getRequest();
/** @var $cookies CCookieCollection */
$cookies = $request->getCookies();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <script type="text/javascript">
    var A = {
      al: parseInt('3') || 4,
      uuid: 0,
      user_id: <?php echo (Yii::app()->user->getId()) ?: 0 ?>,
      lang: '<?php echo Yii::app()->language ?>',
      navPrefix: '/',
      host: location.host,
      width: 791
    };
    // Cross-domain fix
    document.domain = A.host.match(/[a-zA-Z\-]+\.[a-zA-Z]+\.?$/)[0];

    var hshtest = (location.toString().match(/#(.*)/) || {})[1] || '';
    if (hshtest.length && hshtest.substr(0, 1) == A.navPrefix) {
      location.replace(location.protocol + '//' + location.host + '/' + hshtest.replace(/^(\/|!)/, ''));
    }
  </script>
  <title><?php echo CHtml::encode($this->pageTitle); ?></title>
  <meta charset="utf-8">
  <script type="text/javascript" src="http://code.jquery.com/ui/1.10.1/jquery-ui.js"></script>
</head>
<body onresize="onBodyResize()" class="font_default" style="overflow: auto">
<div id="system_msg" class="fixed"></div>
<div id="utils"></div>

<div id="layer_bg" class="fixed"></div><div id="layer_wrap" class="scroll_fix_wrap fixed"><div id="layer"></div></div>
<div id="box_layer_bg" class="fixed"></div><div id="box_layer_wrap" class="scroll_fix_wrap fixed"><div id="box_layer"><div id="box_loader"><div class="loader"></div><div class="back"></div></div></div></div>

<div id="stl_left"></div>

<script type="text/javascript">domStarted()</script>

<div class="scroll_fix_wrap" id="page_wrap">
  <div>
    <div class="scroll_fix">
      <div id="page_header_layout">
        <div id="page_header" class="clear_fix">
          <a class="fl_l text_logo" href="/">ЛОГОТИП</a>
          <div class="fl_l top_search">
            <input type="text" name="gsearch" id="gsearch" value="" />
            <a onclick="return nav.go('/search?c[section]=people&c[name]='+ $('#gsearch').val(), event)">Поиск</a>
          </div>
          <?php if (!Yii::app()->user->isGuest): ?>
          <a href="/id<?php echo Yii::app()->user->getId() ?>" class="fl_l top_user_name" onclick="return nav.go(this, event)"><?php echo Yii::app()->user->model->getDisplayName() ?></a>
          <a class="fl_l top_exit_link" href="/logout">Выйти</a>
          <?php endif; ?>
        </div>
      </div>
      <div id="page_layout" class="clear_fix">
        <div id="page_menu" class="fl_l">
          <div class="menu_link clear_fix">
            <a href="/id<?php echo Yii::app()->user->getId() ?>" onclick="return nav.go(this, event)">Моя Страница</a>
            <a href="/edit" onclick="return nav.go(this, event)" class="fl_r menu_counter">ред.</span>
          </div>
        <?php if (Yii::app()->user->checkAccess('users.friends.index')): ?>
          <?php if ($this->pageCounters['friends']): ?>
            <a id="friends_link" href="/friends?section=requests" onclick="return nav.go(this, event)" class="menu_link clear_fix">
              Мои Друзья
              <span class="fl_r menu_counter">+<?php echo $this->pageCounters['friends'] ?></span>
            </a>
          <?php else: ?>
            <?php echo ActiveHtml::link('Мои Друзья', '/friends', array('class' => 'menu_link')) ?>
          <?php endif; ?>
        <?php endif; ?>
        <?php if (Yii::app()->user->checkAccess('mail.default.inbox')): ?>
          <?php if ($this->pageCounters['pm']): ?>
            <a id="pm_link" href="/mail" onclick="return nav.go(this, event)" class="menu_link clear_fix">
              Мои Сообщения
              <span class="fl_r menu_counter">+<?php echo $this->pageCounters['pm'] ?></span>
            </a>
          <?php else: ?>
            <?php echo ActiveHtml::link('Мои Сообщения', '/mail', array('class' => 'menu_link')) ?>
          <?php endif; ?>
        <?php endif; ?>
        <?php if (Yii::app()->user->checkAccess('photoarchive.albums.index')): ?>
          <?php echo ActiveHtml::link('Мой Фотоархив', '/photoarchive', array('class' => 'menu_link')) ?>
        <?php endif; ?>
        <?php if (Yii::app()->user->checkAccess('users.profiles.settings')): ?>
          <?php echo ActiveHtml::link('Мои Настройки', '/settings', array('class' => 'menu_link')) ?>
        <?php endif; ?>
        <?php if (
          Yii::app()->user->checkAccess('unify.cities.index') ||
          Yii::app()->user->checkAccess('unify.parserlog.index') ||
          Yii::app()->user->checkAccess('unify.console.index') ||
          Yii::app()->user->checkAccess('unify.support.index')
        ): ?>
          <a id="menu_system_link" class="menu_link">Система</a>
        <?php endif; ?>
        <?php if (
          Yii::app()->user->checkAccess('users.users.index') ||
          Yii::app()->user->checkAccess('users.roles.index') ||
          Yii::app()->user->checkAccess('users.roles.operations') ||
          Yii::app()->user->checkAccess('users.roles.connect') ||
          Yii::app()->user->checkAccess('users.feedback.index')
        ): ?>
          <a id="menu_users_link" class="menu_link">Пользователи</a>
        <?php endif; ?>
        </div>
        <div id="page_body" class="fl_l">
          <div id="wrap2">
            <div id="wrap1">
              <div id="content">
                <?php echo $content ?>
              </div>
            </div>
          </div>
        </div>
        <div id="footer_wrap">
          <div id="footer" class="clear">
            <div class="copy_lang">
              foto-mage.ru &copy; <?php echo date("Y") ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  domReady();
  <?php
  // Подготовим меню, в зависимости от разрешений группы пользователя
  $system_menu = array();
  $users_menu = array();
  $org_menu = array();
  $market_menu = array();
  $advert_menu = array();
  $news_menu = array();

  if (Yii::app()->user->checkAccess('unify.cities.index')) $system_menu[] = "['unify/cities/index','Список городов']";
  if (Yii::app()->user->checkAccess('unify.parserlog.index')) $system_menu[] = "['unify/parserLog/index','Логи ботов']";
  if (Yii::app()->user->checkAccess('unify.console.index')) $system_menu[] = "['unify/console/index','Управление системой']";
  if (Yii::app()->user->checkAccess('unify.support.index')) $system_menu[] = "['unify/support/index','Техническая поддержка']";

  if (Yii::app()->user->checkAccess('users.users.index')) $users_menu[] = "['users','Список пользователей']";
  if (Yii::app()->user->checkAccess('users.roles.index')) $users_menu[] = "['users/roles/index','Роли пользователей']";
  if (Yii::app()->user->checkAccess('users.roles.operations')) $users_menu[] = "['users/roles/operations','Операции']";
  if (Yii::app()->user->checkAccess('users.roles.connect')) $users_menu[] = "['users/roles/connect','Связь роли с операциями']";
  if (Yii::app()->user->checkAccess('users.feedback.index')) $users_menu[] = "['users/feedback/index','Обратная связь']";

  if (Yii::app()->user->checkAccess('orgs.orgs.index')) $org_menu[] = "['orgs/orgs/index','Список организаций']";
  if (Yii::app()->user->checkAccess('orgs.default.index')) $org_menu[] = "['orgs/default/index','Категории организаций']";
  if (Yii::app()->user->checkAccess('orgs.events.types')) $org_menu[] = "['orgs/events/types','Типы событий']";
  if (Yii::app()->user->checkAccess('orgs.delivery.categories')) $org_menu[] = "['orgs/delivery/categories','Категории доставки']";
  if (Yii::app()->user->checkAccess('orgs.orgs.import')) $org_menu[] = "['orgs/orgs/import','Импорт организаций']";

  if (Yii::app()->user->checkAccess('market.category.index')) $market_menu[] = "['market/category/index','Категории товаров']";

  if (Yii::app()->user->checkAccess('advert.default.index')) $advert_menu[] = "['advert','Список объявлений']";
  if (Yii::app()->user->checkAccess('advert.category.index')) $advert_menu[] = "['advert/category/index','Категории объявлений']";
  if (Yii::app()->user->checkAccess('advert.param.index')) $advert_menu[] = "['advert/param/index','Настраиваемые параметры']";

  if (Yii::app()->user->checkAccess('news.default.index')) $news_menu[] = "['news/default/index','Список новостей']";
  if (Yii::app()->user->checkAccess('news.ads.index')) $news_menu[] = "['news/ads/index','Рекламные баннеры']";

  ?>
  var menu_system_link = new DDMenu('#menu_system_link', [<?php echo implode(",", $system_menu) ?>], {
    click: function(href) {
      nav.go(href, undefined);
    }
  });
  var menu_users_link = new DDMenu('#menu_users_link', [<?php echo implode(",", $users_menu) ?>], {
    click: function(href) {
      nav.go(href, undefined);
    }
  });
  var menu_org_link = new DDMenu('#menu_org_link', [<?php echo implode(",", $org_menu) ?>], {
    click: function(href) {
      nav.go(href, undefined);
    }
  });
  var menu_market_link = new DDMenu('#menu_market_link', [<?php echo implode(",", $market_menu) ?>], {
    click: function(href) {
      nav.go(href, undefined);
    }
  });
  var menu_advert_link = new DDMenu('#menu_advert_link', [<?php echo implode(",", $advert_menu) ?>], {
    click: function(href) {
      nav.go(href, undefined);
    }
  });
  var menu_news_link = new DDMenu('#menu_news_link', [<?php echo implode(",", $news_menu) ?>], {
    click: function(href) {
      nav.go(href, undefined);
    }
  });
  <?php echo $this->pageJS ?>
</script>
</body>
</body>
</html>