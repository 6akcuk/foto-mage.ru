<?php
$this->pageTitle = Yii::app()->name . ' - Новости';

Yii::app()->getClientScript()->registerCssFile('/css/news.css');
Yii::app()->getClientScript()->registerScriptFile('/js/news.js');

$delta = Yii::app()->getModule('news')->newsPostsPerPage;
?>
<div class="news_search_wrap clear_fix">
  <div rel="filters" class="fl_l">
    <?php echo ActiveHtml::textField('c[title]', (isset($c['title'])) ? $c['title'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить заголовок новости')) ?>
  </div>
  <div class="fl_r">
    <div class="button_blue">
      <button onclick="News.add()">Добавить новость</button>
    </div>
  </div>
</div>
<div class="summary_wrap">
  <?php $this->renderPartial('//layouts/pages', array(
    'url' => '/news/default/index',
    'offset' => $offset,
    'offsets' => $offsets,
    'delta' => $delta,
  )) ?>
  <div class="summary"><?php echo Yii::t('app', '{n} новость|{n} новости|{n} новостей', $offsets) ?></div>
</div>
<div class="news_wrap">
<?php if ($posts): ?>
  <?php echo $this->renderPartial('_postlist', array('posts' => $posts, 'offset' => $offset)) ?>
<?php else: ?>
  <div id="no_results">Новости еще не добавлены</div>
<?php endif; ?>
</div>
<?php
$this->pageJS = <<<HTML
News.init();
HTML;
