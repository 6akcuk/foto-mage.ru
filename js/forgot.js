var Forgot = {
  showMsg: function(msg) {
    $('#forgot_error').html('').hide();
    $('#forgot_result').html('<div class="msg" id="forgot_msg">'+ msg +'</div>').show();
    $('#forgot_msg').animate({backgroundColor: '#F9F6E7'});
  },

  submit: function() {
    var email = $.trim($('#email').val()),
      code = $.trim($('#code').val());

    if (!A.code_sended) {
      if (!email) {
        highlight('#email');
        return false;
      }

      var postdata = {
        email: email
      };

      ajax.post('/forgot', postdata, {
        showProgress: function() {
          lockButton('#forgot_button');
        },
        hideProgress: function() {
          unlockButton('#forgot_button');
        },
        onDone: function(r) {
          if (r.success) {
            Forgot.showMsg(r.message);
            $('#code_row').slideDown();
            A.code_sended = true;
          } else {
            $('#forgot_error').html(r.message).show();
          }
        },
        onFail: function(x) {
          $('#forgot_error').html('Произошла ошибка').show();
        }
      });
    } else {
      if (!email) {
        highlight('#email');
        return false;
      }
      if (!code) {
        highlight('#code');
        return false;
      }

      nav.go('/forgot?email='+ encodeURIComponent(email) + '&code='+ encodeURIComponent(code), null);
    }
  },

  restore: function(email, code) {
    var new_password = $.trim($('#new_password').val()),
      new_password_rpt = $.trim($('#new_password_rpt').val());

    $('#forgot_error').hide();

    if (!new_password) {
      highlight('#new_password');
      return false;
    }
    if (new_password.length < 3) {
      highlight('#new_password');
      $('#forgot_error').html('Длина пароля не меньше 3-х символов').show();
      return false;
    }
    if (new_password_rpt != new_password) {
      highlight('#new_password_rpt');
      $('#forgot_error').html('Пароли не совпадают').show();
      return false;
    }

    var postdata = {
      new_password: new_password,
      new_password_rpt: new_password_rpt
    };

    ajax.post('/forgot?email='+ email + '&code='+ code, postdata, {
      showProgress: function() {
        lockButton('#forgot_button');
      },
      hideProgress: function() {
        unlockButton('#forgot_button');
      },
      onDone: function(r) {
        if (r.success) {
          Forgot.showMsg(r.message);
        } else {
          $('#forgot_error').html(r.message).show();
        }
      },
      onFail: function(x) {
        $('#forgot_error').html(x.responseText).show();
      }
    });
  },

  init: function() {
    placeholderSetup('#email', {back: true});
    placeholderSetup('#code', {back: true});
  },

  initRestore: function() {
    placeholderSetup('#new_password', {back: true});
    placeholderSetup('#new_password_rpt', {back: true});
  }
};

try{stManager.done('forgot.js');}catch(e){}