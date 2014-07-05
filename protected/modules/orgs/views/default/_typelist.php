<?php /** @var $type OrganizationType */ ?>
<?php foreach ($types as $row_idx => $type): ?>
<tr<?php if ($row_idx % 2) echo ' class="even"' ?>>
  <td>
    <span class="org_name"><?php echo $type->type_name ?></span>
    <br class="clear" />
    <a href="/orgs/orgs/index?c[org_type_id]=<?php echo $type->type_id ?>" onclick="return nav.go(this, event)">
      <?php echo Yii::t('app', '{n} организация|{n} организации|{n} организаций', $type->org_num) ?>
    </a>
  </td>
  <td class="org_actions">
    <div class="button_blue" onclick="OrgType.edit('<?php echo $type->type_id ?>')">
      <button>Редактировать</button>
    </div>
    <br class="clear" />
    <div class="button_blue" onclick="OrgType.delete('<?php echo $type->type_id ?>')">
      <button>Удалить</button>
    </div>
  </td>
</tr>
<?php endforeach; ?>