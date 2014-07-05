<?php
$page = ($this->offset + $this->delta) / $this->delta;
$pages = ceil($this->offsets / $this->delta);
$minpage = ($page > 3 && $pages > 5) ? $page - 2 : 1;
$maxpage = ($page < $pages - 2) ? $page + 2 : $pages;
$prevoffset = ($page > 1) ? $this->offset - $this->delta : 0;
$nextoffset = ($page < $maxpage) ? $this->offset + $this->delta : $this->offsets - $this->delta;

if (!$this->url)
  $this->url = $_SERVER['REQUEST_URI'];

$delim = (stristr($this->url, '?')) ? '&' : '?';
?>
<div class="fl_r pg_pages">
<?php if ($pages > 1): ?>
  <?php if (!$this->nopages): ?>
    <? if ($page > 3 && $pages > 5) :?>
    <? //$this->url['offset'] = $prevoffset; ?>
      <? echo ActiveHtml::link('<div class="pg_in">&laquo;</div>', $this->url . $delim .'offset=0', array('class' => 'pg_lnk fl_l')); ?>
    <? endif; ?>
    <? for ($i=$minpage; $i<=$maxpage; $i++): ?>
    <? $offset = ($i * $this->delta) - $this->delta; ?>
      <? echo  ActiveHtml::link('<div class="pg_in">'. $i .'</div>', $this->url . $delim .'offset='. $offset, array('class' => ($page == $i) ? 'pg_lnk_sel left' : 'pg_lnk fl_l')); ?>
    <? endfor; ?>
    <? if ($pages > $maxpage): ?>
      <? echo ActiveHtml::link('<div class="pg_in">&raquo;</div>', $this->url . $delim .'offset='. (($pages * $this->delta) - $this->delta), array('class' => 'pg_lnk fl_l')); ?>
    <? endif; ?>
  </ul>
  <?php endif; ?>
<?php endif; ?>
</div>