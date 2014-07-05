var OrgType = {
  add: function() {
    var b = showBox('/orgs/default/addtype', {}, {params: {bodyStyle: 'padding: 1px 14px 16px;'}}).setButtons(getLang('global_add'), function() {
      var type_name = $.trim($('#type_name').val()), afisha = $('#afisha').val();

      if (!type_name) {
        highlight('#type_name');
        return false;
      }

      var params = {
        type_name: type_name,
        afisha: afisha
      };

      ajax.post('/orgs/default/addtype', params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            OrgType.showMsg(r.message);
            $('#type_name').val('');
          }
          else {
            $('#org_type_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  edit: function(id) {
    var b = showBox('/orgs/default/edittype?id='+ id, {}, {params: {bodyStyle: 'padding: 1px 14px 16px;'}}).setButtons(getLang('global_save'), function() {
      var type_name = $.trim($('#type_name').val()), afisha = $('#afisha').val();

      if (!type_name) {
        highlight('#type_name');
        return false;
      }

      var params = {
        type_name: type_name,
        afisha: afisha
      };

      ajax.post('/orgs/default/edittype?id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            OrgType.showMsg(r.message);
          }
          else {
            $('#org_type_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  delete: function(id) {
    var b = showFastBox('Удаление типа организации', 'Вы действительно хотите удалить данный тип?', getLang('global_delete'), function() {
      ajax.post('/orgs/default/deletetype?id='+ id, {}, {
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

  showMsg: function(msg) {
    $('#org_type_error').hide();
    $('#org_type_result').html('<div class="msg" id="org_type_msg">'+ msg +'</div>').show();
    $('#org_type_msg').animate({backgroundColor: '#F9F6E7'});
    $(window).scrollTop(200);
  },

  init: function() {
    placeholderSetup('#c_type_name', {back: true});
  }
}

try{stManager.done('org_types.js');}catch(e){}