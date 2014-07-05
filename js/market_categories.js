var MarketCategory = {
  showMsg: function(msg) {
    $('#market_category_error').html('').hide();
    $('#market_category_result').html('<div class="msg" id="market_category_msg">'+ msg +'</div>').show();
    $('#market_category_msg').animate({backgroundColor: '#F9F6E7'});
  },
  add: function() {
    var b = showBox('/market/category/add', {}, {params: {bodyStyle: 'padding: 1px 14px 16px;'}}).setButtons(getLang('global_add'), function() {
      var name = $.trim($('#name').val()), parent_id = $('#parent_id').val(), no_title = $('#no_title').val(),
        title_form = $.trim($('#title_form').val()), no_price = $('#no_price').val();

      if (!name) {
        highlight('#name');
        return false;
      }

      var params = {
        name: name,
        parent_id: parent_id,
        no_title: no_title,
        title_form: title_form,
        no_price: no_price
      };

      ajax.post('/market/category/add', params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            MarketCategory.showMsg(r.message);
            $('#name').val('');
            cur.uiParent.reset();
          }
          else {
            $('#market_category_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  edit: function(id) {
    var b = showBox('/market/category/edit?id='+ id, {}, {params: {bodyStyle: 'padding: 1px 14px 16px;'}}).setButtons(getLang('global_save'), function() {
      var name = $.trim($('#name').val()), parent_id = $('#parent_id').val(), no_title = $('#no_title').val(),
        title_form = $.trim($('#title_form').val()), no_price = $('#no_price').val();

      if (!name) {
        highlight('#name');
        return false;
      }

      var params = {
        name: name,
        parent_id: parent_id,
        no_title: no_title,
        title_form: title_form,
        no_price: no_price
      };

      ajax.post('/market/category/edit?id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            MarketCategory.showMsg(r.message);
          }
          else {
            $('#market_category_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  delete: function(id) {
    var b = showFastBox('Удаление категории товаров', 'Вы действительно хотите удалить категорию? Данное действие необратимо и приведет к удалению всех подкатегорий ' +
      'и потере связи с выставленными товарами.', getLang('global_delete'), function() {
      ajax.post('/market/category/delete?id='+ id, {}, {
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
    placeholderSetup('#c_name', {back: true});
  },
  initForm: function(opts) {
    cur.uiParent = new Dropdown('parent_id', {
      width: 368,
      label: 'Выберите категорию',
      items: opts.categories
    });

    cur.uiNoTitle = new Checkbox('no_title', {
      label: 'Заголовок не требуется',
      change: function(val) {
        if (val == 1) {
          $('#aac_title_label, #aac_title').show();
        } else {
          $('#aac_title_label, #aac_title').hide();
        }
      }
    });

    cur.uiNoPrice = new Checkbox('no_price', {
      label: 'Цена не требуется'
    });
  }
};

try{stManager.done('market_categories.js');}catch(e){}