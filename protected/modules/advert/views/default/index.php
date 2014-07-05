<?php
$this->pageTitle = Yii::app()->name . ' - Объявления';

Yii::app()->getClientScript()->registerCssFile('/css/advert.css');
Yii::app()->getClientScript()->registerScriptFile('/js/advert.js');

$delta = Yii::app()->getModule('advert')->advertPostsPerPage;
?>
  <div class="advert_search_wrap clear_fix">
    <div rel="filters" class="fl_l">
      <?php echo ActiveHtml::textField('c[title]', (isset($c['title'])) ? $c['title'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить заголовок объявления')) ?>
    </div>
    <div class="fl_r">
      <div class="button_blue">
        <button onclick="Advert.add()">Добавить объявление</button>
      </div>
    </div>
  </div>
  <div class="summary_wrap">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/advert/default/index',
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="summary"><?php echo Yii::t('app', '{n} объявление|{n} объявления|{n} объявлений', $offsets) ?></div>
  </div>

  <div>
    <table class="advert_table">
      <tr>
        <td class="results">
        <?php if ($posts): ?>
          <?php echo $this->renderPartial('_postlist', array('posts' => $posts, 'offset' => $offset)) ?>
        <?php else: ?>
          <div id="no_results">Объявления еще не добавлены</div>
        <?php endif; ?>
        </td>
        <td class="filters">
          <div id="search_filters">

          </div>
        </td>
      </tr>
    </table>
  </div>
<?php
$this->pageJS = <<<HTML
Advert.init();
HTML;
