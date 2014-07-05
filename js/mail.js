var Mail = {
  selectAll: function() {
    Mail.deselectAll();
    var list = $('#messages input[type="checkbox"]');
    if (list.length == 0) return;

    list.prop('checked', (!cur.mailAllChecked));
    cur.mailAllChecked = !cur.mailAllChecked;
    cur.mailReadedChecked = false;
    cur.mailNewChecked = false;

    (cur.mailAllChecked) ? Mail.showMailActions() : Mail.hideMailActions();
  },
  deselectAll: function() {
    $('#messages input[type="checkbox"]').attr('checked', false);
    Mail.hideMailActions();
  },
  selectReaded: function() {
    Mail.deselectAll();
    var list = $('#messages tr[read="1"] input[type="checkbox"]');
    if (list.length == 0) return;

    list.prop('checked', (!cur.mailReadedChecked));
    cur.mailReadedChecked = !cur.mailReadedChecked;
    cur.mailAllChecked = false;
    cur.mailNewChecked = false;

    (cur.mailReadedChecked) ? Mail.showMailActions() : Mail.hideMailActions();
  },
  selectNew: function() {
    Mail.deselectAll();
    var list = $('#messages tr[read!="1"] input[type="checkbox"]');
    if (list.length == 0) return;

    list.prop('checked', (!cur.mailNewChecked));
    cur.mailNewChecked = !cur.mailNewChecked;
    cur.mailReadedChecked = false;
    cur.mailAllChecked = false;

    (cur.mailNewChecked) ? Mail.showMailActions() : Mail.hideMailActions();
  },
  select: function() {
    (Mail.getSelected().length) ? Mail.showMailActions() : Mail.hideMailActions();
  },
  showMailActions: function() {
    $('#mail_search').hide();
    $('#mail_actions').show();

    if (!cur.mailSummary) cur.mailSummary = $('div.summary').text();
    $('div.summary').text('Выделено сообщений: '+ Mail.getSelected().length);
  },
  hideMailActions: function() {
    $('#mail_search').show();
    $('#mail_actions').hide();

    if (cur.mailSummary) $('div.summary').text(cur.mailSummary);
    cur.mailSummary = null;
  },

  _reset: function() {
    cur.mailNewChecked = null; cur.mailReadedChecked = null; cur.mailAllChecked = null; cur.mailSummary = null;
  },

  getSelected: function() {
    return $('#messages input[type="checkbox"]:checked');
  },
  deleteSelected: function(btn) {
    var $list = Mail.getSelected(), items = [];
    $.each($list, function(i,item) {
      items.push(parseInt($(item).val()));
    });

    ajax.post('/mail?act=delete_selected', {items: items}, {
      showProgress: function() {
        lockButton(btn);
      },
      hideProgress: function() {
        unlockButton(btn);
      },
      onDone: function(r) {
        Mail.onDeleteMsg(r);
      }
    });
  },
  deleteMsg: function(msg_id) {
    if (cur.mailDeletingMsg && cur.mailDeletingMsg[msg_id]) return;
    if (!cur.mailDeletingMsg) cur.mailDeletingMsg = {};
    cur.mailDeletingMsg[msg_id] = true;

    ajax.post('/mail?act=delete_selected', {items: [msg_id]}, {
      showProgress: function() {
        $('#mess'+ msg_id +'_del').html('<img src="/images/upload.gif" />');
      },
      hideProgress: function() {
        $('#mess'+ msg_id +'_del').html('Удалить');
      },
      onDone: function(r) {
        cur.mailDeletingMsg[msg_id] = null;
        Mail.onDeleteMsg(r);
      },
      onFail: function(xhr) {
        cur.mailDeletingMsg[msg_id] = null;
      }
    });
  },
  onDeleteMsg: function(r) {
    if (r.success) {
      if (!cur.mailCache) cur.mailCache = {};
      updatePMCounter(r.pm);
      $.each(r.backlist, function(i, item) {
        $('#mess'+ item).removeClass('new_msg').addClass('mail_del_row').attr('read', '1');
        cur.mailCache[item] = $('#mess'+ item + ' td.mail_contents').html();
        $('#mess'+ item +' td.mail_contents').html('Сообщение удалено. <a onclick="Mail.restoreMsg('+ item +')" onmousedown="event.cancelBubble = true;">Восстановить</a>');
        $('#mess'+ item +'_del').html('');
      });
    }
  },

  restoreMsg: function(msg_id) {
    if (cur.mailRestoringMsg && cur.mailRestoringMsg[msg_id]) return;
    if (!cur.mailRestoringMsg) cur.mailRestoringMsg = {};
    if (!cur.mailCacheRestore) cur.mailCacheRestore = {};
    cur.mailRestoringMsg[msg_id] = true;

    $msg = $('#mess'+ msg_id +' td.mail_contents');
    cur.mailCacheRestore[msg_id] = $msg.html();

    ajax.post('/mail?act=restore&id='+ msg_id, {}, {
      showProgress: function() {
        $msg.html('<img src="/images/upload.gif" />');
      },
      hideProgress: function() {
        $msg.html(cur.mailCacheRestore[msg_id]);
      },
      onDone: function(r) {
        if (r.success) {
          $msg.html(cur.mailCache[msg_id]);
          $('#mess'+ msg_id).removeClass('mail_del_row');
          $('#mess'+ msg_id +'_del').html('Удалить');
          cur.mailCache[msg_id] = null;
        }
        else {
          $msg.html(cur.mailCacheRestore[msg_id]);
          cur.mailCacheRestore[msg_id] = null;
        }
        cur.mailRestoringMsg[msg_id] = null;
      },
      onFail: function(xhr) {
        cur.mailCacheRestore[msg_id] = null;
        cur.mailRestoringMsg[msg_id] = null;
      }
    });
  },

  markAsReaded: function(btn) {
    var $list = Mail.getSelected(), items = [];
    $.each($list, function(i,item) {
      items.push(parseInt($(item).val()));
    });

    ajax.post('/mail?act=mark_readed', {items: items}, {
      showProgress: function() {
        lockButton(btn);
      },
      hideProgress: function() {
        unlockButton(btn);
      },
      onDone: function(r) {
        if (r.success) {
          updatePMCounter(r.pm);
          $.each(r.backlist, function(i, item) {
            $('#mess'+ item).removeClass('new_msg').attr('read', '1');
          });
        }
      }
    });
  },
  markAsNew: function(btn) {
    var $list = Mail.getSelected(), items = [];
    $.each($list, function(i,item) {
      items.push(parseInt($(item).val()));
    });

    ajax.post('/mail?act=mark_new', {items: items}, {
      showProgress: function() {
        lockButton(btn);
      },
      hideProgress: function() {
        unlockButton(btn);
      },
      onDone: function(r) {
        if (r.success) {
          updatePMCounter(r.pm);
          $.each(r.backlist, function(i, item) {
            $('#mess'+ item).addClass('new_msg').attr('read', '');
          });
        }
      }
    });
  },

  showHistory: function(dialog_id) {
    if (cur.mailHistory) return;
    cur.mailHistory = dialog_id;
    cur.mailHistoryCache = $('#mail_history_open').html();

    ajax.post('/mail?act=history&id='+ dialog_id, {}, {
      showProgress: function() {
        $('#mail_history_open').html('<img src="/images/upload.gif" />');
      },
      hideProgress: function() {
        $('#mail_history_open').html(cur.mailHistoryCache);
      },
      onDone: function(r) {
        cur.mailHistory = null;
        $('#mail_history').html(r.html);
      },
      onFail: function(xhr) {
        cur.mailHistory = null;
      }
    });
  },

  showMsgDelete: function(msg_id) {
    if (cur.mailDeletingMsg && cur.mailDeletingMsg[msg_id]) return;
    if (!cur.mailDeletingMsg) cur.mailDeletingMsg = {};
    cur.mailDeletingMsg[msg_id] = true;

    ajax.post('/mail?act=delete_selected', {items: [msg_id]}, {
      showProgress: function() {
        $('#mess'+ msg_id +'_del').html('<img src="/images/upload.gif" />');
      },
      hideProgress: function() {
        $('#mess'+ msg_id +'_del').html('удалить');
      },
      onDone: function(r) {
        cur.mailDeletingMsg[msg_id] = null;

        if (r.success) {
          if (!cur.mailCache) cur.mailCache = {};
          updatePMCounter(r.pm);
          if (r.backlist && r.backlist[0] == msg_id) {
            $('div.mail_envelope_body').hide();
            $('div.mail_envelope_attaches').hide();
            $('<div id="mess'+ msg_id +'_report" class="op_report">Сообщение удалено. <a onclick="Mail.showMsgRestore('+ msg_id +')" onmousedown="event.cancelBubble = true;">Восстановить</a></div>').insertBefore('div.mail_envelope_body');
            $('#mess'+ msg_id +'_del').html('');
          }
        }
      },
      onFail: function(xhr) {
        cur.mailDeletingMsg[msg_id] = null;
      }
    });
  },

  showMsgRestore: function(msg_id) {
    if (cur.mailRestoringMsg && cur.mailRestoringMsg[msg_id]) return;
    if (!cur.mailRestoringMsg) cur.mailRestoringMsg = {};
    if (!cur.mailCacheRestore) cur.mailCacheRestore = {};
    cur.mailRestoringMsg[msg_id] = true;

    $msg = $('#mess'+ msg_id +'_report');
    cur.mailCacheRestore[msg_id] = $msg.html();

    ajax.post('/mail?act=restore&id='+ msg_id, {}, {
      showProgress: function() {
        $msg.html('<img src="/images/upload.gif" />');
      },
      hideProgress: function() {
        $msg.html(cur.mailCacheRestore[msg_id]);
      },
      onDone: function(r) {
        if (r.success) {
          $('div.mail_envelope_body').show();
          $('div.mail_envelope_attaches').show();
          $('#mess'+ msg_id +'_del').html('удалить');
          $msg.remove();
          cur.mailCache[msg_id] = null;
        }
        else {
          $msg.html(cur.mailCacheRestore[msg_id]);
          cur.mailCacheRestore[msg_id] = null;
        }
        cur.mailRestoringMsg[msg_id] = null;
      },
      onFail: function(xhr) {
        cur.mailCacheRestore[msg_id] = null;
        cur.mailRestoringMsg[msg_id] = null;
      }
    });
  },

  send: function() {
    var attaches = {}, message = $.trim($('#mail_message').val());

    if (!message) {
      highlight('#mail_message');
      return false;
    }

    if (document.getElementById('mail_form')) {
      if ($('#mail_form').find('div.media_photo_preview').children().length) {
        attaches.photo = [];
        $('#mail_form').find('div.media_photo_preview').find('input[type="hidden"]').each(function(i, el) {
          attaches.photo.push($(this).val());
        });
      }
      if ($('#mail_form').find('div.media_document_preview').children().length) {
        attaches.document = [];
        $('#mail_form').find('div.media_document_preview').find('input[type="hidden"]').each(function(i, el) {
          attaches.document.push($(this).val());
        });
      }
    }

    var postdata = {
      dialog_id: $('input[name="dialog_id"]').val(),
      message: message,
      attaches: attaches
    };

    ajax.post('/mail?act=send', postdata, {
      showProgress: function() {
        lockButton('#send_button');
      },
      hideProgress: function() {
        unlockButton('#send_button');
      },
      onDone: function(r) {
        if (r.msg) boxPopup(r.msg);
        if (r.url) nav.go(r.url);
      }
    });
  },
  
  write: function() {
    var recipients = $('#recipient').val() ? $('#recipient').val().split(',') : [],
      attaches = {}, message = $.trim($('#mail_message').val());

    if (recipients.length == 0) {
      highlight($('#recipient').parent(), false, true);
      highlight($('#recipient').prev(), false, true);
      return false;
    }
    if (!message) {
      highlight('#mail_message');
      return false;
    }

    if (document.getElementById('mail_form')) {
      if ($('#mail_form').find('div.media_photo_preview').children().length) {
        attaches.photo = [];
        $('#mail_form').find('div.media_photo_preview').find('input[type="hidden"]').each(function(i, el) {
          attaches.photo.push($(this).val());
        });
      }
      if ($('#mail_form').find('div.media_document_preview').children().length) {
        attaches.document = [];
        $('#mail_form').find('div.media_document_preview').find('input[type="hidden"]').each(function(i, el) {
          attaches.document.push($(this).val());
        });
      }
    }

    var postdata = {
      recipients: recipients,
      message: message,
      attaches: attaches
    };

    ajax.post('/mail?act=write', postdata, {
      showProgress: function() {
        lockButton('#send_button');
      },
      hideProgress: function() {
        unlockButton('#send_button');
      },
      onDone: function(r) {
        boxPopup(r.msg);
        nav.go('/mail?act=inbox');
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

  initMailList: function() {
    placeholderSetup('#c_msg', {back: true});

    $('#c_msg').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cname);
      cur.tm_cname = setTimeout(function() {
        Mail.search({c: {msg: self.val()}});
      }, 500);
    });
  },

  initWrite: function(opts) {
    Upload.initAttaches('mail_attaches', {prefix: 'mail', allow: ['photo', 'document'], attaches: opts.attaches || null});

    cur.uiFriends = new Dropdown('recipient', {
      width: 452,
      label: 'Введите имя друга',
      big: true,
      tokens: true,
      autocomplete: true,
      items: opts.friends,
      query: '/users/friends/getFriends?query=%s'
    });

    autosizeSetup('#mail_message', {minHeight: 64, maxHeight: 160, exact: true});
  },

  initSend: function() {
    Upload.initAttaches('mail_attaches', {prefix: 'mail', allow: ['photo', 'document'], attaches: null});
    autosizeSetup('#mail_message', {minHeight: 64, maxHeight: 160, exact: true});
  }
};

try{stManager.done('mail.js');}catch(e){}