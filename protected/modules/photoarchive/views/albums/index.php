<?php
$this->pageTitle=Yii::app()->name . ' - Альбомы фотоархива';

Yii::app()->getClientScript()->registerCssFile('/css/albums.css');
Yii::app()->getClientScript()->registerScriptFile('/js/albums.js');

$url_suffix = array();
if (sizeof($c) > 0) {
  foreach ($c as $c_key => $c_value) {
    $url_suffix[] = "c[". $c_key ."]=". urlencode($c_value);
  }
}
?>
<div class="albums_search_wrap clear_fix">
  <div class="fl_r">
    <div class="button_blue">
      <button onclick="Album.add()">Создать альбом</button>
    </div>
  </div>
</div>

<div class="albums_container">
  <?php if ($albums): ?>
    <?php $this->renderPartial('_albumlist', array('albums' => $albums)) ?>
  <?php else: ?>
    <div class="empty">
      Здесь будут видны ваши альбомы.<br/> <a onclick="Album.add()">Создайте свой первый альбом</a>
    </div>
  <?php endif; ?>
</div>

<?php
$this->pageJS = <<<HTML
Album.initList();
HTML;

