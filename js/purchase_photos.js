var PurchasePhotos = {
  fileDialogComplete: function(numFilesSelected, numFilesQueued) {
    try {
      cur.photosUploaded = 0;
      cur.photosUploadNum = numFilesSelected;
      this.startUpload();
    }
    catch (ex) {}
  },

  uploadStart: function(file) {
    $('#photos_add_bar .swfupload_wrap').css({position: 'absolute', visibility: 'hidden'});
    $('#photos_add_bar_progress').show();
    $('#photos_add_bar').css({height: 57});

    return true;
  },

  uploadProgress: function(file, bytesLoaded, bytesTotal) {
    var p = Math.floor(bytesLoaded / bytesTotal * 100);
    $('#photos_add_p_inner').css({width: p * 175 / 100});
    $('#photos_add_p_text').html('Загружено фотографий: <b>'+ cur.photosUploaded + '</b> из <b>'+ cur.photosUploadNum +'</b>');
  },

  uploadError: function(file, errorCode, message) {
    try {
      switch (errorCode) {
        case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
          ajex.show("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
          break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
          ajex.show("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
          break;
        case SWFUpload.UPLOAD_ERROR.IO_ERROR:
          ajex.show("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
          break;
        case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
          ajex.show("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
          break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
          ajex.show("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
          break;
        case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
          ajex.show("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
          break;
        case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
          // If there aren't any files left (they were all cancelled) disable the cancel button
          ajex.show("Cancelled");
          break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
          ajex.show("Stopped");
          break;
        default:
          ajex.show("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
          break;
      }
    } catch (ex) {
    }
  },

  uploadSuccess: function(file) {
    cur.photosUploaded++;
    $('#photos_add_p_text').html('Загружено фотографий: <b>'+ this.getStats().successful_uploads + '</b> из <b>'+ cur.photosUploadNum +'</b>');
  },

  queueComplete: function(numFilesUploaded) {
    $('#photos_add_bar .swfupload_wrap').css({position: 'relative', visibility: 'visible'});;
    $('#photos_add_bar_progress').hide();
    $('#photos_add_bar').css({height: 'auto'});

    cur.photosUploadNum = 0;

    PurchasePhotos.getMore();
  },

  getMore: function() {
    ajax.post('/purchase'+ A.purchasePhotosCur +'/addmany?last_id='+ A.purchasePhotosLastId, {}, function(r) {
      if (r.html && r.last_id) {
        $(r.html).appendTo('#photos_list');
        A.purchasePhotosLastId = parseInt(r.last_id);
      }
    });
  },

  addGood: function(purchase_id, pk_id) {
    var $form = $('#photo'+ pk_id + '_form');
    $('#photo'+ pk_id +'_progress').show();

    $form.find('.input_error').remove();

    ajax.post('/purchase'+ purchase_id +'/addmany?pk_id='+ pk_id, $form.serialize(), function(r) {
      $('#photo'+ pk_id +'_progress').hide();

      if (r.success) {
        $('#photo'+ pk_id +' .photo img').hide();
        $('#photo'+ pk_id +' .info form').hide();

        $('<div class="dld">Товар добавлен в закупку</div>').insertAfter('#photo' + pk_id + ' .info form');
      }
      else {
        $.each(r, function(i, v) {
          $('#'+ i).addClass('error');
          if ($.isArray(v)) {
            var string = [];
            $.each(v, function(i2, v2) {
              string.push(v2);
            })
            inputError($('#'+ i), string.join(''));
          }
          else inputError($('#'+ i), v);
        });
      }
    }, function() {
      $('#photo'+ pk_id +'_progress').hide();
    });

    return false;
  },

  deleteGood: function(purchase_id, pk_id) {
    $('#photo'+ pk_id +'_progress').show();

    ajax.post('/purchase'+ purchase_id +'/addmany?pk_id='+ pk_id + '&del=1', {}, function(r) {
      $('#photo'+ pk_id +'_progress').hide();

      $('#photo'+ pk_id +' .photo img').hide();
      $('#photo'+ pk_id +' .info form').hide();

      $('<div class="dld">Фотография удалена</div>').insertAfter('#photo' + pk_id + ' .info form');
    }, function() {
      $('#photo'+ pk_id +'_progress').hide();
    });

    return false;
  }
};

try {stmgr.loaded('purchase_photos.js');}catch(e){}