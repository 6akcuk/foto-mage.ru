/**
 * Created with JetBrains PhpStorm.
 * User: denis
 * Date: 21.11.12
 * Time: 17:39
 * To change this template use File | Settings | File Templates.
 */
var Register = {
  showMsg: function(msg) {
    $('#register_error').html('').hide();
    $('#register_result').html('<div class="msg" id="register_msg">'+ msg +'</div>').show();
    $('#register_msg').animate({backgroundColor: '#F9F6E7'});
  },

  submit: function() {
    var email = $.trim($('#email').val()), password = $.trim($('#password').val()),
      firstname = $.trim($('#firstname').val()), lastname = $.trim($('#lastname').val()),
      gender = parseInt($('#gender').val()), city_id = parseInt($('#city_id').val());

    if (!email) {
      highlight('#email');
      return false;
    }
    if (!password) {
      highlight('#password');
      return false;
    }
    if (!firstname) {
      highlight('#firstname');
      return false;
    }
    if (!lastname) {
      highlight('#lastname');
      return false;
    }
    if (!gender) {
      highlight($('#gender').parent(), false, true);
      highlight($('#gender').prev(), false, true);
      return false;
    }
    if (!city_id) {
      highlight($('#city_id').parent(), false, true);
      highlight($('#city_id').prev(), false, true);
      return false;
    }

    var postdata = {
      email: email,
      password: password,
      firstname: firstname,
      lastname: lastname,
      gender: gender,
      city_id: city_id
    };

    ajax.post('/register', postdata, {
      showProgress: function() {
        lockButton('#register_button');
      },
      hideProgress: function() {
        unlockButton('#register_button');
      },
      onDone: function(r) {
        if (r.success) {
          Register.showMsg(r.message);
          nav.go('/id'+ r.id, null);
        } else {
          $('#register_error').html(r.message).show();
        }
      },
      onFail: function(x) {
        $('#register_error').html('Произошла ошибка').show();
      }
    });
  },

  init: function(opts) {
    placeholderSetup('#email', {back: true});
    placeholderSetup('#password', {back: true});
    placeholderSetup('#firstname', {back: true});
    placeholderSetup('#lastname', {back: true});

    var city_dropdown = new Dropdown('city_id', {
      width: 154,
      label: 'Выберите город',
      items: opts.cities
    });

    var gender_dropdown = new Dropdown('gender', {
      width: 154,
      label: 'Пол',
      items: [['Male','Мужской'],['Female','Женский']]
    });
  }
};

try{stManager.done('register.js');}catch(e){}