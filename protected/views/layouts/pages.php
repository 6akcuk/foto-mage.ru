<?php
$page = ($offset + $delta) / $delta;
$pages = ceil($offsets / $delta);
$minpage = ($page > 3 && $pages > 5) ? $page - 2 : 1;
$maxpage = ($page < $pages - 2) ? $page + 2 : $pages;
$prevoffset = ($page > 1) ? $offset - $delta : 0;
$nextoffset = ($page < $maxpage) ? $offset + $delta : $offsets - $delta;

if (!isset($url))
  $url = $_SERVER['REQUEST_URI'];

$delim = (stristr($url, '?')) ? '&' : '?';
?>
<div class="fl_r pg_pages">
  <?php if ($pages > 1): ?>
    <? if ($page > 3 && $pages > 5) :?>
      <? //$url['offset'] = $prevoffset; ?>
      <? echo ActiveHtml::link('<div class="pg_in">&laquo;</div>', $url . $delim .'offset=0', array('class' => 'pg_lnk fl_l')); ?>
    <? endif; ?>
    <? for ($i=$minpage; $i<=$maxpage; $i++): ?>
      <? $offset = ($i * $delta) - $delta; ?>
      <? echo  ActiveHtml::link('<div class="pg_in">'. $i .'</div>', $url . $delim .'offset='. $offset, array('class' => ($page == $i) ? 'pg_lnk_sel fl_l' : 'pg_lnk fl_l')); ?>
    <? endfor; ?>
    <? if ($pages > $maxpage): ?>
      <? echo ActiveHtml::link('<div class="pg_in">&raquo;</div>', $url . $delim .'offset='. (($pages * $delta) - $delta), array('class' => 'pg_lnk fl_l')); ?>
    <? endif; ?>
    </ul>
  <?php endif; ?>
</div>