var Market = {
  showMsg: function(msg) {
    $('#market_good_error').html('').hide();
    $('#market_good_result').html('<div class="msg" id="market_good_msg">'+ msg +'</div>').show();
    $('#market_good_msg').animate({backgroundColor: '#F9F6E7'});
  },
  addGood: function(org_id) {
    var b = showBox('/orgs/market/addGood?id='+ org_id, {}, {params: {}}).setButtons(getLang('global_add'), function() {
      var category_id = $('#category_id').val(), facephoto = $('#facephoto').val(), name = $.trim($('#name').val()),
        shortstory = $.trim($('#shortstory').val()), price = $.trim($('#price').val()), discount = $.trim($('#discount').val());

      if (!category_id || category_id == "0") {
        highlight($('#category_id').parent(), false, true);
        highlight($('#category_id').prev(), false, true);
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
        category_id: category_id,
        name: name,
        facephoto: facephoto,
        shortstory: shortstory,
        price: price,
        discount: discount
      };

      ajax.post('/orgs/market/addGood?id='+ org_id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Market.showMsg(r.message);
            $('#name, #shortstory, #price, #discount').val('');

            //cur.uiCategories.reset();
          }
          else {
            $('#market_good_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  editGood: function(id) {
    var b = showBox('/orgs/market/editGood?id='+ id, {}, {params: {}}).setButtons(getLang('global_save'), function() {
      var category_id = $('#category_id').val(), facephoto = $('#facephoto').val(), name = $.trim($('#name').val()),
        shortstory = $.trim($('#shortstory').val()), price = $.trim($('#price').val()), discount = $.trim($('#discount').val());

      if (!category_id || category_id == "0") {
        highlight($('#category_id').parent(), false, true);
        highlight($('#category_id').prev(), false, true);
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
        category_id: category_id,
        name: name,
        facephoto: facephoto,
        shortstory: shortstory,
        price: price,
        discount: discount
      };

      ajax.post('/orgs/market/editGood?id='+ org_id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Market.showMsg(r.message);
          }
          else {
            $('#market_good_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  deleteGood: function(id) {
    var b = showFastBox('Удаление товара', 'Вы действительно хотите удалить товар?', getLang('global_delete'), function() {
      ajax.post('/orgs/market/deleteGood?id='+ id, {}, {
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

  search: function(obj) {
    $.extend(true, nav.objLoc, obj);

    $('div.summary_wrap .pg_pages').hide();
    $('div.summary_wrap .progress').show();

    nav.objLoc.offset = 0;
    nav.go("/"+ nav.toStr(nav.objLoc), null);
  },

  init: function(opts) {
    placeholderSetup('#c_name', {back: true});

    cur.uiPostCategory = new Dropdown('c[category_id]', {
      width: 178,
      label: 'Выберите категорию',
      items: opts.categories,
      change: function(val) {
        Market.search({c: {category_id: val}});
      }
    });

    $('#c_name').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cname);
      cur.tm_cname = setTimeout(function() {
        Market.search({c: {name: self.val()}});
      }, 500);
    });
  },

  initGoodForm: function(opts) {
    Upload.initSinglePhoto('facephoto', {
      action: cur.uploadAction,
      size: 'a'
    });

    cur.uiCategories = new Dropdown('category_id', {
      width: 370,
      label: 'Выберите категорию',
      items: opts.categories
    });

    autosizeSetup('#shortstory', {exact: true, minHeight: 64, maxHeight: 128});
  }
}

try{stManager.done('market.js');}catch(e){}