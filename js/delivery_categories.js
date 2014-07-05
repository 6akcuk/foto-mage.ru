var DeliveryCategory = {
  showMsg: function(msg) {
    $('#delivery_error').html('').hide();
    $('#delivery_result').html('<div class="msg" id="delivery_msg">'+ msg +'</div>').show();
    $('#delivery_msg').animate({backgroundColor: '#F9F6E7'});
  },
  add: function() {
    var b = showBox('/orgs/delivery/addCategory', {}, {params: {}}).setButtons(getLang('global_add'), function() {
      var name = $.trim($('#name').val());

      if (!name) {
        highlight('#name');
        return false;
      }

      var params = {
        name: name
      };

      ajax.post('/orgs/delivery/addCategory', params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            DeliveryCategory.showMsg(r.message);
            $('#name').val('');
          }
          else {
            $('#delivery_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  edit: function(id) {
    var b = showBox('/orgs/delivery/editCategory?id='+ id, {}, {params: {}}).setButtons(getLang('global_save'), function() {
      var name = $.trim($('#name').val());

      if (!name) {
        highlight('#name');
        return false;
      }

      var params = {
        name: name
      };

      ajax.post('/orgs/delivery/editCategory?id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            DeliveryCategory.showMsg(r.message);
          }
          else {
            $('#delivery_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  delete: function(id) {
    var b = showFastBox('Удаление категории', 'Вы действительно хотите удалить категорию доставки?', getLang('global_delete'), function() {
      ajax.post('/orgs/delivery/deleteCategory?id='+ id, {}, {
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
    }, getLang('global_cancel'));
  },

  init: function() {

  }
};

try{stManager.done('delivery_categories.js');}catch(e){}