var Delivery = {
  showMsg: function(msg) {
    $('#delivery_error').html('').hide();
    $('#delivery_result').html('<div class="msg" id="delivery_msg">'+ msg +'</div>').show();
    $('#delivery_msg').animate({backgroundColor: '#F9F6E7'});
  },
  showSettingsBox: function(id) {
    var b = showBox('/orgs/delivery/settings?id='+ id, {}, {params: {}}).setButtons(getLang('global_save'), function() {
      var logo = $('#logo').val(), fullstory = $.trim($('#fullstory').val()), sms_phone = $.trim($('#sms_phone').val()),
        disable_cart = $('#disable_cart').val();

      var postdata = {
        logo: logo,
        fullstory: fullstory,
        sms_phone: sms_phone,
        disable_cart: disable_cart
      };

      ajax.post('/orgs/delivery/settings?id='+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Delivery.showMsg(r.message);
          }
          else {
            $('#delivery_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  addGood: function(id) {
    var b = showBox('/orgs/delivery/addGood?id='+ id, {}, {params: {}}).setButtons(getLang('global_add'), function() {
      var element_id = $('#element_id').val(), facephoto = $('#facephoto').val(), name = $.trim($('#name').val()),
        shortstory = $.trim($('#shortstory').val()), price = $.trim($('#price').val()), discount = $.trim($('#discount').val());

      if (!element_id || element_id == "0") {
        highlight($('#element_id').parent(), false, true);
        highlight($('#element_id').prev(), false, true);
        return false;
      }

      if (!name) {
        highlight('#name');
        return false;
      }

      if (!facephoto) {
        highlight($('#facephoto').parent());
        return false;
      }

      if (!shortstory) {
        highlight('#shortstory');
        return false;
      }

      if (!price) {
        highlight('#price');
        return false;
      }

      var postdata = {
        element_id: element_id,
        name: name,
        facephoto: facephoto,
        shortstory: shortstory,
        price: price,
        discount: discount
      };

      ajax.post('/orgs/delivery/addGood?id='+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Delivery.showMsg(r.message);
            $('#name, #shortstory, #price, #discount').val('');

            //cur.uiCategories.reset();
          }
          else {
            $('#delivery_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  editGood: function(id) {
    var b = showBox('/orgs/delivery/editGood?id='+ id, {}, {params: {}}).setButtons(getLang('global_save'), function() {
      var element_id = $('#element_id').val(), facephoto = $('#facephoto').val(), name = $.trim($('#name').val()),
        shortstory = $.trim($('#shortstory').val()), price = $.trim($('#price').val()), discount = $.trim($('#discount').val());

      if (!element_id || element_id == "0") {
        highlight($('#element_id').parent(), false, true);
        highlight($('#element_id').prev(), false, true);
        return false;
      }

      if (!name) {
        highlight('#name');
        return false;
      }

      if (!facephoto) {
        highlight($('#facephoto').parent());
        return false;
      }

      if (!shortstory) {
        highlight('#shortstory');
        return false;
      }

      if (!price) {
        highlight('#price');
        return false;
      }

      var postdata = {
        element_id: element_id,
        name: name,
        facephoto: facephoto,
        shortstory: shortstory,
        price: price,
        discount: discount
      };

      ajax.post('/orgs/delivery/editGood?id='+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Delivery.showMsg(r.message);
          }
          else {
            $('#delivery_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  deleteGood: function(id) {
    var b = showFastBox('Удаление товара', 'Вы действительно хотите удалить товар?', getLang('global_delete'), function() {
      ajax.post('/orgs/delivery/deleteGood?id='+ id, {}, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          b.hide();
          nav.reload();
        }
      });
    }, getLang('global_cancel'));
  },
  addMenuElement: function(id) {
    var b = showBox('/orgs/delivery/addMenuElement?id='+ id, {}, {params: {}}).setButtons(getLang('global_add'), function() {
      var category_id = $('#category_id').val(), icon = $('#icon').val(), name = $.trim($('#name').val());

      if (!category_id || category_id == "0") {
        highlight($('#category_id').parent(), false, true);
        highlight($('#category_id').prev(), false, true);
        return false;
      }

      if (!name) {
        highlight('#name');
        return false;
      }

      if (!icon) {
        highlight($('#icon').parent());
        return false;
      }

      var postdata = {
        category_id: category_id,
        name: name,
        icon: icon
      };

      ajax.post('/orgs/delivery/addMenuElement?id='+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Delivery.showMsg(r.message);
            $('#name').val('');

            cur.uiCategories.reset();
          }
          else {
            $('#delivery_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  editMenuElement: function(id) {
    var b = showBox('/orgs/delivery/editMenuElement?id='+ id, {}, {params: {}}).setButtons(getLang('global_save'), function() {
      var category_id = $('#category_id').val(), icon = $('#icon').val(), name = $.trim($('#name').val());

      if (!category_id || category_id == "0") {
        highlight($('#category_id').parent(), false, true);
        highlight($('#category_id').prev(), false, true);
        return false;
      }

      if (!name) {
        highlight('#name');
        return false;
      }

      if (!icon) {
        highlight($('#icon').parent());
        return false;
      }

      var postdata = {
        category_id: category_id,
        name: name,
        icon: icon
      };

      ajax.post('/orgs/delivery/editMenuElement?id='+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Delivery.showMsg(r.message);
            $('#name').val('');

            cur.uiCategories.reset();
          }
          else {
            $('#delivery_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  deleteMenuElement: function(id) {
    var b = showFastBox('Удаление элемента меню', 'Вы действительно хотите удалить элемент меню доставки?', getLang('global_delete'), function() {
      ajax.post('/orgs/delivery/deleteMenuElement?id='+ id, {}, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          b.hide();
          nav.reload();
        }
      });
    }, getLang('global_cancel'));
  },

  initMenuElementForm: function(opts) {
    Upload.initSinglePhoto('icon', {
      action: cur.uploadAction,
      size: 'a'
    });

    cur.uiCategories = new Dropdown('category_id', {
      width: 370,
      label: 'Выберите категорию',
      items: opts.categories
    });
  },
  initGoodForm: function(opts) {
    Upload.initSinglePhoto('facephoto', {
      action: cur.uploadAction,
      size: 'a'
    });

    cur.uiCategories = new Dropdown('element_id', {
      width: 370,
      label: 'Выберите элемент меню',
      items: opts.categories
    });

    autosizeSetup('#shortstory', {exact: true, minHeight: 64, maxHeight: 128});
  },
  initSettingsForm: function(opts) {
    Upload.initSinglePhoto('logo', {
      action: cur.uploadAction,
      size: 'a'
    });
    autosizeSetup('#fullstory', {exact: true, minHeight: 64, maxHeight: 128});
    cur.uiDisableCart = new Checkbox('disable_cart', {label: 'Отключить возможность заказов'});
  }
}

try{stManager.done('delivery.js');}catch(e){}