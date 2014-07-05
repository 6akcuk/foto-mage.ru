var Org = {
  showMsg: function(msg) {
    $('#org_error').html('').hide();
    $('#org_result').html('<div class="msg" id="org_msg">'+ msg +'</div>').show();
    $('#org_msg').animate({backgroundColor: '#F9F6E7'});
  },
  add: function() {
    var b = showBox('/orgs/orgs/add', {}, {params: {}}).setButtons(getLang('global_add'), function() {
      var org_type_id = $.trim($('#org_type_id').val()), city_id = $.trim($('#city_id').val()),
        name = $.trim($('#name').val()), address = $.trim($('#address').val()), worktimes = $.trim($('#worktimes').val()),
        shortstory = $.trim($('#shortstory').val()), photo = $('#photo').val(), phone = $('#phone').val();

      if (!org_type_id) {
        highlight($('#org_type_id').parent(), false, true);
        highlight($('#org_type_id').prev(), false, true);
        return false;
      }
      if (!city_id) {
        highlight($('#city_id').parent(), false, true);
        highlight($('#city_id').prev(), false, true);
        return false;
      }
      if (!name) {
        highlight('#name');
        return false;
      }
      if (!address) {
        highlight('#address');
        return false;
      }
      if (!phone) {
        highlight('#phone');
        return false;
      }
      if (!worktimes) {
        highlight('#worktimes');
        return false;
      }
      if (!shortstory) {
        highlight('#shortstory');
        return false;
      }

      var params = {
        org_type_id: org_type_id,
        city_id: city_id,
        name: name,
        address: address,
        phone: phone,
        worktimes: worktimes,
        shortstory: shortstory,
        photo: photo
      };

      ajax.post('/orgs/orgs/add', params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Org.showMsg(r.message);
            $('#name, #address, #phone, #worktimes, #shortstory').val('');
            cur.uiOrgCities.clear();
            cur.uiOrgTypes.clear();
          }
          else {
            $('#org_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  edit: function(id) {
    var b = showBox('/orgs/orgs/edit?id='+ id, {}, {params: {}}).setButtons(getLang('global_save'), function() {
      var org_type_id = $.trim($('#org_type_id').val()), city_id = $.trim($('#city_id').val()),
        name = $.trim($('#name').val()), address = $.trim($('#address').val()), worktimes = $.trim($('#worktimes').val()),
        shortstory = $.trim($('#shortstory').val()), photo = $('#photo').val(), phone = $('#phone').val();

      if (!org_type_id) {
        highlight($('#org_type_id').parent(), false, true);
        highlight($('#org_type_id').prev(), false, true);
        return false;
      }
      if (!city_id) {
        highlight($('#city_id').parent(), false, true);
        highlight($('#city_id').prev(), false, true);
        return false;
      }
      if (!name) {
        highlight('#name');
        return false;
      }
      if (!address) {
        highlight('#address');
        return false;
      }
      if (!phone) {
        highlight('#phone');
        return false;
      }
      if (!worktimes) {
        highlight('#worktimes');
        return false;
      }
      if (!shortstory) {
        highlight('#shortstory');
        return false;
      }

      var params = {
        org_type_id: org_type_id,
        city_id: city_id,
        name: name,
        address: address,
        phone: phone,
        worktimes: worktimes,
        shortstory: shortstory,
        photo: photo
      };

      ajax.post('/orgs/orgs/edit?id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Org.showMsg(r.message);
          }
          else {
            $('#org_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  delete: function(id) {
    var b = showFastBox('Удаление организации', 'Вы действительно хотите удалить организацию?', getLang('global_delete'), function() {
      ajax.post('/orgs/orgs/delete?id='+ id, {}, {
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
  roomShowMsg: function(msg) {
    $('#room_error').html('').hide();
    $('#room_result').html('<div class="msg" id="room_msg">'+ msg +'</div>').show();
    $('#room_msg').animate({backgroundColor: '#F9F6E7'});
  },
  addRoom: function(id) {
    var b = showBox('/orgs/owner/addroom?id='+ id, {}, {}).setButtons(getLang('global_add'), function() {
      var name = $.trim($('#name').val());

      if (!name) {
        highlight('#name');
        return false;
      }

      var params = {
        name: name
      };

      ajax.post('/orgs/owner/addroom?id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Org.roomShowMsg(r.message);
            nav.reload();
          }
          else {
            $('#room_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  editRooms: function(id) {
    var b = showBox('/orgs/owner/editrooms?id='+ id, {}, {params: {width: 467, bodyStyle: 'padding: 0px;'}}).controlsText('<a onclick="Org.addRoom('+ id +')">Добавить помещение</a>');
  },
  editRoom: function(id) {
    var b = showBox('/orgs/owner/editroom?id='+ id, {}, {}).setButtons(getLang('global_save'), function() {
      var name = $.trim($('#name').val());

      if (!name) {
        highlight('#name');
        return false;
      }

      var params = {
        name: name
      };

      ajax.post('/orgs/owner/editroom?id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Org.roomShowMsg(r.message);
            nav.reload();
          }
          else {
            $('#room_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  deleteRoom: function(id) {
    ajax.post('/orgs/owner/deleteroom?id='+ id, {}, {
      showProgress: curBox().showProgress,
      hideProgress: curBox().hideProgress,
      onDone: function(r) {
        if (r.success) {
          $('#room'+ id).remove();
          nav.reload();
        }
      }
    });
  },
  eventShowMsg: function(msg) {
    $('#event_error').html('').hide();
    $('#event_result').html('<div class="msg" id="event_msg">'+ msg +'</div>').show();
    $('#event_msg').animate({backgroundColor: '#F9F6E7'});
  },
  addEvent: function(id) {
    var b = showBox('/orgs/events/add?id='+ id, {}, {}).setButtons(getLang('global_add'), function() {
      var event_type_id = $('#event_type_id').val(), room_id = $('#room_id').val(), title = $.trim($('#title').val()),
        shortstory = $.trim($('#shortstory').val()), start_time = $('#start_time').val(), end_time = $('#end_time').val(),
        price = $.trim($('#price').val()), ch_st = $('#ch_st').val(), ch_et = $('#ch_et').val(), photo = $('#photo').val(),
        weekly = [], _dow = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

      if (!event_type_id) {
        highlight($('#event_type_id').parent(), false, true);
        highlight($('#event_type_id').prev(), false, true);
        return false;
      }
      if (!title) {
        highlight('#title');
        return false;
      }
      if (!shortstory) {
        highlight('#shortstory');
        return false;
      }

      if ($('#weekly').val() == '1') {
        $('#event_weekly_bar a').each(function(i) {
          if ($(this).hasClass('selected')) weekly.push(_dow[i]);
        });
      }

      var params = {
        event_type_id: event_type_id,
        room_id : room_id,
        title: title,
        shortstory: shortstory,
        price: price,
        photo: photo,
        weekly: weekly.join(',')
      };
      if (ch_st == 1) params.start_time = start_time;
      if (ch_et == 1) params.end_time = end_time;

      ajax.post('/orgs/events/add?id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Org.eventShowMsg(r.message);
            nav.reload();
          } else {
            $('#event_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  editEvent: function(id) {
    var b = showBox('/orgs/events/edit?id='+ id, {}, {}).setButtons(getLang('global_save'), function() {
      var event_type_id = $('#event_type_id').val(), room_id = $('#room_id').val(), title = $.trim($('#title').val()),
        shortstory = $.trim($('#shortstory').val()), start_time = $('#start_time').val(), end_time = $('#end_time').val(),
        price = $.trim($('#price').val()), ch_st = $('#ch_st').val(), ch_et = $('#ch_et').val(), photo = $('#photo').val(),
        weekly = [], _dow = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

      if (!event_type_id) {
        highlight($('#event_type_id').parent(), false, true);
        highlight($('#event_type_id').prev(), false, true);
        return false;
      }
      if (!title) {
        highlight('#title');
        return false;
      }
      if (!shortstory) {
        highlight('#shortstory');
        return false;
      }

      if ($('#weekly').val() == '1') {
        $('#event_weekly_bar a').each(function(i) {
          if ($(this).hasClass('selected')) weekly.push(_dow[i]);
        });
      }

      var params = {
        event_type_id: event_type_id,
        room_id : room_id,
        title: title,
        shortstory: shortstory,
        price: price,
        photo: photo,
        weekly: weekly.join(',')
      };
      if (ch_st == 1) params.start_time = start_time;
      if (ch_et == 1) params.end_time = end_time;

      ajax.post('/orgs/events/edit?id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Org.eventShowMsg(r.message);
          } else {
            $('#event_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  deleteEvent: function(id) {
    var b = showFastBox('Удаление события', 'Вы действительно хотите удалить событие?', getLang('global_delete'), function() {
      ajax.post('/orgs/events/delete?id='+ id, {}, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          $('#event'+ id).replaceWith('<div class="msg">'+ r.message +'</div>');
          b.hide();
        }
      });
    }, getLang('global_cancel'));
  },
  showModules: function(id) {
    var b = showBox('/orgs/owner/modules?id='+ id, {}, {}).setButtons(getLang('global_save'), function() {
      var enable_delivery = $('#enable_delivery').val(), enable_discount = $('#enable_discount').val(),
        enable_market = $('#enable_market').val();

      var params = {
        enable_delivery: enable_delivery,
        enable_discount: enable_discount,
        enable_market: enable_market
      };

      ajax.post('/orgs/owner/modules?id='+ id, params, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Org.showMsg(r.message);
            nav.reload();
          } else {
            $('#org_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },

  import: function() {
    var b = showBox('/orgs/orgs/importBox', {}, {params: {}}).setButtons(getLang('global_add'), function() {
      var source = $.trim($('#source').val()), city_id = $.trim($('#city_id').val()),
        filedata = $.trim($('#filedata').val());

      if (!city_id) {
        highlight($('#city_id').parent(), false, true);
        highlight($('#city_id').prev(), false, true);
        return false;
      }
      if (!source) {
        highlight($('#source').parent(), false, true);
        highlight($('#source').prev(), false, true);
        return false;
      }
      if (!filedata) {
        return false;
      }

      b.lockButton(0);
      $('#org_form').submit();
    }, getLang('global_cancel'));
  },

  importRun: function(el, id) {
    ajax.post('/orgs/orgs/importRun?id='+ id, {}, {
      dataType: 'text',
      showProgress: function() {
        lockButton($(el).parent());
        $('#imp'+ id +'_status').text('Обрабатывается');
      },
      onDone: function(r) {
        $('#imp'+ id +'_status').text('Выполнен');
        unlockButton($(el).parent());
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
      width: 220,
      label: 'Выберите город',
      big: true,
      items: opts.cities,
      change: function(val) {
        Org.search({c: {city_id: val}});
      }
    });

    var org_type_dropdown = new Dropdown('c[org_type_id]', {
      width: 220,
      label: 'Выберите категорию организации',
      big: true,
      tokens: true,
      autocomplete: true,
      items: opts.types,
      query: '/orgs/default/searchType?query=%s',
      change: function(val) {
        Org.search({c: {org_type_id: val}});
      }
    });

    $('#c_name').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cname);
      cur.tm_cname = setTimeout(function() {
        Org.search({c: {name: self.val()}});
      }, 500);
    });
  },
  initOrgForm: function(opts) {
    Upload.initSinglePhoto('photo', {
      size: 'b',
      action: opts.uploadAction,
      selector_html: '<a class="org_upload_photo">Прикрепить фотографию</a>'
    });

    cur.uiOrgCities = new Dropdown('city_id', {
      width: 250,
      label: 'Выберите город',
      items: opts.cities
    });
    cur.uiOrgTypes = new Dropdown('org_type_id', {
      width: 250,
      label: 'Выберите категорию организации',
      items: opts.orgTypes,
      query: '/orgs/default/searchType?query=%s',
      autocomplete: true,
      tokens: true
    });

    autosizeSetup('#shortstory', {minHeight: 32, maxHeight: 96, exact: true});
  },
  initImportForm: function(opts) {
    cur.uiImpCities = new Dropdown('city_id', {
      width: 300,
      label: 'Выберите город',
      items: opts.cities
    });
    cur.uiImpSource = new Dropdown('source', {
      width: 300,
      label: 'Выберите источник',
      items: [['2gis.csv','2GIS CSV']]
    });
  },
  initEvents: function() {
    placeholderSetup('#c_name', {back: true});
  },
  initAddEvent: function(opts) {
    autosizeSetup('#shortstory', {minHeight: 48});
    Upload.initSinglePhoto('photo', {
      action: opts.uploadAction,
      size: 'a'
    });

    cur.uiEventTypes = new Dropdown('event_type_id', {
      width: 246,
      label: 'Выберите тип события',
      items: opts.eventTypes
    });

    if (opts.rooms.length) {
      cur.uiRooms = new Dropdown('room_id', {
        width: 246,
        label: 'Выберите помещение',
        items: opts.rooms
      });
    }

    var hours = [], minutes = [];
    for(var i=0; i <= 23; i++) {
      hours.push(i);
    }
    for(var i=0; i <= 55; i+=5) {
      minutes.push((i < 10) ? '0'+ i : i);
    }

    cur.uiStartTime = new Calendar('start_time', {
      width: 120
    });
    cur.uiEndTime = new Calendar('end_time', {
      width: 120
    });

    cur.uiHours = new Dropdown('hours', {
      width: 47,
      items: hours,
      change: function(value) {
        var val = $('#start_time').val(), dt = val.split(' '), tm = dt[1].split(':');
        tm[0] = (value < 10) ? '0'+ value : value;
        $('#start_time').val(dt[0] + ' '+ tm.join(':'));
      }
    });
    cur.uiMinutes = new Dropdown('minutes', {
      width: 47,
      items: minutes,
      change: function(value) {
        var val = $('#start_time').val(), dt = val.split(' '), tm = dt[1].split(':');
        tm[1] = (value < 10) ? '0'+ value : value;
        $('#start_time').val(dt[0] + ' '+ tm.join(':'));
      }
    });
    cur.uiHoursEnd = new Dropdown('hours_end', {
      width: 47,
      items: hours,
      change: function(value) {
        var val = $('#end_time').val(), dt = val.split(' '), tm = dt[1].split(':');
        tm[0] = (value < 10) ? '0'+ value : value;
        $('#end_time').val(dt[0] + ' '+ tm.join(':'));
      }
    });
    cur.uiMinutesEnd = new Dropdown('minutes_end', {
      width: 47,
      items: minutes,
      change: function(value) {
        var val = $('#end_time').val(), dt = val.split(' '), tm = dt[1].split(':');
        tm[1] = (value < 10) ? '0'+ value : value;
        $('#end_time').val(dt[0] + ' '+ tm.join(':'));
      }
    });

    cur.uiWeekly = new Checkbox('weekly', {
      label: 'Событие повторяется еженедельно',
      change: function(val) {
        (val == '1') ? $('#event_weekly_bar').show() : $('#event_weekly_bar').hide();
      }
    });
  }
}

try{stManager.done('orgs.js');}catch(e){}