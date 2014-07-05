<?php
$app=Yii::app();
$request = $app->getRequest();
/** @var $cookies CCookieCollection */
$cookies = $request->getCookies();

Yii::app()->getClientScript()->registerScriptFile('/js/lang_'. Yii::app()->language .'.js');
Yii::app()->getClientScript()->registerScriptFile('/js/jquery-1.10.1.min.js');
Yii::app()->getClientScript()->registerScriptFile('/js/common.js', CClientScript::POS_HEAD, 'after jquery-');
Yii::app()->getClientScript()->registerScriptFile('/js/ui_controls.js');
Yii::app()->getClientScript()->registerScriptFile('/js/main.js');

Yii::app()->getClientScript()->registerCssFile('/css/main.css');
Yii::app()->getClientScript()->registerCssFile('/css/common.css');
Yii::app()->getClientScript()->registerCssFile('/css/ui_controls/ui_controls.css');
Yii::app()->getClientScript()->registerCssFile('/css/intro.css');

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <script type="text/javascript">
      var A = {
        al: parseInt('3') || 4,
        uuid: 0,
        user_id: 0,
        lang: '<?php echo Yii::app()->language ?>',
        navPrefix: '/',
        host: location.host,
        width: 791
      };

      var hshtest = (location.toString().match(/#(.*)/) || {})[1] || '';
      if (hshtest.length && hshtest.substr(0, 1) == A.navPrefix) {
        location.replace(location.protocol + '//' + location.host + '/' + hshtest.replace(/^(\/|!)/, ''));
      }
    </script>
      <meta charset="utf-8">
      <title><?php echo CHtml::encode($this->pageTitle); ?></title>
  </head>
  <body class="font_default" style="overflow: auto">
  <div class="scroll_fix_wrap" id="page_wrap">
    <div>
      <div class="scroll_fix">
        <div id="page_layout">
          <div id="intro" class="clear_fix">
            <div class="fl_l">
              <?php
              if (Yii::app()->user->getIsGuest()) {
                $this->widget('application.modules.users.components.LoginWidget');
              }
              ?>
            </div>
            <div class="fl_l or">
              или
            </div>
            <div class="fl_l socialnetwork">
              <div class="button_blue button_wide button_big">
                <button onclick="window.open(
                  'https://oauth.vk.com/authorize?client_id=4193898&scope=friends,photos&redirect_uri=http://foto-mage.ru/vklogin&response_type=code&v=5.16',
                  '',
                  'width=607,height=417,resizable=no,scrollbars=no,status=no,location=no'
                )">Войти через ВКонтакте</button>
              </div>
            </div>
            <div class="clear"></div>
            <div class="content">
              <?php echo $content ?>
            </div>
            <div class="register">
              <a href="/register">Зарегистрироваться</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>