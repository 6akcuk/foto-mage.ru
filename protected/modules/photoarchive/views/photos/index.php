<?php
$this->pageTitle=Yii::app()->name . ' - Фотографии из фотоархива';

Yii::app()->getClientScript()->registerCssFile('/css/albums.css');
Yii::app()->getClientScript()->registerScriptFile('/js/albums.js');

Yii::app()->getClientScript()->registerScriptFile('/js/swfupload.js');
Yii::app()->getClientScript()->registerScriptFile('/js/swfupload.queue.js');

Yii::app()->getClientScript()->registerCssFile('/css/photoview.css');
Yii::app()->getClientScript()->registerScriptFile('/js/photoview.js');

$url_suffix = array();
if (sizeof($c) > 0) {
  foreach ($c as $c_key => $c_value) {
    $url_suffix[] = "c[". $c_key ."]=". urlencode($c_value);
  }
}

$photosJs = array();
/** @var PhotoarchivePhoto $photo */
foreach ($photos as $photo) {
  $photosJs[] = $photo->photo;
}

$photosJs = implode(",", $photosJs);
?>
<div class="tabs">
  <?php echo ActiveHtml::link('Фотоархив', '/photoarchive') ?>
  <?php echo ActiveHtml::link($album->name, '/photoarchive'. $album->album_id, array('class' => 'selected')) ?>
</div>
<div class="clear_fix">
  <div id="photos_add_bar" rel="scrollfix" data-scroll="top">
    <div class="photos_add_bar_shadow"></div>
    <div class="swfupload_wrap">
      <div id="swfupload_button"></div>
      <div class="upload_field">
        Добавить фотографии
      </div>
    </div>
    <div id="photos_add_bar_progress">
      <div id="photos_add_p_line">
        <div id="photos_add_p_inner"></div>
      </div>
      <div id="photos_add_p_text"></div>
    </div>
  </div>
</div>

<div class="photos_container">
  <?php if ($photos): ?>
    <?php $this->renderPartial('_photolist', array('photos' => $photos)) ?>
  <?php else: ?>
    <div class="empty">
      Здесь будут видны ваши фотографии.
    </div>
  <?php endif; ?>
</div>

<?php
$this->pageJS = <<<HTML
Album.initUploader({
  id: $album->album_id,
  uploadAction: 'http://cs1.foto-mage.ru/upload.php'
});
Album.initPhotoList();

Photoview.list('photoarchive$album->album_id', {count: $offsets, items: [$photosJs]});
HTML;

