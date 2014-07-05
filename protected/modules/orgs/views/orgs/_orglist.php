<?php /** @var $org Organization */ ?>
<?php foreach ($orgs as $row_idx => $org): ?>
<tr<?php if ($row_idx % 2) echo ' class="even"' ?>>
  <td class="org_thumb">
    <a href="/<?php echo ($org->url) ? $org->url : 'org'. $org->org_id ?>" onclick="return nav.go(this, event)">
      <img src="<?php echo ActiveHtml::getPhotoUrl($org->photo, 'c') ?>" />
    </a>
  </td>
  <td>
    <a class="org_name" href="/<?php echo ($org->url) ? $org->url : 'org'. $org->org_id ?>" onclick="return nav.go(this, event)">
      <?php echo $org->name ?>
    </a>
    <br class="clear" />
    <div class="org_label">
    <?php
      $types = array();
      foreach ($org->types as $type) {
        $types[] = $type->type->type_name;
      }

      echo implode(", ", $types);
    ?>
    </div>
  </td>
  <td>
    <b><?php echo $org->city->name ?></b>
  </td>
  <td class="org_address">
    <?php echo $org->address ?>
  </td>
  <td class="org_actions">
    <div class="button_blue" onclick="Org.edit('<?php echo $org->org_id ?>')">
      <button>Редактировать</button>
    </div>
    <br class="clear" />
    <div class="button_blue" onclick="Org.delete('<?php echo $org->org_id ?>')">
      <button>Удалить</button>
    </div>
  </td>
</tr>
<?php endforeach; ?>