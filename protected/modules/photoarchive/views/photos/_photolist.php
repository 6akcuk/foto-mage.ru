<?php /** @var PhotoarchivePhoto $photo */ ?>
<?php foreach ($photos as $photo_idx => $photo): ?>
  <div id="photo<?php echo $photo->photo_id ?>" class="photo_row">
    <a onclick="Photoview.show('photoarchive<?php echo $photo->album_id ?>', <?php echo $photo_idx ?>)">
      <?php echo ActiveHtml::showUploadImage($photo->photo, 'e', array('class' => 'photo_row_img')) ?>
      <div class="photo_delete" onclick="Album.deletePhoto(<?php echo $photo->photo_id ?>, event)">
        <div class="photo_delete_back"></div>
        <div class="photo_delete_cont"></div>
      </div>
    </a>
  </div>
<?php endforeach; ?>