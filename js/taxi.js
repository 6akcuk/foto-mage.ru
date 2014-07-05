var Taxi = {
  doAddOrder: function(city_id, btn) {
    var customer_phone = $.trim($('#customer_phone').val()), from_area_id = $('#from_area_id').val(),
      from_street = $.trim($('#from_street').val()), from_house = $.trim($('#from_house').val()),
      from_porch = $.trim($('#from_porch').val()), from_comment = $.trim($('#from_comment').val()),
      to_street = $.trim($('#to_street').val()), to_house = $.trim($('#to_house').val()),
      is_deferred = $('#is_deferred').val(), deferred_time = $('#deferred_time').val(),
      price = $.trim($('#price').val());

    if (!customer_phone) {
      highlight('#customer_phone');
      return false;
    }
    if (!from_street) {
      highlight('#from_street');
      return false;
    }
    if (!from_house) {
      highlight('#from_house');
      return false;
    }
    if (!to_street) {
      highlight('#to_street');
      return false;
    }
    if (!to_house) {
      highlight('#to_house');
      return false;
    }
    if (!price) {
      highlight('#price');
      return false;
    }

    var params = {
      customer_phone: customer_phone,
      from_area_id: from_area_id,
      from_street: from_street,
      from_house: from_house,
      from_porch: from_porch,
      from_comment: from_comment,
      to_street: to_street,
      to_house: to_house,
      price: price,
      is_deferred: is_deferred,
      deferred_time: (is_deferred == '1') ? deferred_time : null
    };

    ajax.post('/taxi/orders/add?city_id='+ city_id, params, {
      showProgress: function() {
        lockButton(btn);
      },
      hideProgress: function() {
        unlockButton(btn);
      },
      onDone: function(r) {
        if (r.success) {
          $('#taxi_error').html('').hide();
          $('#taxi_result').html('<div class="msg" id="taxi_msg">'+ r.message +'</div>').show();
          $('#taxi_msg').animate({backgroundColor: '#F9F6E7'});
          setTimeout(function() {
            $('#taxi_result').animate({opacity: 0}, function() {
              $('#taxi_result').html('').css({
                display: 'none',
                opacity: 1
              });
            });
          }, 1000);

          $('div.taxi_inline_order_form input[type="text"]').val('');
          $('#from_comment').val('');
        } else {
          $('#taxi_error').html(r.message).show();
        }
      },
      onFail: function(x) {
        try {
          var r = $.parseJSON(x.responseText);
        } catch(e) {}

        if (r && r.html) $('#taxi_error').html(r.html).show();
        else {
          showFastBox('Ошибка', x.responseText);
        }
      }
    });
  },

  addOrder: function() {
    var b = showBox('/taxi/orders/add', {}, {params: {width: 360}}).setButtons(getLang('global_add'), function() {
      var customer_phone = $.trim($('#customer_phone').val()), from_area_id = $('#from_area_id').val(),
        from_street = $.trim($('#from_street').val()), from_house = $.trim($('#from_house').val()),
        from_porch = $.trim($('#from_porch').val()), from_comment = $.trim($('#from_comment').val()),
        to_street = $.trim($('#to_street').val()), to_house = $.trim($('#to_house').val()),
        is_deferred = $('#is_deferred').val(), deferred_time = $('#deferred_time').val();

      if (!customer_phone) {
        highlight('#customer_phone');
        return false;
      }
      if (!from_area_id) {
        highlight($('#from_area_id').parent(), false, true);
        highlight($('#from_area_id').prev(), false, true);
        return false;
      }
      if (!from_street) {
        highlight('#from_street');
        return false;
      }
      if (!from_house) {
        highlight('#from_house');
        return false;
      }
      if (!to_street) {
        highlight('#to_street');
        return false;
      }
      if (!to_house) {
        highlight('#to_house');
        return false;
      }

      var params = {
        customer_phone: customer_phone,
        from_area_id: from_area_id,
        from_street: from_street,
        from_house: from_house,
        from_porch: from_porch,
        from_comment: from_comment,
        to_street: to_street,
        to_house: to_house,
        is_deferred: is_deferred,
        deferred_time: (is_deferred == '1') ? deferred_time : null
      };

      ajax.post('/taxi/orders/add', params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Taxi.showMsg(r.message);
          } else {
            $('#taxi_error').html(r.message).show();
          }
        },
        onFail: function(x) {
          try {
            var r = $.parseJSON(x.responseText);
          } catch(e) {}

          if (r.html) $('#taxi_error').html(r.html).show();
        }
      });
    }, getLang('global_cancel'));
  },

  editOrder: function(city_id, id) {
    var b = showBox('/taxi/orders/edit?city_id='+ city_id +'&id='+ id, {}, {params: {width: 360}}).setButtons(getLang('global_save'), function() {
      var customer_phone = $.trim($('#customer_phone').val()), from_area_id = $('#from_area_id').val(),
        from_street = $.trim($('#from_street').val()), from_house = $.trim($('#from_house').val()),
        from_porch = $.trim($('#from_porch').val()), from_comment = $.trim($('#from_comment').val()),
        to_street = $.trim($('#to_street').val()), to_house = $.trim($('#to_house').val()),
        is_deferred = $('#is_deferred').val(), deferred_time = $('#deferred_time').val();

      if (!customer_phone) {
        highlight('#customer_phone');
        return false;
      }
      if (!from_street) {
        highlight('#from_street');
        return false;
      }
      if (!from_house) {
        highlight('#from_house');
        return false;
      }
      if (!to_street) {
        highlight('#to_street');
        return false;
      }
      if (!to_house) {
        highlight('#to_house');
        return false;
      }

      var params = {
        customer_phone: customer_phone,
        from_street: from_street,
        from_house: from_house,
        from_porch: from_porch,
        from_comment: from_comment,
        to_street: to_street,
        to_house: to_house,
        is_deferred: is_deferred,
        deferred_time: (is_deferred == '1') ? deferred_time : null
      };

      ajax.post('/taxi/orders/edit?city_id='+ city_id +'&id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Taxi.showMsg(r.message);
          } else {
            $('#taxi_error').html(r.message).show();
          }
        },
        onFail: function(x) {
          try {
            var r = $.parseJSON(x.responseText);
          } catch(e) {}

          if (r.html) $('#taxi_error').html(r.html).show();
        }
      });
    }, getLang('global_cancel'));
  },

  deleteOrder: function(city_id, id, key) {
    var b = showFastBox('Удаление заявки', 'Вы действительно хотите удалить данную заявку?', getLang('global_delete'), function() {
      ajax.post('/taxi/orders/delete?city_id='+ city_id +'&id='+ id, {}, {
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

  addArea: function(city_id) {
    var b = showBox('/taxi/geography/addArea?city_id='+ city_id, {}, {params: {width: 360}}).setButtons(getLang('global_add'), function() {
      var name = $.trim($('#name').val());

      if (!name) {
        highlight('#name');
        return false;
      }

      var params = {
        name: name
      };

      ajax.post('/taxi/geography/addArea?city_id='+ city_id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Taxi.showMsg(r.message);
            nav.reload();
          } else {
            $('#taxi_error').html(r.message).show();
          }
        },
        onFail: function(x) {
          try {
            var r = $.parseJSON(x.responseText);
          } catch(e) {}

          if (r.html) $('#taxi_error').html(r.html).show();
        }
      });
    }, getLang('global_cancel'));
  },

  editArea: function(city_id, id) {
    var b = showBox('/taxi/geography/editArea?city_id='+ city_id +'&id='+ id, {}, {params: {width: 360}}).setButtons(getLang('global_save'), function() {
      var name = $.trim($('#name').val());

      if (!name) {
        highlight('#name');
        return false;
      }

      var params = {
        name: name
      };

      ajax.post('/taxi/geography/editArea?city_id='+ city_id +'&id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Taxi.showMsg(r.message);
            nav.reload();
          } else {
            $('#taxi_error').html(r.message).show();
          }
        },
        onFail: function(x) {
          try {
            var r = $.parseJSON(x.responseText);
          } catch(e) {}

          if (r.html) $('#taxi_error').html(r.html).show();
        }
      });
    }, getLang('global_cancel'));
  },

  deleteArea: function(city_id, id) {
    var b = showFastBox('Удаление района города', 'Вы действительно хотите удалить данный район?', getLang('global_delete'), function() {
      ajax.post('/taxi/geography/deleteArea?city_id='+ city_id +'&id='+ id, {}, {
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

  addAddress: function(city_id) {
    var b = showBox('/taxi/geography/addAddress?city_id='+ city_id, {}, {params: {width: 360}}).setButtons(getLang('global_add'), function() {
      var area_id = $('#area_id').val(), name = $.trim($('#name').val()), street = $.trim($('#street').val()),
        house = $.trim($('#house').val()), lat = $.trim($('#lat').val()), lon = $.trim($('#lon').val());

      if (!area_id) {
        highlight($('#area_id').parent(), false, true);
        highlight($('#area_id').prev(), false, true);
        return false;
      }
      if (!street) {
        highlight('#street');
        return false;
      }
      if (!house) {
        highlight('#house');
        return false;
      }

      var params = {
        area_id: area_id,
        street: street,
        house: house,
        name: name,
        lat: lat,
        lon: lon
      };

      ajax.post('/taxi/geography/addAddress?city_id='+ city_id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Taxi.showMsg(r.message);
            nav.reload();
          } else {
            $('#taxi_error').html(r.message).show();
          }
        },
        onFail: function(x) {
          try {
            var r = $.parseJSON(x.responseText);
          } catch(e) {}

          if (r.html) $('#taxi_error').html(r.html).show();
        }
      });
    }, getLang('global_cancel'));
  },

  editAddress: function(city_id, id) {
    var b = showBox('/taxi/geography/editAddress?city_id='+ city_id +'&id='+ id, {}, {params: {width: 360}}).setButtons(getLang('global_save'), function() {
      var area_id = $('#area_id').val(), name = $.trim($('#name').val()), street = $.trim($('#street').val()),
        house = $.trim($('#house').val()), lat = $.trim($('#lat').val()), lon = $.trim($('#lon').val());

      if (!area_id) {
        highlight($('#area_id').parent(), false, true);
        highlight($('#area_id').prev(), false, true);
        return false;
      }
      if (!street) {
        highlight('#street');
        return false;
      }
      if (!house) {
        highlight('#house');
        return false;
      }

      var params = {
        area_id: area_id,
        street: street,
        house: house,
        name: name,
        lat: lat,
        lon: lon
      };

      ajax.post('/taxi/geography/editAddress?city_id='+ city_id +'&id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Taxi.showMsg(r.message);
            nav.reload();
          } else {
            $('#taxi_error').html(r.message).show();
          }
        },
        onFail: function(x) {
          try {
            var r = $.parseJSON(x.responseText);
          } catch(e) {}

          if (r.html) $('#taxi_error').html(r.html).show();
        }
      });
    }, getLang('global_cancel'));
  },

  deleteAddress: function(city_id, id) {
    var b = showFastBox('Удаление адреса', 'Вы действительно хотите удалить данный адрес?', getLang('global_delete'), function() {
      ajax.post('/taxi/geography/deleteAddress?city_id='+ city_id +'&id='+ id, {}, {
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

  addDriver: function(city_id) {
    var b = showBox('/taxi/drivers/add?city_id='+ city_id, {}, {params: {width: 360}}).setButtons(getLang('global_add'), function() {
      var phone = $.trim($('#phone').val()), lastname = $.trim($('#lastname').val()), firstname = $.trim($('#firstname').val()),
        middlename = $.trim($('#middlename').val()), car_brand = $.trim($('#car_brand').val()), car_model = $.trim($('#car_model').val()),
        car_number = $.trim($('#car_number').val()), car_color = $.trim($('#car_color').val());

      if (!phone) {
        highlight('#phone');
        return false;
      }
      if (!lastname) {
        highlight('#lastname');
        return false;
      }
      if (!firstname) {
        highlight('#firstname');
        return false;
      }
      if (!middlename) {
        highlight('#middlename');
        return false;
      }
      if (!car_brand) {
        highlight('#car_brand');
        return false;
      }
      if (!car_model) {
        highlight('#car_model');
        return false;
      }
      if (!car_number) {
        highlight('#car_number');
        return false;
      }
      if (!car_color) {
        highlight('#car_color');
        return false;
      }

      var params = {
        phone: phone,
        lastname: lastname,
        firstname: firstname,
        middlename: middlename,
        car_brand: car_brand,
        car_model: car_model,
        car_number: car_number,
        car_color: car_color
      };

      ajax.post('/taxi/drivers/add?city_id='+ city_id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Taxi.showMsg(r.message);
            nav.reload();
          } else {
            $('#taxi_error').html(r.message).show();
          }
        },
        onFail: function(x) {
          try {
            var r = $.parseJSON(x.responseText);
          } catch(e) {}

          if (r.html) $('#taxi_error').html(r.html).show();
        }
      });
    }, getLang('global_cancel'));
  },

  editDriver: function(id) {
    var b = showBox('/taxi/drivers/edit/id/'+ id, {}, {params: {width: 360}}).setButtons(getLang('global_save'), function() {
      var phone = $.trim($('#phone').val()), lastname = $.trim($('#lastname').val()), firstname = $.trim($('#firstname').val()),
        middlename = $.trim($('#middlename').val()), car_brand = $.trim($('#car_brand').val()), car_model = $.trim($('#car_model').val()),
        car_number = $.trim($('#car_number').val()), car_color = $.trim($('#car_color').val());

      if (!phone) {
        highlight('#phone');
        return false;
      }
      if (!lastname) {
        highlight('#lastname');
        return false;
      }
      if (!firstname) {
        highlight('#firstname');
        return false;
      }
      if (!middlename) {
        highlight('#middlename');
        return false;
      }
      if (!car_brand) {
        highlight('#car_brand');
        return false;
      }
      if (!car_model) {
        highlight('#car_model');
        return false;
      }
      if (!car_number) {
        highlight('#car_number');
        return false;
      }
      if (!car_color) {
        highlight('#car_color');
        return false;
      }

      var params = {
        phone: phone,
        lastname: lastname,
        firstname: firstname,
        middlename: middlename,
        car_brand: car_brand,
        car_model: car_model,
        car_number: car_number,
        car_color: car_color
      };

      ajax.post('/taxi/drivers/edit/id/'+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Taxi.showMsg(r.message);
            nav.reload();
          } else {
            $('#taxi_error').html(r.message).show();
          }
        },
        onFail: function(x) {
          try {
            var r = $.parseJSON(x.responseText);
          } catch(e) {}

          if (r.html) $('#taxi_error').html(r.html).show();
        }
      });
    }, getLang('global_cancel'));
  },

  deleteDriver: function(id) {
    var b = showFastBox('Удаление водителя', 'Вы действительно хотите удалить данного водителя?', getLang('global_delete'), function() {
      ajax.post('/taxi/drivers/delete/id/'+ id, {}, {
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

  incrementDriverBalance: function(id) {
    var b = new Box({title: 'Пополнить баланс водителя'});
    b.content('\
    <div id="taxi_error" class="error"></div>\
    <div class="clear_fix">\
      <div class="fl_l" style="width: 100px; padding: 5px 10px 0 0; color: #666">Сумма пополнения:</div>\
      <div class="fl_l"><input type="text" id="driver_balance_value" class="text_big text" style="width: 250px" /></div>\
    </div>\
    <div class="clear_fix" style="padding-top: 5px">\
      <div class="fl_l" style="width: 100px; padding: 5px 10px 0 0; color: #666">Комментарий:</div>\
      <div class="fl_l"><input type="text" id="driver_balance_comment" class="text_big text" style="width: 250px" /></div>\
    </div>');
    b.setButtons('Пополнить', function() {
      var balance = $.trim($('#driver_balance_value').val()), comment = $.trim($('#driver_balance_comment').val());

      if (balance == '' || parseInt(balance) <= 0) {
        highlight('#driver_balance_value');
        return false;
      }

      var postdata = {
        balance: balance,
        comment: comment
      }

      ajax.post('/taxi/drivers/addBalance/id/'+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            nav.reload();
            boxPopup('Баланс успешно пополнен');
            b.hide();
          } else {
            $('#taxi_error').html('Произошла ошибка').show();
          }
        }
      });
    }, 'Отмена');
    b.show();

    $('#driver_balance_value').focus();
  },

  changeDriverRating: function(id) {
    var b = new Box({title: 'Изменить рейтинг водителя'});
    b.content('\
    <div id="taxi_error" class="error"></div>\
    <div class="clear_fix">\
      <div class="fl_l" style="width: 100px; padding: 5px 10px 0 0; color: #666">Новый рейтинг:</div>\
      <div class="fl_l"><input type="text" id="driver_rating_value" class="text_big text" style="width: 250px" /></div>\
    </div>\
    ');
    b.setButtons('Изменить', function() {
      var rating = $.trim($('#driver_rating_value').val());

      if (rating == '' || parseInt(rating) <= 0) {
        highlight('#driver_rating_value');
        return false;
      }

      var postdata = {
        rating: rating
      }

      ajax.post('/taxi/drivers/changeRating/id/'+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            nav.reload();
            boxPopup('Рейтинг успешно изменен');
            b.hide();
          } else {
            $('#taxi_error').html('Произошла ошибка').show();
          }
        },
        onFail: function(x) {
          r = $.parseJSON(x.responseText);
          $('#taxi_error').html(r.html).show();
        }
      });
    }, 'Отмена');
    b.show();

    $('#driver_rating_value').focus();
  },

  showMsg: function(msg) {
    $('#taxi_error').html('').hide();
    $('#taxi_result').html('<div class="msg" id="taxi_msg">'+ msg +'</div>').show();
    $('#taxi_msg').animate({backgroundColor: '#F9F6E7'});
  },

  search: function(obj) {
    $.extend(true, nav.objLoc, obj);

    $('div.summary_wrap .pg_pages').hide();
    $('div.summary_wrap .progress').show();

    nav.objLoc.offset = 0;
    nav.go("/"+ nav.toStr(nav.objLoc), null);
  },

  initHeartbeat: function(opts) {
    new autocomplete('customer_phone', {
      query: '/taxi/common/getFavorites?phone=%s&city_id='+ opts.city_id,
      handler: function() {

      }
    });

    new autocomplete('from_street', {
      query: '/taxi/geography/findAddress?address=%s&city_id='+ opts.city_id,
      handler: function(item) {
        $('#from_street').val(item.xdata.street);
        if (item.xdata.house) $('#from_house').val(item.xdata.house);
      }
    });

    new autocomplete('to_street', {
      query: '/taxi/geography/findAddress?address=%s&city_id='+ opts.city_id,
      handler: function(item) {
        $('#to_street').val(item.xdata.street);
        if (item.xdata.house) $('#to_house').val(item.xdata.house);
      }
    });

    cur.taxiOrdersNum = opts.offsets;

    /* Read timeouts */
    if (cur.orderTimeouts) {
      $.each(cur.orderTimeouts, function(i, t) {
        clearInterval(t);
      })
    } else {
      cur.orderTimeouts = {};
    }
    cur.orderTimecounters = {};

    $('div.taxi_order_timecounter').each(function(i) {
      var self = $(this);
      cur.orderTimecounters[self.prop('order_id')] = parseInt($(this).text());
      if (cur.orderTimecounters[self.prop('order_id')] > 0) {
        cur.orderTimeouts[self.prop('order_id')] = setInterval(function() {
          cur.orderTimecounters[self.prop('order_id')]++;
          var ms = cur.orderTimecounters[self.prop('order_id')],
            h = Math.floor(ms / 3600),
            m = Math.floor((ms % 3600) / 60),
            s = ms % 60;

          self.text(h + ':'+ ((m < 10) ? '0'+ m : m) +':'+ ((s < 10) ? '0'+ s : s));
        }, 1000);
      }
    });

    // Long-Polling
    if (cur.pushstream) cur.pushstream.disconnect();

    cur.pushstream = new PushStream({
      host: 'queue.e-bash.me',
      port: window.location.port,
      urlPrefixLongpolling: '/lp',
      modes: "longpolling",
      tagArgument: 'tag',
      timeArgument: 'time',
      useJSONP: true,
      timeout: 30000
    });
    cur.pushstream.onmessage = Taxi.ordersPoll;
    cur.pushstream.addChannel(opts.channel_key);
    cur.pushstream.connect();

    if (opts.channels.length) {
      $.each(opts.channels, function(i, ch) {
        cur.pushstream.addChannel(ch);
      });
    }

    // Channel refresh
    cur.rcInterval = setInterval(function() {
      ajax.post('/taxi/common/refreshChannel?city_id='+ opts.city_id, {}, {

      });
    }, 270000);

    cur.destroy.push(function(c) {
      c.pushstream.disconnect();
      clearInterval(c.rcInterval);
      $.each(c.orderTimeouts, function(i ,t) {
        clearInterval(t);
      });
    });
  },
  initDriver: function(opts) {
    placeholderSetup('#c_name', {back: true});

    $('#c_name').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cname);
      cur.tm_cname = setTimeout(function() {
        Taxi.search({c: {name: self.val()}});
      }, 500);
    });
  },
  initOrder: function(opts) {
    cur.taxiOrdersNum = opts.offsets;
    placeholderSetup('#c_name', {back: true});

    $('#c_name').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cname);
      cur.tm_cname = setTimeout(function() {
        Taxi.search({c: {name: self.val()}});
      }, 500);
    });

    /* Read timeouts */
    /*if (cur.orderTimeouts) {
      $.each(cur.orderTimeouts, function(i, t) {
        clearInterval(t);
      })
    } else {
      cur.orderTimeouts = {};
    }
    cur.orderTimecounters = {};

    $('div.taxi_order_timecounter').each(function(i) {
      var self = $(this);
      cur.orderTimecounters[self.prop('order_id')] = parseInt($(this).text());
      if (cur.orderTimecounters[self.prop('order_id')] > 0) {
        cur.orderTimeouts[self.prop('order_id')] = setInterval(function() {
          cur.orderTimecounters[self.prop('order_id')]++;
          var ms = cur.orderTimecounters[self.prop('order_id')],
            h = Math.floor(ms / 3600),
            m = Math.floor((ms % 3600) / 60),
            s = ms % 60;

          self.text(h + ':'+ ((m < 10) ? '0'+ m : m) +':'+ ((s < 10) ? '0'+ s : s));
        }, 1000);
      }
    });*/
  },
  initGeo: function(opts) {
    placeholderSetup('#c_name', {back: true});

    $('#c_name').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cname);
      cur.tm_cname = setTimeout(function() {
        Taxi.search({c: {name: self.val()}});
      }, 500);
    });
  },
  initAddress: function(opts) {
    placeholderSetup('#c_name', {back: true});

    $('#c_name').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cname);
      cur.tm_cname = setTimeout(function() {
        Taxi.search({c: {name: self.val()}});
      }, 500);
    });
  },
  initAreaForm: function(opts) {
    placeholderSetup('#name', {back: true});
  },
  initAddressForm: function(opts) {
    cur.uiAddressArea = new Dropdown('area_id', {
      width: 320,
      label: 'Выберите район',
      items: opts.areas
    });
  },
  initDriverForm: function(opts) {

  },
  initOrderForm: function(opts) {
    placeholderSetup('#customer_phone', {back: true});
    placeholderSetup('#from_street', {back: true});
    placeholderSetup('#from_house', {back: true});
    placeholderSetup('#from_porch', {back: true});
    placeholderSetup('#to_street', {back: true});
    placeholderSetup('#to_house', {back: true});

    cur.uiOrderDeferred = new Checkbox('is_deferred', {
      label: 'Отложенный заказ',
      change: function(val) {
        (val == '1') ? $('#taxi_order_deferred').show() : $('#taxi_order_deferred').hide();
      }
    });

    var hours = [], minutes = [];
    for(var i=0; i <= 23; i++) {
      hours.push(i);
    }
    for(var i=0; i <= 55; i+=5) {
      minutes.push((i < 10) ? '0'+ i : i);
    }

    cur.uiDeferredTime = new Calendar('deferred_time', {
      width: 192
    });

    cur.uiHours = new Dropdown('hours', {
      width: 47,
      items: hours,
      change: function(value) {
        var val = $('#deferred_time').val(), dt = val.split(' '), tm = dt[1].split(':');
        tm[0] = (value < 10) ? '0'+ value : value;
        $('#deferred_time').val(dt[0] + ' '+ tm.join(':'));
      }
    });
    cur.uiMinutes = new Dropdown('minutes', {
      width: 47,
      items: minutes,
      change: function(value) {
        var val = $('#deferred_time').val(), dt = val.split(' '), tm = dt[1].split(':');
        tm[1] = (value < 10) ? '0'+ value : value;
        $('#deferred_time').val(dt[0] + ' '+ tm.join(':'));
      }
    });

    autosizeSetup('#from_comment', {
      minHeight: 40,
      maxHeight: 120
    });
  },

  initViewBalanceHistory: function() {
    placeholderSetup('#c_amount', {back: true});
    placeholderSetup('#c_comment', {back: true});

    $('#c_amount').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_camout);
      cur.tm_camount = setTimeout(function() {
        Taxi.search({c: {amount: self.val()}});
      }, 500);
    });

    $('#c_comment').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_ccomment);
      cur.tm_ccomment = setTimeout(function() {
        Taxi.search({c: {comment: self.val()}});
      }, 500);
    });

    cur.uiStartDate = new Calendar('c_date', {
      width: 195,
      default: false,
      onSelect: function() {
        Taxi.search({c: {start_date: $('#c_date').val()}});
      }
    });

  },

  initViewOrders: function(opts) {
    placeholderSetup('#c_from_street', {back: true});
    placeholderSetup('#c_from_house', {back: true});
    placeholderSetup('#c_to_street', {back: true});
    placeholderSetup('#c_to_house', {back: true});

    $('#c_from_street').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cfromstreet);
      cur.tm_cfromstreet = setTimeout(function() {
        Taxi.search({c: {from_street: self.val()}});
      }, 500);
    });

    $('#c_from_house').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cfromhouse);
      cur.tm_cfromhouse = setTimeout(function() {
        Taxi.search({c: {from_house: self.val()}});
      }, 500);
    });

    $('#c_to_street').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_ctostreet);
      cur.tm_cfromstreet = setTimeout(function() {
        Taxi.search({c: {to_street: self.val()}});
      }, 500);
    });

    $('#c_to_house').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_ctohouse);
      cur.tm_cfromhouse = setTimeout(function() {
        Taxi.search({c: {to_house: self.val()}});
      }, 500);
    });

    cur.uiStartDate = new Calendar('c_start_date', {
      width: 195,
      default: false,
      onSelect: function() {
        Taxi.search({c: {start_date: $('#c_start_date').val()}});
      }
    });

    var status_dropdown = new Dropdown('c_status', {
      width: 204,
      label: 'Выберите статус',
      items: opts.statuses,
      change: function(val) {
        Taxi.search({c: {status: val}});
      }
    });
  },

  initViewVoteHistory: function() {
    cur.uiDate = new Calendar('c_date', {
      width: 195,
      default: false,
      onSelect: function() {
        Taxi.search({c: {start_date: $('#c_date').val()}});
      }
    });
  },

  initViewReviews: function() {
    cur.uiDate = new Calendar('c_date', {
      width: 195,
      default: false,
      onSelect: function() {
        Taxi.search({c: {start_date: $('#c_date').val()}});
      }
    });
  },

  // Long-polling handlers
  ordersPoll: function(text, id, channel) {
    if (channel.match(/taxi_orders/g)) {
      var new_order = text.split('<!>'),
        order_id = new_order[0],
        phone = new_order[1],
        driver = new_order[2],
        area = new_order[3],
        from = new_order[4],
        to = new_order[5],
        price = new_order[6],
        time = new_order[7],
        status = new_order[8],
        date = new_order[9],
        key = new_order[10];

      $('#taxi_orders_list .not_found').parent().hide();
      if (document.getElementById('taxi_order'+ order_id)) return;
      /*
      $('\
      <div id="taxi_order'+ order_id +'" class="taxi_order_row clear_fix">\
        <div class="fl_l ta_l">\
          <div class="taxi_order_customer">'+ phone +'</div>\
          <div id="taxi_order'+ order_id + '_driver" class="taxi_order_driver">'+ driver +'</div>\
          <div class="taxi_order_from_address">'+ from +'</div>\
          <div class="taxi_order_to_address">'+ to +'</div>\
        </div>\
        <div class="fl_r ta_r">\
          <div id="taxi_order'+ order_id +'_price" class="taxi_order_price">'+ price +'</div>\
          <div id="order'+ order_id +'_timecounter" class="'+ ((time.match(/[А-я]/i)) ? 'taxi_order_deferred' : 'taxi_order_timecounter') +'">\
            '+ time +'\
          </div>\
          <div id="taxi_order'+ order_id +'_status" class="taxi_order_status">'+ status +'</div>\
        </div>\
        <div class="clear taxi_order_bottom">\
          '+ date +'\
          |\
          <a>Назначить водителя</a>\
          |\
          <a onclick="Taxi.editOrder('+ order_id +')">Редактировать</a>\
          |\
          <a onclick="Taxi.deleteOrder('+ order_id +', \''+ key +'\')">Удалить</a>\
      </div>\
      ').prependTo('#taxi_orders_list');*/

      $('\
      <tr id="taxi_order'+ order_id +'" class="'+ ((cur.taxiOrdersNum % 2) ? 'even' : '') +'">\
        <td>'+ area +'</td>\
        <td>'+ from +'</td>\
        <td>'+ phone +'</td>\
        <td id="taxi_order'+ order_id +'_driver">'+ driver +'</td>\
        <td id="taxi_order'+ order_id +'_price">'+ price +'</td>\
        <td id="taxi_order'+ order_id +'_status">'+ status +'</td>\
      </tr>\
      ').prependTo('#taxi_orders_list');

      cur.taxiOrdersNum++;
      $('#taxi_orders_num').text(cur.taxiOrdersNum);
      highlight('#taxi_order'+ order_id, 'notice');

      /*if (!time.match(/[А-я]/i)) {
        var timecounter = parseInt(time);
        cur.orderTimecounters[order_id] = timecounter;
        cur.orderTimeouts[order_id] = setInterval(function() {
          timecounter++;
          var ms = timecounter,
            h = Math.floor(ms / 3600),
            m = Math.floor((ms % 3600) / 60),
            s = ms % 60;

          $('#order'+ order_id +'_timecounter').text(h + ':'+ ((m < 10) ? '0'+ m : m) +':'+ ((s < 10) ? '0'+ s : s));
        }, 1000);
      }*/

      cur.pushstream.addChannel('taxi_order'+ order_id +'_'+ key);
    } else if (channel.match(/taxi_order(\d+)/i)) {
      var update = text.split('<!>'),
        order_id = update[0],
        driver = update[1],
        dispatcher_driver = update[2],
        price = update[3],
        xstatus = update[4],
        status = update[5],
        key = update[6];

      if (xstatus == 'Canceled' || xstatus == 'Finished') {
        cur.pushstream.removeChannel('taxi_order'+ order_id +'_'+ key);
        delete cur.orderTimecounters[order_id];
        clearInterval(cur.orderTimeouts[order_id]);
        $('#taxi_order'+ order_id +'_status').text(getLang('taxi_order_'+ text.toLowerCase()));

        cur.taxiOrdersNum--;
        $('#taxi_orders_num').text(cur.taxiOrdersNum);

        if (cur.taxiOrdersNum == 0) {
          $('#taxi_orders_list .not_found').parent().show();
        }

        $('#taxi_order'+ order_id).remove();
        return;
      }

      $('#taxi_order'+ order_id +'_driver').text(dispatcher_driver);
      $('#taxi_order'+ order_id +'_price').text(price);
      $('#taxi_order'+ order_id +'_status').text(status);
    }
  }
};

try{stManager.done('taxi.js');}catch(e){}