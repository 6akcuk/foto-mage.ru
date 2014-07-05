<?php
Yii::app()->getClientScript()->registerScriptFile('/js/jquery-1.8.2.min.js');
Yii::app()->getClientScript()->registerScriptFile('/js/main.js');
Yii::app()->getClientScript()->registerCssFile('/css/main.css');
Yii::app()->getClientScript()->registerCssFile('/css/elements.css');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <script type="text/javascript">
      var A = {
        uuid: 0,
        user_id: <?php echo (!Yii::app()->user->getIsGuest()) ? Yii::app()->user->getId() : 0 ?>,
        navPrefix: '/',
        host: location.host
      };

      var hshtest = (location.toString().match(/#(.*)/) || {})[1] || '';
      if (hshtest.length && hshtest.substr(0, 1) == A.navPrefix) {
        location.replace(location.protocol + '//' + location.host + '/' + hshtest.replace(/^(\/|!)/, ''));
      }
    </script>
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <meta charset="utf-8">
  </head>
  <body>
    <div id="utils"></div>
    <div id="global_progress_bg"></div>
    <div id="global_progress" class="fixed">
        <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/2.gif" alt="" />
    </div>

    <div id="box_popup" class="box_popup box_popup_dark fixed">
      <div class="text"></div>
      <div class="back"></div>
    </div>

    <div class="wrapper">
      <div id="top">
          <div class="wrap">
              <div id="top_intro" class="clearfix">
                  <div class="left">
                      <h1><a href="/">SPMix</a></h1>
                  </div>
                  <div class="right">
                      <?php
                      if (Yii::app()->user->getIsGuest()) {
                          $this->widget('application.modules.users.components.LoginWidget');
                      }
                      ?>
                  </div>
              </div>
          </div>
      </div>
      <div id="content" class="edge wrap" style="width: 1000px">
        <?php echo $content ?>
      </div>
      <div id="footer">
        &copy; <?php echo date("Y") ?>
      </div>
    </div>
  </body>
</html>