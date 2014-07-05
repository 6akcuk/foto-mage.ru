var City = {
  showMsg: function(msg) {
    $('#city_error').html('').hide();
    $('#city_result').html('<div class="msg" id="city_msg">'+ msg +'</div>').show();
    $('#city_msg').animate({backgroundColor: '#F9F6E7'});
  },
  add: function() {
    var b = showBox('/unify/cities/add', {}, {params: {}}).setButtons(getLang('global_add'), function() {
      var name = $.trim($('#name').val()), timezone = $.trim($('#timezone').val()), published = $('#published').val();

      if (!name) {
        highlight('#name');
        return false;
      }

      if (!timezone) {
        highlight($('#timezone').parent(), false, true);
        highlight($('#timezone').prev(), false, true);
        return false;
      }

      var postdata = {
        name: name,
        timezone: timezone,
        published: published
      };

      ajax.post('/unify/cities/add', postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            City.showMsg(r.message);
            $('#name').val('');
            cur.uiTimezones.reset();
          }
          else {
            $('#city_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  edit: function(id) {
    var b = showBox('/unify/cities/edit?id='+ id, {}, {params: {}}).setButtons(getLang('global_save'), function() {
      var name = $.trim($('#name').val()), timezone = $.trim($('#timezone').val()), published = $('#published').val();

      if (!name) {
        highlight('#name');
        return false;
      }

      if (!timezone) {
        highlight($('#timezone').parent(), false, true);
        highlight($('#timezone').prev(), false, true);
        return false;
      }

      var postdata = {
        name: name,
        timezone: timezone,
        published: published
      };

      ajax.post('/unify/cities/edit?id='+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            City.showMsg(r.message);
          }
          else {
            $('#city_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  delete: function(id) {
    var b = showFastBox('Удаление города', 'Вы действительно хотите удалить город?', getLang('global_delete'), function() {
      ajax.post('/unify/cities/delete/id/'+ id, {}, {
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
    cur.uiTimezones = new Dropdown('timezone', {
      width: 378,
      label: 'Выберите часовой пояс',
      items: opts.timezones
    });

    cur.uiPublic = new Checkbox('published', {
      label: 'Опубликовать город'
    });
  }
}

try{stManager.done('cities.js');}catch(e){}