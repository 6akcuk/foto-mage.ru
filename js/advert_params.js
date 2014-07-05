var AdvertParam = {
  showMsg: function(msg) {
    $('#advert_param_error').html('').hide();
    $('#advert_param_result').html('<div class="msg" id="advert_param_msg">'+ msg +'</div>').show();
    $('#advert_param_msg').animate({backgroundColor: '#F9F6E7'});
  },
  attemptAdd: function() {
    var title = $.trim($('#title').val()), parent_id = $('#parent_id').val(), category_id = $('#category_id').val(),
      type = $('#type').val(), suffix = $.trim($('#suffix').val());

    if (!category_id) {
      highlight($('#category_id').parent(), false, true);
      highlight($('#category_id').prev(), false, true);
      return false;
    }

    if (!title) {
      highlight('#title');
      return false;
    }

    if (!type) {
      highlight($('#type').parent(), false, true);
      highlight($('#type').prev(), false, true);
      return false;
    }

    var params = {
      title: title,
      parent_id: parent_id,
      category_id: category_id,
      type: type,
      suffix: suffix
    };

    ajax.post('/advert/param/add', params, {
      showProgress: curBox().showProgress,
      hideProgress: curBox().hideProgress,
      onDone: function(r) {
        if (r.success) {
          AdvertParam.showMsg(r.message);
          $('#title').val('').focus();
          //cur.uiParent.reset();
          //cur.uiParent.disable();
          //cur.uiCategory.reset();
          //cur.uiType.reset();
        }
        else {
          $('#advert_param_error').html(r.message).show();
        }
      }
    });
  },
  add: function() {
    var b = showBox('/advert/param/add', {}, {params: {}}).setButtons(getLang('global_add'), AdvertParam.attemptAdd, getLang('global_cancel'));
  },
  edit: function(id) {
    var b = showBox('/advert/param/edit?id='+ id, {}, {params: {}}).setButtons(getLang('global_save'), function() {
      var title = $.trim($('#title').val()), parent_id = $('#parent_id').val(), category_id = $('#category_id').val(),
        type = $('#type').val(), suffix = $.trim($('#suffix').val());

      if (!category_id) {
        highlight($('#category_id').parent(), false, true);
        highlight($('#category_id').prev(), false, true);
        return false;
      }

      if (!title) {
        highlight('#title');
        return false;
      }

      if (!type) {
        highlight($('#type').parent(), false, true);
        highlight($('#type').prev(), false, true);
        return false;
      }

      var params = {
        title: title,
        parent_id: parent_id,
        category_id: category_id,
        type: type,
        suffix: suffix
      };

      ajax.post('/advert/param/edit?id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            AdvertParam.showMsg(r.message);
          }
          else {
            $('#advert_param_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  delete: function(id) {
    var b = showFastBox('Удаление параметра', 'Вы действительно хотите удалить параметр? Это приведет к потери части данных в объявлениях.', getLang('global_delete'), function() {
      ajax.post('/advert/param/delete?id='+ id, {}, {
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
    placeholderSetup('#c_title', {back: true});
  },
  initForm: function(opts) {
    cur.uiCategory = new Dropdown('category_id', {
      width: 205,
      label: 'Выберите категорию',
      items: opts.categories,
      change: function(val) {
        if (val) {
          ajax.post('/advert/param/getParams/id/'+ val, {}, {
            showProgress: curBox().showProgress,
            hideProgress: curBox().hideProgress,
            onDone: function(r) {
              curBox().hideProgress();
              cur.uiParent.render(r.items);
              cur.uiParent.enable();
            }
          });
        } else {
          curBox().hideProgress();
          cur.uiParent.disable();
        }
      }
    });

    var params = {
      width: 205,
      label: 'Выберите параметр'
    };
    if (!opts.params) {
      params = $.extend({}, params, {
        disabled: true,
        disabledLabel: 'Выберите категорию'
      });
    } else {
      params.items = opts.params;
    }
    cur.uiParent = new Dropdown('parent_id', params);

    cur.uiType = new Dropdown('type', {
      width: 205,
      label: 'Выберите тип',
      items: [['input','Поле ввода'],['select','Выпадающий список'],['value','Конечное значение']],
      change: function(val) {
        if (val == 'input') $('#suffix_wrap').show();
        else $('#suffix_wrap').hide();
      }
    });
  }
};

try{stManager.done('advert_params.js');}catch(e){}