var Album = {
  showMsg: function(msg) {
    $('#album_error').hide();
    $('#album_result').html('<div class="msg" id="album_msg">'+ msg +'</div>').show();
    $('#album_msg').animate({backgroundColor: '#F9F6E7'});
  },

  add: function() {
    var b = showBox('/photoarchive/albums/add', {}, {}).setButtons(getLang('global_add'), function() {
      var name = $.trim($('#name').val());

      if (!name) {
        highlight('#name');
        return false;
      }

      var postdata = {
        name: name
      };

      ajax.post('/photoarchive/albums/add', postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Album.showMsg(r.message);
            nav.reload();
          }
          else {
            $('#album_result').hide();
            $('#album_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },

  edit: function(id) {
    var b = showBox('/photoarchive/albums/edit?id='+ id, {}, {}).setButtons(getLang('global_save'), function() {
      var name = $.trim($('#name').val());

      if (!name) {
        highlight('#name');
        return false;
      }

      var postdata = {
        name: name
      };

      ajax.post('/photoarchive/albums/edit?id='+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Album.showMsg(r.message);
            nav.reload();
          }
          else {
            $('#album_result').hide();
            $('#album_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },

  delete: function(id) {
    var b = showFastBox('Удаление альбома', 'Вы действительно хотите удалить альбом?', 'Удалить', function() {
      ajax.post('/photoarchive/albums/delete?id='+ id, {}, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          b.hide();
          boxPopup(r.message);
          nav.reload();
        },
        onFail: function(x) {
        }
      });
    }, 'Отмена');
  },

  deletePhoto: function(id, ev) {
    ev.stopPropagation();

    ajax.post('/photoarchive/photos/delete?id='+ id, {}, {
      showProgress: function() {
        $('<img src="/images/upload_inv.gif" style="position: absolute; top: 50%; left: 36%" />').appendTo($('#photo'+ id + ' a'));
      },
      hideProgress: function() {
        $('#photo'+ id + ' a img:last-child').remove();
      },
      onDone: function(r) {
        $('#photo'+ id).remove();
      },
      onFail: function(x) {
      }
    });
  },

  initList: function() {
    $('.album_row').mouseenter(function(e) {
      $(this).find('.album_info_back, .album_delete_back').stop(true, true).animate({opacity: 0.5});
      $(this).find('.album_info_cont, .album_delete_cont').stop(true, true).animate({opacity: 0.8});
    }).mouseleave(function(e) {
      $(this).find('.album_info_back, .album_delete_back, .album_info_cont, .album_delete_cont')
        .stop(true, true).animate({opacity: 0});
    });
    $('.album_row .album_info, .album_row .album_delete').mouseenter(function(e) {
      $(this).children('[class*="back"]').stop(true, true).animate({opacity: 0.6});
      $(this).children('[class*="cont"]').stop(true, true).animate({opacity: 1});
    }).mouseleave(function(e) {
      $(this).children('[class*="back"]').stop(true, true).animate({opacity: 0.5});
      $(this).children('[class*="cont"]').stop(true, true).animate({opacity: 0.8});
    });
  },

  initPhotoList: function() {
    $('.photo_row').mouseenter(function(e) {
      $(this).find('.photo_delete_back').stop(true, true).animate({opacity: 0.5});
      $(this).find('.photo_delete_cont').stop(true, true).animate({opacity: 0.8});
    }).mouseleave(function(e) {
      $(this).find('.photo_delete_back, .photo_delete_cont')
        .stop(true, true).animate({opacity: 0});
    });
    $('.photo_row .photo_info, .photo_row .photo_delete').mouseenter(function(e) {
      $(this).children('[class*="back"]').stop(true, true).animate({opacity: 0.6});
      $(this).children('[class*="cont"]').stop(true, true).animate({opacity: 1});
    }).mouseleave(function(e) {
      $(this).children('[class*="back"]').stop(true, true).animate({opacity: 0.5});
      $(this).children('[class*="cont"]').stop(true, true).animate({opacity: 0.8});
    });
  },

  initUploader: function(opts) {
    var swfu, settings = {
      flash_url : '/images/swfupload.swf',
      upload_url: opts.uploadAction,
      post_params: {ext: 'photoarchive', id: opts.id, upload_id: -1, type: 'photo'},
      file_size_limit : "20 MB",
      file_types : "*.jpg;*.gif",
      file_types_description : "Images",
      file_upload_limit : 20,
      file_queue_limit : 0,

      // Button settings
      button_placeholder_id: "swfupload_button",
      button_width: "789",
      button_height: "57",
      button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
      button_cursor: SWFUpload.CURSOR.HAND,

      // Events
      file_dialog_complete_handler : Album.fileDialogComplete,
      upload_start_handler : Album.uploadStart,
      upload_progress_handler : Album.uploadProgress,
      upload_error_handler : Album.uploadError,
      upload_success_handler : Album.uploadSuccess,
      queue_complete_handler : Album.queueComplete	// Queue plugin event
    };

    swfu = new SWFUpload(settings);
  },

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
          topError("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
          break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
          topError("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
          break;
        case SWFUpload.UPLOAD_ERROR.IO_ERROR:
          topError("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
          break;
        case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
          topError("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
          break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
          topError("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
          break;
        case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
          topError("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
          break;
        case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
          // If there aren't any files left (they were all cancelled) disable the cancel button
          topError("Cancelled");
          break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
          topError("Stopped");
          break;
        default:
          topError("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
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

    nav.reload();
  }
};

try{stManager.done('albums.js');}catch(e){}