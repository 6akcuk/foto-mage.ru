<?php /** @var PhotoarchiveAlbum $album */ ?>
<?php foreach ($albums as $album): ?>
  <div class="album_row">
    <a href="/photoarchive<?php echo $album->album_id ?>" class="img_link" onclick="return nav.go(this, event)">
      <?php if ($album->cover): ?>
        <?php echo ActiveHtml::showUploadImage($album->cover->photo, 'd') ?>
      <?php elseif (!$album->cover_id && $album->photos_num > 0): ?>
        <?php
        /** @var PhotoarchivePhoto $photo */
        $photo = PhotoarchivePhoto::model()->find('album_id = :aid', array(':aid' => $album->album_id));

        $album->cover_id = $photo->photo_id;
        $album->save(true, array('cover_id'));

        echo ActiveHtml::showUploadImage($photo->photo, 'd');
        ?>
      <?php endif; ?>
      <div class="album_info" onclick="Album.edit(<?php echo $album->album_id ?>)">
        <div class="album_info_back"></div>
        <div class="album_info_cont"></div>
      </div>
      <div class="album_delete" onclick="Album.delete(<?php echo $album->album_id ?>)">
        <div class="album_delete_back"></div>
        <div class="album_delete_cont"></div>
      </div>
      <div class="album_title">
        <div class="clear_fix">
          <div class="fl_l ge_album"><?php echo $album->name ?></div>
          <div class="camera fl_r"><?php echo $album->photos_num ?></div>
        </div>
      </div>
    </a>
  </div>
<?php endforeach; ?>