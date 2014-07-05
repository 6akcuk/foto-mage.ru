var Discount = {
  showMsg: function(msg) {
    $('#org_error').html('').hide();
    $('#org_result').html('<div class="msg" id="org_msg">'+ msg +'</div>').show();
    $('#org_msg').animate({backgroundColor: '#F9F6E7'});
  },
  giveCard: function(id) {
    var b = showBox('/orgs/discount/giveCard?id='+ id, {}, {params: {}}).setButtons(getLang('global_add'), function() {
      var name = $.trim($('#name').val()), owner_id = $.trim($('#owner_id').val()), banner = $('#banner').val();

      if (!name) {
        highlight('#name');
        return false;
      }

      if (!banner) {
        highlight($('#banner').parent());
        return false;
      }

      if (!owner_id) {
        highlight('#owner_id');
        return false;
      }

      var postdata = {
        name: name,
        banner: banner,
        owner_id: owner_id
      };

      ajax.post('/orgs/discount/giveCard?id='+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Discount.showMsg(r.message);
            $('#name, #owner_id').val('');
            nav.reload();
          }
          else {
            $('#org_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  deleteCard: function(id) {
    var b = showFastBox('Удаление карты', 'Вы действительно хотите удалить дисконтную карту?', getLang('global_delete'), function() {
      ajax.post('/orgs/discount/deleteCard?id='+ id, {}, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          b.hide();
          nav.reload();
        }
      });
    }, getLang('global_cancel'));
  },
  addAction: function(id) {
    var b = showBox('/orgs/discount/addAction?id='+ id, {}, {params: {}}).setButtons(getLang('global_add'), function() {
      var name = $.trim($('#name').val()), start_time = $('#start_time').val(), end_time = $('#end_time').val(),
        pc_limits = $.trim($('#pc_limits').val()), ch_st = $('#ch_st').val(), ch_et = $('#ch_et').val(),
        banner = $('#banner').val(), fullstory = $.trim($('#fullstory').val());

      if (!name) {
        highlight('#name');
        return false;
      }

      if (!banner) {
        highlight($('#banner').parent());
        return false;
      }

      var postdata = {
        name: name,
        fullstory: fullstory,
        banner: banner,
        pc_limits: pc_limits
      };
      if (ch_st == 1) postdata.start_time = start_time;
      if (ch_et == 1) postdata.end_time = end_time;

      ajax.post('/orgs/discount/addAction?id='+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Discount.showMsg(r.message);
            $('#name, #pc_limits').val('');
            nav.reload();
          }
          else {
            $('#org_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  editAction: function(id) {
    var b = showBox('/orgs/discount/editAction?id='+ id, {}, {params: {}}).setButtons(getLang('global_save'), function() {
      var name = $.trim($('#name').val()), start_time = $('#start_time').val(), end_time = $('#end_time').val(),
        pc_limits = $.trim($('#pc_limits').val()), ch_st = $('#ch_st').val(), ch_et = $('#ch_et').val(),
        banner = $('#banner').val(), fullstory = $.trim($('#fullstory').val());

      if (!name) {
        highlight('#name');
        return false;
      }

      if (!banner) {
        highlight($('#banner').parent());
        return false;
      }

      var postdata = {
        name: name,
        fullstory: fullstory,
        banner: banner,
        pc_limits: pc_limits
      };
      if (ch_st == 1) postdata.start_time = start_time;
      if (ch_et == 1) postdata.end_time = end_time;

      ajax.post('/orgs/discount/editAction?id='+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Discount.showMsg(r.message);
          }
          else {
            $('#org_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  deleteAction: function(id) {
    var b = showFastBox('Удаление акции', 'Вы действительно хотите удалить акцию? Это приведет к утрате всей информации, включая промо-коды', getLang('global_delete'), function() {
      ajax.post('/orgs/discount/deleteAction?id='+ id, {}, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          b.hide();
          nav.reload();
        }
      });
    }, getLang('global_cancel'));
  },
  init: function() {

  },
  initActionForm: function() {
    autosizeSetup('#fullstory', {minHeight: 32, maxHeight: 96});

    Upload.initSinglePhoto('banner', {
      action: cur.uploadAction,
      size: 'a'
    });

    cur.uiStartTime = new Calendar('start_time', {
      width: 120
    });
    cur.uiEndTime = new Calendar('end_time', {
      width: 120
    });
  },
  initCodes: function() {
    placeholderSetup('#c_value', {back: true});
  },
  initCardForm: function() {
    Upload.initSinglePhoto('banner', {
      action: cur.uploadAction,
      size: 'a'
    });
  }
};

try{stManager.done('discount.js');}catch(e){}