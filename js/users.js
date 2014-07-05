var Users = {
  addRoleShowMsg: function(msg) {
    $('#rc_error').hide();
    $('#rc_result').html('<div class="msg" id="rc_msg">'+ msg +'</div>').show();
    $('#rc_msg').animate({backgroundColor: '#F9F6E7'});
    $(window).scrollTop(200);
  },
  editRoleShowMsg: function(msg) {
    $('#re_error').hide();
    $('#re_result').html('<div class="msg" id="re_msg">'+ msg +'</div>').show();
    $('#re_msg').animate({backgroundColor: '#F9F6E7'});
    $(window).scrollTop(200);
  },
  addRole: function() {
    var b = showBox('/users/roles/createRole', {}, {params: {bodyStyle: 'padding: 1px 14px 16px;'}}).setButtons(getLang('global_add'), function() {
      var role_name = $.trim($('#RoleForm_name').val()),
        role_descr = $.trim($('#RoleForm_description').val());

      if (!role_name) {
        highlight('#RoleForm_name');
        return false;
      }
      if (!role_descr) {
        highlight('#RoleForm_description');
        return false;
      }

      ajax.post('/users/roles/createRole', $('#addRoleForm').serialize(), {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Users.addRoleShowMsg(r.message);
            $('#RoleForm_name, #RoleForm_description, #RoleForm_bizrule').val('');
            nav.reload();
          }
          else {
            $('#rc_result').hide();
            $('#rc_error').html(r.message).show();
          }
        },
        onFail: function() {
        }
      });
    }, getLang('global_cancel'));
  },
  editRole: function(role) {
    var b = showBox('/users/roles/editRole?role='+ role, {}, {params: {bodyStyle: 'padding: 1px 14px 16px;'}}).setButtons(getLang('global_save'), function() {
      var role_name = $.trim($('#RoleForm_name').val()),
        role_descr = $.trim($('#RoleForm_description').val());

      if (!role_name) {
        highlight('#RoleForm_name');
        return false;
      }
      if (!role_descr) {
        highlight('#RoleForm_description');
        return false;
      }

      ajax.post('/users/roles/editRole?role='+ role, $('#editRoleForm').serialize(), {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Users.editRoleShowMsg(r.message);
            nav.reload();
          }
          else {
            $('#re_result').hide();
            $('#re_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  deleteRole: function(role) {
    var b = showFastBox('Удаление роли', 'Вы действительно хотите удалить роль?', 'Удалить', function() {
      ajax.post('/users/roles/deleteRole?role='+ role, {}, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          b.hide();
          boxPopup(r.msg);
          nav.reload();
        },
        onFail: function(x) {
        }
      });
    }, 'Отмена');
  },
  addOperationShowMsg: function(msg) {
    $('#op_create_error').hide();
    $('#op_create_result').html('<div class="msg" id="op_create_msg">'+ msg +'</div>').show();
    $('#op_create_msg').animate({backgroundColor: '#F9F6E7'});
    $(window).scrollTop(200);
  },
  editOperationShowMsg: function(msg) {
    $('#op_edit_error').hide();
    $('#op_edit_result').html('<div class="msg" id="re_msg">'+ msg +'</div>').show();
    $('#op_edit_msg').animate({backgroundColor: '#F9F6E7'});
    $(window).scrollTop(200);
  },
  addOperation: function() {
    var b = showBox('/users/roles/createOperation', {}, {params: {bodyStyle: 'padding: 1px 14px 16px;'}}).setButtons(getLang('global_add'), function() {
      var op_name = $.trim($('#OperationForm_name').val()),
        op_descr = $.trim($('#OperationForm_description').val());

      if (!op_name) {
        highlight('#OperationForm_name');
        return false;
      }
      if (!op_descr) {
        highlight('#OperationForm_description');
        return false;
      }

      ajax.post('/users/roles/createOperation', $('#addOperationForm').serialize(), {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Users.addOperationShowMsg(r.message);
            $('#OperationForm_name, #OperationForm_description, #OperationForm_bizrule').val('');
            nav.reload();
          }
          else {
            $('#op_create_result').hide();
            $('#op_create_error').html(r.message).show();
          }
        },
        onFail: function() {
        }
      });
    }, getLang('global_cancel'));
  },
  editOperation: function(op) {
    var b = showBox('/users/roles/editOperation?op='+ op, {}, {params: {bodyStyle: 'padding: 1px 14px 16px;'}}).setButtons(getLang('global_save'), function() {
      var op_name = $.trim($('#OperationForm_name').val()),
        op_descr = $.trim($('#OperationForm_description').val());

      if (!op_name) {
        highlight('#OperationForm_name');
        return false;
      }
      if (!op_descr) {
        highlight('#OperationForm_description');
        return false;
      }

      ajax.post('/users/roles/editOperation?op='+ op, $('#editOperationForm').serialize(), {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Users.editOperationShowMsg(r.message);
            nav.reload();
          }
          else {
            $('#op_edit_result').hide();
            $('#op_edit_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  deleteOperation: function(op) {
    var b = showFastBox('Удаление операции', 'Вы действительно хотите удалить операцию?', 'Удалить', function() {
      ajax.post('/users/roles/deleteOperation?op='+ op, {}, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          b.hide();
          boxPopup(r.msg);
          nav.reload();
        },
        onFail: function(x) {
        }
      });
    }, 'Отмена');
  },
  connectRole: function(role) {
    var b = showBox('/users/roles/link?role='+ role, {}, {params: {bodyStyle: 'padding: 0px;', width: 550}}).setButtons(getLang('global_save'), function() {
      ajax.post('/users/roles/syncItems?role='+ role, {items: cur.roclistItems}, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          boxPopup(r.msg);
        },
        onFail: function(x) {

        }
      });
    }, getLang('global_cancel'));
  },

  roclistSelect: function(op, obj, event) {
    var attr_id = $(obj).parent().attr('id');
    if (attr_id.match(/roclist_sel/)) {
      $(document.getElementById('roclist_sel_'+ op)).remove();
      $(document.getElementById('roclist_'+ op)).show();

      if ($.trim($('#roclist_sel_list').html()) == '')
        $('#roclist_info').show();

      delete cur.roclistItems[op];
    }
    else {
      $(document.getElementById('roclist_'+ op)).hide();
      Users.roclistRender(op);
      $('#roclist_info').hide();
      cur.roclistItems[op] = 1;
    }

    cur.roclistScrollbar.update(false, true);
  },
  roclistRender: function(op) {
    var trg = document.getElementById('roclist_'+ op);

    $('<div id="roclist_sel_'+ op +'">\
      <table class="roclist_cell" cellspacing="0" cellpadding="0" onmousedown="Users.roclistSelect(\''+ op +'\', this, event)">\
      <tr>\
        <td>\
          <div class="roclist_item_name">'+ $(trg).find('div.roclist_item_name').text() +'</div>\
          <div class="roclist_item_description">'+ $(trg).find('div.roclist_item_description').text() +'</div>\
        </td>\
        <td class="roclist_item_act">\
          <div class="roclist_item_action"></div>\
        </td>\
      </tr>\
      </table>\
    </div>').appendTo('#roclist_sel_list');
  },
  assignRole: function(el, id) {
    var menu = $(el).data('dd_menu');
    if (!menu) {
      menu = $(el).data('dd_menu', new DDMenu(el, rolesList, {
        header: true,
        click: function(v) {
          Users.roleAssigned(el, id, v);
        }
      })).data('dd_menu');
    }
    menu.show();
  },
  roleAssigned: function(el, id, item) {
    ajax.post('/users/users/assignRole', {user_id: id, role: item}, {
      onDone: function(r) {
        if (r.msg) {
          boxPopup(r.msg);
          $(el).text(item);
        }
      },
      onFail: function(x) {
        try {
          var r = $.parseJSON(x.responseText);
        } catch(e) {}
        showFastBox(getLang('global_error'), (r && r.html) || getLang('global_page_error'));
      }
    });
  },
  addUser: function() {
    var b = showBox('/users/users/adduser', {}, {params: {}}).setButtons(getLang('global_add'), function() {
      var login = $.trim($('#add_user_login').val()), email = $.trim($('#add_user_email').val()), password = $.trim($('#add_user_password').val()),
        role = $.trim($('#add_user_role').val()), lastname = $.trim($('#add_user_lastname').val()), firstname = $.trim($('#add_user_firstname').val()),
        city = $.trim($('#add_user_city_id').val());

      if (!email) {
        highlight('#add_user_email');
        return false;
      }
      if (!login) {
        highlight('#add_user_login');
        return false;
      }
      if (!password) {
        highlight('#add_user_password');
        return false;
      }
      if (!role) {
        highlight($('#add_user_role').parent(), false, true);
        highlight($('#add_user_role').prev(), false, true);
        return false;
      }
      if (!city) {
        highlight($('#add_user_city_id').parent(), false, true);
        highlight($('#add_user_city_id').prev(), false, true);
        return false;
      }

      var params = {
        email: email,
        login: login,
        password: password,
        role: role,
        city: city,
        lastname: lastname,
        firstname: firstname
      };

      ajax.post('/users/users/addUser', params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Users.addUserShowMsg(r.message);
            $('#add_user_email, #add_user_login, #add_user_password, #add_user_lastname, #add_user_firstname').val('');
            cur.uiRolesDD.clear();
            cur.uiCitiesDD.clear();
          }
          else {
            $('#add_user_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  addUserShowMsg: function(msg) {
    $('#add_user_error').hide();
    $('#add_user_result').html('<div class="msg" id="add_user_msg">'+ msg +'</div>').show();
    $('#add_user_msg').animate({backgroundColor: '#F9F6E7'});
    $(window).scrollTop(200);
  },
  editUser: function(id) {
    var b = showBox('/users/users/editUser?id='+ id, {}, {params: {}}).setButtons(getLang('global_save'), function() {
      var login = $.trim($('#edit_user_login').val()), email = $.trim($('#edit_user_email').val()), password = $.trim($('#edit_user_password').val()),
        role = $.trim($('#edit_user_role').val()), lastname = $.trim($('#edit_user_lastname').val()), firstname = $.trim($('#edit_user_firstname').val()),
        city = $.trim($('#edit_user_city_id').val());

      if (!email) {
        highlight('#edit_user_email');
        return false;
      }
      if (!login) {
        highlight('#edit_user_login');
        return false;
      }
      if (!password) {
        highlight('#edit_user_password');
        return false;
      }
      if (!role) {
        highlight($('#edit_user_role').parent(), false, true);
        highlight($('#edit_user_role').prev(), false, true);
        return false;
      }
      if (!city) {
        highlight($('#edit_user_city_id').parent(), false, true);
        highlight($('#edit_user_city_id').prev(), false, true);
        return false;
      }

      var params = {
        email: email,
        login: login,
        password: password,
        role: role,
        city: city,
        lastname: lastname,
        firstname: firstname
      };

      ajax.post('/users/users/editUser?id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Users.editUserShowMsg(r.message);
          }
          else {
            $('#edit_user_error').html(r.message).show();
          }
        },
        onFail: function(x) {
          try {
            var r = $.parseJSON(x.responseText);
          } catch(e) {}
          showFastBox(getLang('global_error'), (r && r.html) || getLang('global_page_error'));
        }
      });
    }, getLang('global_cancel'));
  },
  editUserShowMsg: function(msg) {
    $('#edit_user_error').hide();
    $('#edit_user_result').html('<div class="msg" id="edit_user_msg">'+ msg +'</div>').show();
    $('#edit_user_msg').animate({backgroundColor: '#F9F6E7'});
    $(window).scrollTop(200);
  },
  deleteUser: function(id) {
    var b = showFastBox('Удаление пользователя', 'Вы действительно хотите удалить пользователя?', getLang('global_delete'), function() {
      ajax.post('/users/users/deleteUser?id='+ id, {}, {
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
  linkOrg: function(id) {
    showBox('/users/users/linkOrg?id='+ id, {}, {params: {bodyStyle: 'padding: 0px'}});
  },
  deleteLinkOrg: function(id, org_id) {
    ajax.post('/users/users/deleteLinkOrg?id='+ id +'&org_id='+ org_id, {}, {
      showProgress: curBox().showProgress,
      hideProgress: curBox().hideProgress,
      onDone: function(r) {
        Users.linkOrg(id);
      }
    });
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

    var city_dropdown = new Dropdown('c[city_id]', {
      width: 150,
      label: 'Выберите город',
      items: opts.cities,
      change: function(val) {
        Users.search({c: {city_id: val}});
      }
    });

    var role_dropdown = new Dropdown('c[role]', {
      width: 150,
      label: 'Выберите роль',
      items: opts.roles,
      change: function(val) {
        Users.search({c: {role: val}});
      }
    });

    $('#c_name').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cname);
      cur.tm_cname = setTimeout(function() {
        Users.search({c: {name: self.val()}});
      }, 500);
    });
  },
  initRoles: function() {
    placeholderSetup('#c_name', {back: true});

    $('#c_name').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cname);
      cur.tm_cname = setTimeout(function() {
        Users.search({c: {name: self.val()}});
      }, 500);
    });
  },
  initOperations: function() {
    placeholderSetup('#c_name', {back: true});

    $('#c_name').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cname);
      cur.tm_cname = setTimeout(function() {
        Users.search({c: {name: self.val()}});
      }, 500);
    });
  },
  initConnect: function() {
    placeholderSetup('#roclist_op_name', {back: true});

    cur.roclistScrollbar = new Scrollbar('#roclist_scroll_wrap', {
      nomargin: true,
      right: 0,
      left: 'auto',
      wheelObj: $('#roclist_cont'),
      onScroll: function(delta) {
        $('#roclist_right_col').scrollTop($('#roclist_right_col').scrollTop() - delta);
      }
    });
  },
  initLinkOrg: function(opts) {
    cur.uiOrgsDD = new Dropdown('org_id', {
      width: 360,
      label: 'Выберите организацию',
      items: opts.orgs,
      divider: true,
      change: function(value) {
        ajax.post('/users/users/linkOrg?id='+ opts.id, {org_id: value}, {
          showProgress: curBox().showProgress,
          hideProgress: curBox().hideProgress,
          onDone: function(r) {
            if (r.success) {
              Users.linkOrg(opts.id);
            } else {
              $('#link_org_error').html(r.message).show();
            }
          }
        });
      }
    });
  },
  initFeedback: function() {
    $('#c_name').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cname);
      cur.tm_cname = setTimeout(function() {
        Users.search({c: {name: self.val()}});
      }, 500);
    });
  }
};

try{stManager.done('users.js');}catch(e){}