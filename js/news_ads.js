var NewsAds = {
  showMsg: function(msg) {
    $('#news_error').html('').hide();
    $('#news_result').html('<div class="msg" id="news_msg">'+ msg +'</div>').show();
    $('#news_msg').animate({backgroundColor: '#F9F6E7'});
  },
  add: function() {
    var b = showBox('/news/ads/add', {}, {params: {}}).setButtons(getLang('global_add'), function() {
      var city_id = $('#city_id').val(), banner = $.trim($('#banner').val()), weight = $('#weight').val();

      if (!banner) {
        highlight('#banner');
        return false;
      }

      if (!city_id || city_id == "0") {
        highlight($('#city_id').parent(), false, true);
        highlight($('#city_id').prev(), false, true);
        return false;
      }

      if (!weight) {
        highlight('#weight');
        return false;
      }

      var postdata = {
        city_id: city_id,
        banner: banner,
        weight: weight
      };

      ajax.post('/news/ads/add', postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            NewsAds.showMsg(r.message);
            $('#weight').val('');

            cur.uiCities.reset();
          }
          else {
            $('#news_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  edit: function(id) {
    var b = showBox('/news/ads/edit?id='+ id, {}, {params: {}}).setButtons(getLang('global_save'), function() {
      var city_id = $('#city_id').val(), banner = $.trim($('#banner').val()), weight = $('#weight').val();

      if (!banner) {
        highlight('#banner');
        return false;
      }

      if (!city_id || city_id == "0") {
        highlight($('#city_id').parent(), false, true);
        highlight($('#city_id').prev(), false, true);
        return false;
      }

      if (!weight) {
        highlight('#weight');
        return false;
      }

      var postdata = {
        city_id: city_id,
        banner: banner,
        weight: weight
      };

      ajax.post('/news/ads/edit?id='+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            NewsAds.showMsg(r.message);
          }
          else {
            $('#news_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  delete: function(id) {
    var b = showFastBox('Удаление баннера', 'Вы действительно хотите удалить баннер?', getLang('global_delete'), function() {
      ajax.post('/news/ads/delete/id/'+ id, {}, {
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
    Upload.initSinglePhoto('banner', {
      size: 'd',
      action: cur.uploadAction
    });

    cur.uiCities = new Dropdown('city_id', {
      width: 378,
      label: 'Выберите город',
      items: opts.cities
    });
  }
}

try{stManager.done('news_ads.js');}catch(e){}