var Support = {
  answer: function(id) {
    var b = showFastBox({
      title: 'Ответить пользователю'
    }, '<div class="support_form_header">Сообщение:</div><div class="support_form_param">' +
        '<textarea id="support_answer" class="text" style="width: 370px"></textarea>' +
      '</div>', 'Ответить', function() {
      var params = {
        msg: $('#support_answer').val()
      };

      ajax.post('/unify/support/answer/id/'+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          Support.updateStatus(id, 'Processed');
          b.hide();
          boxPopup(r.message);
        }
      });
    }, 'Отмена');

    autosizeSetup('#support_answer', {minHeight: 64, maxHeight: 164, exact: true});

    $('#support_answer').val('Здравствуйте, '+ $('#support_author_'+ id).text() + '!\n\n').focus()
      .val($('#support_answer').val() + '\n\nНе нужно отвечать на данное сообщение. Воспользуйтесь формой поддержки на сайте http://e-bash.me/support\n\nС уважением, команда поддержки!');
  },

  recharge: function(id) {
    if (!$('#support_recharge_'+ id).data('ddmenu')) {
      $('#support_recharge_'+ id).data('ddmenu', new DDMenu('#support_recharge_'+ id, [['Sended','Отправлено'],['Received','Получено'],['Processed','Обработано']], {
        click: function(val) {
          var params = {id: id, status: val};

          ajax.post('/unify/support/recharge/id/'+ id, params, {
            showProgress: function() {
              $('div.summary_wrap .progress').show();
            },
            hideProgress: function() {
              $('div.summary_wrap .progress').hide();
            },
            onDone: function() {
              Support.updateStatus(id, val);
            }
          });
        }
      }));

      $('#support_recharge_'+ id).data('ddmenu').show();
    }
  },

  updateStatus: function(id, status) {
    $('#support_status_'+ id).text(status);
  },

  search: function(obj) {
    $.extend(true, nav.objLoc, obj);

    $('div.summary_wrap .pg_pages').hide();
    $('div.summary_wrap .progress').show();

    nav.objLoc.offset = 0;
    nav.go("/"+ nav.toStr(nav.objLoc), null);
  },

  init: function(opts) {
    placeholderSetup('#c_q', {back: true});

    var status_dropdown = new Dropdown('c[status]', {
      width: 150,
      label: 'Выберите статус',
      items: [['Sended','Отправлено'],['Received','Получено'],['Processed','Обработано']],
      change: function(val) {
        Support.search({c: {status: val}});
      }
    });

    $('#c_q').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cq);
      cur.tm_cq = setTimeout(function() {
        Support.search({c: {q: self.val()}});
      }, 500);
    });
  }
};

try{stManager.done('support.js');}catch(e){}