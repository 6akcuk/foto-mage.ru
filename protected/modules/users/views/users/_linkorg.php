<a id="link<?php echo $link->user_id ?>_<?php echo $link->org_id ?>" onclick="Users.deleteLinkOrg(<?php echo $link->user_id ?>, <?php echo $link->org_id ?>)" class="link_org_user clear_fix">
  <div class="link_org_user_help fl_r">Удалить</div>
  <div class="link_org_user_name"><?php echo $link->org->name ?></div>
  <div class="link_org_user_description"><?php echo $link->org->city->name ?></div>
</a>