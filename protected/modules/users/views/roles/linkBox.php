<?php
/** @var RbacItem $operation
 */

$this->pageTitle = 'Управление связями роли';

$ops = array(); $hidelist = array(); $roclist = array();
foreach ($operations as $operation) {
  $ops[$operation->name] = array('name' => $operation->name, 'descr' => $operation->description);
}
?>
<div class="roclist_search_cont">
  <?php echo ActiveHtml::textField('roclist_op_name', '', array('class' => 'text', 'placeholder' => 'Начните вводить код операции')) ?>
</div>
<div id="roclist_cont">
  <div id="roclist_right_col" class="roclist_col">
    <div id="roclist_right_inner">
      <div class="summary_wrap">
        <div class="summary">Связанные с ролью операции</div>
      </div>
      <div id="roclist_info" class="roclist_info noselect"<?php if(sizeof($childs)): ?> style="display: none"<?php endif; ?>>
        Вы можете выбрать операции в списке слева.
      </div>
      <div id="roclist_sel_list" class="roclist_list_items">
      <?php foreach ($childs as $child): ?>
      <?php $roclist[] = '"'. $child->child .'": 1'; ?>
      <?php $hidelist[$child->child] = true; ?>
        <div id="roclist_sel_<?php echo $child->child ?>">
          <table class="roclist_cell" cellspacing="0" cellpadding="0" onmousedown="Users.roclistSelect('<?php echo $child->child ?>', this, event)">
            <tr>
              <td>
                <div class="roclist_item_name"><?php echo $child->child ?></div>
                <div class="roclist_item_description"><?php echo $ops[$child->child]['descr'] ?></div>
              </td>
              <td class="roclist_item_act">
                <div class="roclist_item_action"></div>
              </td>
            </tr>
          </table>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div id="roclist_scroll_wrap">
    <div class="roclist_col roclist_left_col">
      <div class="summary_wrap">
        <div class="summary">Все операции</div>
      </div>
      <div id="roclist_all_list" class="roclist_list_items">
      <?php foreach ($operations as $operation): ?>
        <div id="roclist_<?php echo $operation->name ?>"<?php if(isset($hidelist[$operation->name])): ?> style="display: none"<?php endif; ?>>
          <table class="roclist_cell" cellspacing="0" cellpadding="0" onmousedown="Users.roclistSelect('<?php echo $operation->name ?>', this, event)">
            <tr>
              <td>
                <div class="roclist_item_name"><?php echo $operation->name ?></div>
                <div class="roclist_item_description"><?php echo $operation->description ?></div>
              </td>
              <td class="roclist_item_act">
                <div class="roclist_item_action"></div>
              </td>
            </tr>
          </table>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php
$roclist = implode(",", $roclist);

$this->pageJS = <<<HTML
Users.initConnect();
cur.roclistItems = {
  $roclist
};
HTML;
