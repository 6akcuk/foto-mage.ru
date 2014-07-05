var News = {
  showMsg: function(msg) {
    $('#news_error').html('').hide();
    $('#news_result').html('<div class="msg" id="news_msg">'+ msg +'</div>').show();
    $('#news_msg').animate({backgroundColor: '#F9F6E7'});
  },
  add: function() {
    var b = showBox('/news/default/add', {}, {params: {}}).setButtons(getLang('global_add'), function() {
      var city_id = $('#city_id').val(), title = $.trim($('#title').val()), fullstory = $.trim($('#fullstory').val()),
        facephoto = $.trim($('#facephoto').val()), attaches = {};

      if (!facephoto) {
        highlight('#facephoto_cont');
        return false;
      }

      if (!city_id || city_id == "0") {
        highlight($('#city_id').parent(), false, true);
        highlight($('#city_id').prev(), false, true);
        return false;
      }

      if (!title) {
        highlight('#title');
        return false;
      }

      if (!fullstory) {
        highlight('#fullstory');
        return false;
      }

      if (document.getElementById('ap_form')) {
        if ($('#ap_form').find('div.media_photo_preview').children().length) {
          attaches.photo = [];
          $('#ap_form').find('div.media_photo_preview').find('input[type="hidden"]').each(function(i, el) {
            attaches.photo.push($(this).val());
          });
        }
        if ($('#ap_form').find('div.media_document_preview').children().length) {
          attaches.document = [];
          $('#ap_form').find('div.media_document_preview').find('input[type="hidden"]').each(function(i, el) {
            attaches.document.push($(this).val());
          });
        }
      }

      var postdata = {
        city_id: city_id,
        title: title,
        fullstory: fullstory,
        attaches: attaches,
        facephoto: facephoto
      };

      ajax.post('/news/default/add', postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            News.showMsg(r.message);
            $('#title, #fullstory').val('');

            cur.uiNewsCities.reset();

            Upload.initAttaches('news_form_attach_wrap', {prefix: 'ap', allow: ['photo', 'document']});
          }
          else {
            $('#news_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  edit: function(id) {
    var b = showBox('/news/default/edit?id='+ id, {}, {params: {}}).setButtons(getLang('global_save'), function() {
      var city_id = $('#city_id').val(), title = $.trim($('#title').val()), fullstory = $.trim($('#fullstory').val()),
        facephoto = $.trim($('#facephoto').val()), attaches = {};

      if (!facephoto) {
        highlight('#facephoto_cont');
        return false;
      }

      if (!city_id || city_id == "0") {
        highlight($('#city_id').parent(), false, true);
        highlight($('#city_id').prev(), false, true);
        return false;
      }

      if (!title) {
        highlight('#title');
        return false;
      }

      if (!fullstory) {
        highlight('#fullstory');
        return false;
      }

      if (document.getElementById('ap_form')) {
        if ($('#ap_form').find('div.media_photo_preview').children().length) {
          attaches.photo = [];
          $('#ap_form').find('div.media_photo_preview').find('input[type="hidden"]').each(function(i, el) {
            attaches.photo.push($(this).val());
          });
        }
        if ($('#ap_form').find('div.media_document_preview').children().length) {
          attaches.document = [];
          $('#ap_form').find('div.media_document_preview').find('input[type="hidden"]').each(function(i, el) {
            attaches.document.push($(this).val());
          });
        }
      }

      var postdata = {
        city_id: city_id,
        title: title,
        fullstory: fullstory,
        attaches: attaches,
        facephoto: facephoto
      };

      ajax.post('/news/default/edit?id='+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            News.showMsg(r.message);
          }
          else {
            $('#news_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  delete: function(id) {
    var b = showFastBox('Удаление новости', 'Вы действительно хотите удалить новость?', getLang('global_delete'), function() {
      ajax.post('/news/default/delete/id/'+ id, {}, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          b.hide();
          boxPopup(r.message);
          nav.reload();
        }
      });
    }, getLang('global_cancel'));
  },

  init: function() {

  },
  initForm: function(opts) {
    Upload.initSinglePhoto('facephoto', {
      size: 'b',
      action: cur.uploadAction,
      selector_html: '<a id="facephoto_cont" class="news_upload_photo">Прикрепить фотографию</a>'
    });
    Upload.initAttaches('news_form_attach_wrap', {prefix: 'ap', allow: ['photo', 'document'], attaches: opts.attaches || null});

    cur.uiNewsCities = new Dropdown('city_id', {
      width: 258,
      label: 'Выберите город',
      items: opts.cities
    });

    autosizeSetup('#fullstory', {minHeight: 64, maxHeight: 160, exact: true});
  }
}

try{stManager.done('news.js');}catch(e){}