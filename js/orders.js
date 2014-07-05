var Order = {
  massMarkOrders: function(obj) {
    $('input[type="checkbox"][name*="select"]').attr('checked', ($(obj).attr('checked')) ? true : false);
  },

  massChangeStatus: function(id) {
    var ids = $('input[type="checkbox"][name*="select"]:checked'), box;

    box = Box({
      title: 'Изменение статуса заказов',
      absolute: true,
      buttons: [
        {
          title: 'Выполнить',
          onclick: function() {
            $('.input_error').remove();
            var orders = $('input[type="checkbox"][name*="select"]:checked'), ids = [];

            if (!orders.size()) {
              inputError($('#new_status'), 'Не выбраны заказы');
              return;
            }

            $.each(orders, function(i, v) {
              ids.push($(v).attr('name').match(/(\d+)/i)[1]);
            });

            if (!$('#new_status').val()) {
              inputError($('#new_status'), 'Выберите новый статус для заказов');
              return;
            }

            if (A.massChangeStatus) return;
            A.massChangeStatus = true;
            box.showProgress();

            ajax.post('/purchases/orders/massChangeStatus/id/'+ id, {ids: ids, status: $('#new_status').val(), org_comment: $('#org_comment').val()}, function(r) {
              box.hideProgress();
              box.hide();
              A.massChangeStatus = false;
              if (r.success) {
                $.each(ids, function(i, order_id) {
                  $('#order'+ order_id +'_status').html(r.status);
                });

                boxPopup(r.msg);
              }
            }, function(r) {
              box.hideProgress();
              A.massChangeStatus = false;
            });
          }
        }
      ],
      onHide: function() {
        $('#_box_hidden_status').insertBefore('div.my_orders').hide();
        $('#_box_hidden_status textarea, #_box_hidden_add input').val('');
      }
    });
    $('#_box_hidden_status').appendTo(box.bodyNode).show();
    box.show();
  },

  massSendMessage: function(id) {
    var ids = $('input[type="checkbox"][name*="select"]:checked'), box;

    box = Box({
      title: 'Отправка ЛС',
      absolute: true,
      buttons: [
        {
          title: 'Выполнить',
          onclick: function() {
            $('.input_error').remove();
            var orders = $('input[type="checkbox"][name*="select"]:checked'), ids = [];

            if (!orders.size()) {
              inputError($('#im_message').parent(), 'Не выбраны заказы');
              return;
            }

            $.each(orders, function(i, v) {
              ids.push($(v).attr('name').match(/(\d+)/i)[1]);
            });

            if (!$('#im_message').val()) {
              inputError($('#im_message').parent(), 'Напишите что-нибудь');
              return;
            }

            if (A.massSendMessage) return;
            A.massSendMessage = true;
            box.showProgress();

            ajax.post('/purchases/orders/massSendMessage/id/'+ id, {ids: ids, message: $('#im_message').val()}, function(r) {
              box.hideProgress();
              box.hide();
              A.massSendMessage = false;
              if (r.success) {
                boxPopup(r.msg);
              }
            }, function(r) {
              box.hideProgress();
              A.massSendMessage = false;
            });
          }
        }
      ],
      onHide: function() {
        $('#_box_hidden_im').insertBefore('div.my_orders').hide();
        $('#_box_hidden_im textarea, #_box_hidden_add input').val('');
      }
    });
    $('#_box_hidden_im').appendTo(box.bodyNode).show();
    box.show();
  },

  massSendSMS: function(purchase_id) {
    var orders = $('input[type="checkbox"][name*="select"]:checked'), ids = [];

    if (orders.length === 0) {
      ajex.show('Не выбраны заказы');
      return;
    }

    $.each(orders, function(i, v) {
      ids.push($(v).attr('name').match(/(\d+)/i)[1]);
    });

    showGlobalPrg();
    ajax.post('/purchases/orders/massSendSMS?purchase_id='+ purchase_id, {ids: ids}, function(r) {
      hideGlobalPrg();

      var box = new Box({
        hideButtons: true,
        bodyStyle: 'padding: 0px',
        width: 500
      });
      box.content(r.html);
      box.show();

      $('#sms_message').autosize();
    }, function() { hideGlobalPrg(); });
  },

  countSMS: function() {
    var msg = $.trim($('#sms_message').val()), u = parseInt($('#users_num').text());

    $('#letters_num').text(msg.length);
    $('#sms_num').text(Math.ceil(msg.length / 70));
    $('#total_sms_num').text(Math.ceil(msg.length / 70) * u);
  },

  sendSMS: function(purchase_id, order_ids) {
    var msg = $.trim($('#sms_message').val());

    if (!msg || msg.length === 0) {
      $('#sms_message').focus();
      return;
    }

    $('#sms_progress').show();
    ajax.post('/purchases/orders/massSendSMS?purchase_id='+ purchase_id, {ids: order_ids, message: msg}, function(r) {
      $('#sms_progress').hide();

      nav.go('/smsdeliveries?purchase_id='+ purchase_id +'&delivery_id='+ r.delivery_id, null, {box: 1});
    }, function() { $('#sms_progress').hide(); })
  },

  smsDeliveries: function(purchase_id) {
    showGlobalPrg();
    ajax.post('/purchases/orders/smsdeliveries?purchase_id='+ purchase_id, {}, function(r) {
      hideGlobalPrg();

      var box = new Box({
        hideButtons: true,
        bodyStyle: 'padding: 0px',
        width: 500
      });
      box.content(r.html);
      box.show();
    }, function() { hideGlobalPrg(); });
  }
}

try {stmgr.loaded('orders.js');}catch(e){}