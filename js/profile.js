var Profile = {
  initFriends: function(opts) {
    placeholderSetup('#c_name', {back: true});

    var city_dropdown = new Dropdown('c[city_id]', {
      width: 160,
      label: 'Выберите город',
      items: opts.cities,
      change: function(val) {
        Profile.search({c: {city_id: val}});
      }
    });

    var role_dropdown = new Dropdown('c[role]', {
      width: 160,
      label: 'Выберите роль',
      items: opts.roles,
      change: function(val) {
        Profile.search({c: {role: val}});
      }
    });

    $('#c_name').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cname);
      cur.tm_cname = setTimeout(function() {
        Profile.search({c: {name: self.val()}});
      }, 500);
    });
  },

  search: function(obj) {
    $.extend(true, nav.objLoc, obj);

    $('div.summary_wrap .pg_pages').hide();
    $('div.summary_wrap .progress').show();

    nav.objLoc.offset = 0;
    nav.go("/"+ nav.toStr(nav.objLoc), null);
  },

  showKeepSubscriber: function() {
      $cont = $($cont);
      $cont.replaceWith('<a onclick="return Profile.keepSubscriber(this, '+ friend_id +')">Оставить в подписчиках</a>');
  },

  addFriend: function(cont, friend_id) {
    ajax.post('/friends/add', {friend_id: friend_id}, {
      showProgress: function() {
        lockButton(cont);
      },
      hideProgress: function() {
        unlockButton(cont);
      },
      onDone: function(r) {
        $(cont).parent().parent().html('<div class="social-status">'+ r.message + '</div>');
      }
    });
  },

  deleteFriend: function(cont, friend_id) {
    ajax.post('/friends/delete', {friend_id: friend_id}, {
      showProgress: function() {
        lockButton(cont);
      },
      hideProgress: function() {
        unlockButton(cont);
      },
      onDone: function(r) {
        $('#people'+ friend_id).addClass('report').html(r.message);
      }
    });
  },

  keepSubscriber: function(cont, friend_id) {
    ajax.post('/friends/keep', {friend_id: friend_id}, {
      showProgress: function() {
        lockButton(cont);
      },
      hideProgress: function() {
        unlockButton(cont);
      },
      onDone: function(r) {
        $('#people'+ friend_id).addClass('report').html(r.message);
      }
    });
  },

  showStatusEditor: function(cont) {
    var $w = $('#profile-status-editor'), st = $.trim($('#profile-status').text());
    $w.show().css({
      top: $(cont).offset().top - $('#content').offset().top - 12 - 8,
      left: $(cont).offset().left - $('#content').offset().left - 12 - 8
    });
    $w.click(function(event) {
      event.stopPropagation();
    });
    $w.find('input').focus().val((st != 'Изменить статус') ? st : '');

    setTimeout(function() {
      $('body').one('click', function() {
        $w.hide();
      });
    }, 1);
  },

  saveStatus: function() {
    var status = $.trim($('#profile-status-editor input[type="text"]').val());
    ajax.post('/users/profiles/status', {status: status}, function(r) {
      $('body').click();
      if (status == '') status = 'Изменить статус';
      $('#profile-status').text(status);
    });
  },

  changePassword: function(btn) {
    var old_password = $.trim($('#old_password').val()), new_password = $.trim($('#new_password').val()),
      rpt_password = $.trim($('#rpt_password').val());

    if (!old_password) {
      highlight('#old_password');
      return false;
    }
    if (!new_password) {
      highlight('#new_password');
      return false;
    }
    if (!rpt_password) {
      highlight('#rpt_password');
      return false;
    }

    var postdata = {
      act: 'changepwd',
      old_password: old_password,
      new_password: new_password,
      rpt_password: rpt_password
    };

    ajax.post('/settings', postdata, {
      showProgress: function() {
        lockButton(btn);
      },
      hideProgress: function() {
        unlockButton(btn);
      },
      onDone: function(r) {
        if (r.success) {
          $('#profile_settings_error').html('').hide();
          $('#profile_settings_result').html('<div class="msg" id="profile_settings_msg">'+ r.message +'</div>').show();
          $('#profile_settings_msg').animate({backgroundColor: '#F9F6E7'});
        } else {
          $('#profile_settings_error').html(r.message).show();
        }
      }
    });
  },

  saveEmail: function(btn) {
    var new_mail = $.trim($('#new_mail').val());

    if (!new_mail) {
      highlight('#new_mail');
      return false;
    }

    var postdata = {
      act: 'changeemail',
      new_mail: new_mail
    };

    ajax.post('/settings', postdata, {
      showProgress: function() {
        lockButton(btn);
      },
      hideProgress: function() {
        unlockButton(btn);
      },
      onDone: function(r) {
        if (r.success) {
          $('#profile_settings_error').html('').hide();
          $('#profile_settings_result').html('<div class="msg" id="profile_settings_msg">'+ r.message +'</div>').show();
          $('#profile_settings_msg').animate({backgroundColor: '#F9F6E7'});
        } else {
          $('#profile_settings_error').html(r.message).show();
        }
      }
    });
  },

  changePhone: function() {
    showGlobalPrg();

    ajax.post('/settings', {act: 'changephone'}, function(r) {
      hideGlobalPrg();

      var box = new Box({
        hideButtons: true,
        bodyStyle: 'padding: 0px',
        width: 500
      });
      box.content(r.html);
      box.show();
    });
  },

  getPhoneCode: function() {
    var $form = $('#changephoneform');

    FormMgr.submit($form, 'left', function(r) {
      if (r.msg) boxPopup(r.msg);
      if (r.step == 1) {
        $('#change_phone_code').slideDown();
        $('#change_phone_button').html('Сменить номер');
        $form.find('input[name="eid"]').val(r.eid);
      }
      else if (r.step == 2) {
        curBox().hide();
      }
    }, function(r) {

    });
  },

  emailNotify: function() {
    FormMgr.submit('#emailnotifyform', 'left', function(r) {
      if (r.msg) boxPopup(r.msg);
    }, function(r) {

    });
  },

  saveEdit: function(btn) {
    var firstname = $.trim($('#firstname').val()), lastname = $.trim($('#lastname').val()),
      gender = $('#gender').val(), city_id = $('#city_id').val(), about = $.trim($('#about').val()),
      photo = $('#photo').val();

    if (!firstname) {
      highlight('#firstname');
      return false;
    }
    if (!lastname) {
      highlight('#lastname');
      return false;
    }

    var postdata = {
      firstname: firstname,
      lastname: lastname,
      gender: gender,
      city_id: city_id,
      about: about,
      photo: photo
    };

    ajax.post('/edit', postdata, {
      showProgress: function() {
        lockButton(btn);
      },
      hideProgress: function() {
        unlockButton(btn);
      },
      onDone: function(r) {
        if (r.success) {
          $('#profile_edit_error').html('').hide();
          $('#profile_edit_result').html('<div class="msg" id="profile_edit_msg">'+ r.message +'</div>').show();
          $('#profile_edit_msg').animate({backgroundColor: '#F9F6E7'});
        } else {
          $('#profile_edit_error').html(r.message).show();
        }
      }
    });
  },

  initEditForm: function(opts) {
    Upload.initSinglePhoto('photo', {
      size: 'b',
      action: opts.uploadAction
    });

    cur.uiGender = new Dropdown('gender', {
      width: 232,
      big: true,
      items: [['Male','Мужской'],['Female','Женский']]
    });

    cur.uiCities = new Dropdown('city_id', {
      width: 232,
      big: true,
      items: opts.cities
    });

    autosizeSetup('#about', {minHeight: 32, maxHeight: 96, exact: true});
  }
};

try {stManager.done('profile.js');}catch(e){}