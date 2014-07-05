var Discuss = {
  createForum: function() {
    var title = $.trim($('#bnt_title').val()),
      description = $.trim($('#bnt_description').val()),
      parent_id = $.trim($('#bnt_parent').val()),
      icon = $.trim($('#icon').val()),
      city = parseInt($('#bnt_city').val()),
      rights = parseInt($('#bnt_rights').val());

    if (!title) {
      $('#bnt_title').focus();
      return false;
    }

    if (A.discussForumCreate) return;
    A.discussForumCreate = true;

    $('#bnt_progress').show();
    ajax.post('/discuss?act=create', {title: title, description: description, parent_id: parent_id, icon: icon, city: city, rights: rights}, function(r) {
      $('#bnt_progress').hide();
      A.discussForumCreate = false;

      boxPopup(r.msg);
      nav.go(r.url);
    }, function(xhr) {
      $('#bnt_progress').hide();
      A.discussForumCreate = false;
    });
  },

  doEditForum: function(id) {
    var title = $.trim($('#bnt_title').val()),
      description = $.trim($('#bnt_description').val()),
      parent_id = $.trim($('#bnt_parent').val()),
      icon = $.trim($('#icon').val()),
      city = parseInt($('#bnt_city').val()),
      rights = parseInt($('#bnt_rights').val());

    if (!title) {
      $('#bnt_title').focus();
      return false;
    }

    if (A.discussForumEdit) return;
    A.discussForumEdit = true;

    $('#bnt_progress').show();
    ajax.post('/discuss?act=edit&id='+ id, {title: title, description: description, parent_id: parent_id, icon: icon, city: city, rights: rights}, function(r) {
      $('#bnt_progress').hide();
      A.discussForumEdit = false;

      boxPopup(r.msg);
      nav.go(r.url);
    }, function(xhr) {
      $('#bnt_progress').hide();
      A.discussForumEdit = false;
    });
  },

  deleteForum: function(id) {
    showConfirmBox('Вы уверене, что хотите удалить данный форум? Данное действие удалит все темы и сообщения, его нельзя будет отменить', 'Да', function() {
      Discuss.doDeleteForum(id);
    }, 'Нет', function() {
      curBox().hide();
    });
  },

  doDeleteForum: function(id) {
    ajax.post('/discuss?act=delete&id='+ id, {}, function(r) {
      curBox().hide();
      boxPopup(r.msg);
      nav.go(r.url);
    }, function(r) {

    });
  },

  createTheme: function(id) {
    var title = $.trim($('#bnt_title').val()),
      post = $.trim($('#bnt_post').val()),
      attaches = [];

    if (!title) {
      $('#bnt_title').focus();
      return false;
    }

    if (!post) {
      $('#bnt_post').focus();
      return false;
    }

    if (A.discussThemeCreate) return;
    A.discussThemeCreate = true;

    $('input[type="hidden"][name*=attach]').each(function(i, item) {
      attaches.push($(item).val());
    });

    $('#bnt_progress').show();
    ajax.post('/discuss'+ id +'?act=create', {title: title, post: post, attaches: attaches}, function(r) {
      $('#bnt_progress').hide();
      A.discussThemeCreate = false;

      nav.go(r.url);
    }, function(xhr) {
      $('#bnt_progress').hide();
      A.discussThemeCreate = false;
    });
  },

  editTheme: function(theme_id) {
    cur.editedTheme = theme_id;

    var box = new Box({
      title: 'Редактирование темы',
      buttons: [{title: 'Сохранить', onclick: Discuss.saveTheme}]
    });
    box.content('<div class="dce_label left">Новое название темы:</div><div class="left"><input type="text" id="dce_title" onkeyup="if (event.keyCode == 10 || event.keyCode == 13) Discuss.saveTheme()" /></div><div class="clear"></div>');
    box.show();

    $('#dce_title').focus().val($('#discuss_title').text());
  },

  saveTheme: function() {
    curBox().showProgress();

    ajax.post('/discuss/theme/save?theme_id='+ cur.editedTheme, {title: $('#dce_title').val()}, function(r) {
      curBox().hide();
      $('#discuss_title').text(r.title);
    }, function() {
      curBox().hideProgress();
    });
  },

  fixTheme: function(theme_id) {
    $('#edit_theme_progress').show().next().hide();
    ajax.post('/discuss/theme/fix?theme_id='+ theme_id, {}, function(r) {
      nav.go(r.url);
    });
  },
  unfixTheme: function(theme_id) {
    $('#edit_theme_progress').show().next().hide();
    ajax.post('/discuss/theme/unfix?theme_id='+ theme_id, {}, function(r) {
      nav.go(r.url);
    });
  },

  closeTheme: function(theme_id) {
    $('#edit_theme_progress').show().next().hide();
    ajax.post('/discuss/theme/close?theme_id='+ theme_id, {}, function(r) {
      nav.go(r.url);
    });
  },
  openTheme: function(theme_id) {
    $('#edit_theme_progress').show().next().hide();
    ajax.post('/discuss/theme/open?theme_id='+ theme_id, {}, function(r) {
      nav.go(r.url);
    });
  },

  deleteTheme: function(theme_id) {
    cur.deletingTheme = theme_id;

    var box = new Box({
      title: 'Редактирование темы',
      buttons: [{title: 'Удалить', onclick: Discuss.doDeleteTheme}]
    });
    box.content('Вы уверены, что хотите удалить эту тему?');
    box.show();
  },
  doDeleteTheme: function() {
    curBox().showProgress();

    ajax.post('/discuss/theme/delete?theme_id='+ cur.deletingTheme, {}, function(r) {
      curBox().hide();

      boxPopup(r.msg);
      nav.go(r.url);
    }, function() {
      curBox().hideProgress();
    });
  },

  replyPost: function(post_id) {
    A.discussPostFixed = true;
    Discuss.scroll();
    $('#discuss_text').focus().val($('#discuss_text').val() + '[post'+ post_id +'|'+ $('#discuss_post'+ post_id + ' a.discuss_post_author').attr('data-name') +'], ').keyup();
  },

  fixPost: function() {
    A.discussPostTmHeight = setInterval(Discuss.ctrlPost, 100);
    $(window).bind('scroll', Discuss.scroll);
  },
  ctrlPost: function() {
    $('#discuss_post_wrap').css({height: $('#discuss_post').outerHeight()});
  },

  scroll: function() {
    if (A.discussPostFixed) {
      var $win = $(window), $post = $('#discuss_post'), wY = $win.height() + $win.scrollTop(), fY = $('#discuss_fixer').offset().top;
      if (wY < (fY + $post.outerHeight())) {
        $post.addClass('fixed');
        $('#discuss_cancel').show();
      }
      else $post.removeClass('fixed');
    }
    else {
      $('#discuss_post').removeClass('fixed');
      $('#discuss_cancel').hide();
      $(window).unbind('scroll', Discuss.scroll);
      clearInterval(A.discussPostTmHeight);
    }
  },

  onKeyUp: function() {
    var post = $.trim($('#discuss_text').val());
    A.discussPostFixed = (post.length > 0) ? true : false;
    if (!A.discussPostFixed) Discuss.scroll();
    else Discuss.fixPost();
  },

  cancelPost: function() {
    $('#discuss_text').val('').keyup();
  },

  sendPost: function(fid) {
    var post = $.trim($('#discuss_text').val()), attaches = [];

    if (!post) {
      $('#discuss_text').focus();
      return false;
    }

    if (A.discussPostCreate) return;
    A.discussPostCreate = true;

    $('input[type="hidden"][name*=attach]').each(function(i, item) {
      attaches.push($(item).val());
    });

    $('#bnt_progress').show();
    ajax.post('/discuss'+ fid +'?act=create', {post: post, attaches: attaches}, function(r) {
      $('#bnt_progress').hide();
      A.discussPostCreate = false;

      nav.go('/discuss'+ fid +'?offset=last&scroll=1');
    }, function(xhr) {
      $('#bnt_progress').hide();
      A.discussPostCreate = false;
    });
  },


  doReply: function() {
    var post = $.trim($('#dcp_text').val()), attaches = [];
    if (!post) {
      $('#dcp_text').focus();
      return;
    }

    $('#discuss_post_box input[type="hidden"][name*=attach]').each(function(i, item) {
      attaches.push($(item).val());
    });

    if (A.discussPostReplySending) return;
    A.discussPostReplySending = true;

    var fid = A.discussPostReplyOpened;
    $('#bnt_progress').show();

    ajax.post('/discuss'+ fid +'?act=create', {post: post, attaches: attaches, last_id: $('#discuss'+ fid).attr('last_id'), feed: true}, function(r) {
      $('#bnt_progress').hide();
      A.discussPostReplySending = false;

      $('#dcp_text').val('').blur();
      $('body').click();
      $('#dcp_attaches').html('');

      $(r.html).insertBefore('#reply_fakebox_discuss'+ fid);
      $('#discuss'+ fid).attr('last_id', r.last_id);
      $('#dch_text'+ fid).html(r.num);
    }, function(xhr) {
      $('#bnt_progress').hide();
      A.discussPostReplySending = false;
    });
  },

  attachPhoto: function(cont_id, file_id) {
    if (A.discussPostPhotoAttaches >= 3) {
      boxPopup('Вы не можете прикрепить более 3-х фотографий');
      return;
    }

    cur['fileDoneCustom'+ file_id] = Discuss.onUploadDone.pbind(cont_id, file_id);
    Upload.onStart(file_id);
  },

  onUploadDone: function(cont_id, file_id, filedata) {
    var json = $.parseJSON(filedata), uid = cont_id.replace(/#/, '');
    Upload.showInput(file_id);
    Upload.initFile(file_id, function() {
      Discuss.attachPhoto(file_id);
    });

    $('<input/>').attr({type: 'hidden', id: uid + file_id +'_attach', name: 'DiscussPost[attach][]'}).val(filedata).prependTo(cont_id);
    var cont = $('<div/>').attr({class: 'left post_attach_photo'}).appendTo(cont_id);
    cont.html('<img src="http://cs'+ json['b'][2] +'.'+ A.host +'/'+ json['b'][0] +'/'+ json['b'][1] +'" alt=""/><a class="tt photo_attach_delete" title="Удалить фотографию"><span class="icon-remove icon-white"></span></a>');
    var att_length = $(cont_id).find('.post_attach_photo').length;

    if (att_length >= 3) {
      $('#file_button_'+ file_id).hide();
    }

    cont.children('a').click(function() {
      $('#file_button_'+ file_id).show();
      cont.remove();
      $('#'+ uid + file_id +'_attach').remove();
    });
  },

  onEditDelete: function(cont) {
    $(cont).parent().remove();
  },

  editPost: function(post_id) {
    if (A.postEditing) {
      $('#discuss_post_editing').focus();
      return;
    }
    A.postEditing = post_id;
    $('#discuss_post'+ post_id + ' div.dc_progress').show();

    ajax.post('/discuss/post/edit?post_id='+ post_id, null, function(r) {
      $('#discuss_post'+ post_id + ' div.dc_progress').hide();
      $('#discuss_post'+ post_id + ' div.discuss_post_bottom').hide();
      $('#discuss_post_data'+ post_id).hide();

      $(r.html).insertBefore('#discuss_post_data'+ post_id);
      $('#content').trigger('contentChanged', [{noscroll: true}]);

      var att_length = $('#dce_attaches').find('.post_attach_photo').length;
      if (att_length >= 3) $('div.discuss_post_edit_wrap div.fileupload').hide();
    }, function(xhr) {
      $('#discuss_post'+ post_id + ' div.dc_progress').hide();
      A.postEditing = false;
    });
  },

  cancelEdit: function() {
    var $obj = $('#discuss_post'+ A.postEditing);
    $obj.find('div.discuss_post_edit_wrap').remove();
    $obj.find('div.discuss_post_bottom').show();
    $('#discuss_post_data'+ A.postEditing).show();

    A.postEditing = false;
  },

  doEditPost: function() {
    var post = $.trim($('#discuss_post_editing').val()), attaches = [];

    if (!post) {
      $('#discuss_post_editing').focus();
      return false;
    }

    if (A.postEditingProgress) return;
    A.postEditingProgress = true;

    $('#dce_attaches input[type="hidden"][name*=attach]').each(function(i, item) {
      attaches.push($(item).val());
    });

    $('#dce_progress').show();
    ajax.post('/discuss/post/edit?post_id='+ A.postEditing, {post: post, attaches: attaches}, function(r) {
      $('#dce_progress').hide();
      A.postEditingProgress = false;
      Discuss.cancelEdit();

      var h = $(r.html);

      $('#discuss_post_data'+ r.post_id).html(h.find('#discuss_post_data'+ r.post_id));
      $('#discuss_post_data'+ r.post_id).effect('highlight');
    }, function(xhr) {
      $('#dce_progress').hide();
      A.postEditingProgress = false;
    });
  },

  deletePost: function(post_id) {
    if (A.postDeleting) return;
    A.postDeleting = true;

    $('#discuss_post'+ post_id + ' div.dc_progress').show();
    ajax.post('/discuss/post/delete?post_id='+ post_id, null,function(r) {
      A.postDeleting = false;
      $('#discuss_post'+ post_id + ' div.dc_progress').hide();
      $('#discuss_post'+ post_id + ' > table').hide().after(r.html);
    }, function(xhr) {
      A.postDeleting = false;
      $('#discuss_post'+ post_id + ' div.dc_progress').hide();
    });
  },

  deleteFeedPost: function(post_id) {
    ajax.post('/discuss/post/delete?post_id='+ post_id, {feed: true},function(r) {
      $('#discuss_post'+ post_id + ' div.reply_table').hide().after(r.html);
    }, function(xhr) {
    });
  },

  massDelete: function(post_id, author_id, hash) {
    ajax.post('/discuss/post/massdelete', {post_id: post_id, author_id: author_id, hash: hash}, function(r) {
      boxPopup(r.html);
    }, function(xhr) {
    });
  },

  restorePost: function(post_id, hash) {
    ajax.post('/discuss/post/restore?post_id='+ post_id, {hash: hash}, function(r) {
      $('#discuss_post'+ post_id + ' > table').show().next().remove();
    }, function(xhr) {
    });
  },

  restoreFeedPost: function(post_id, hash) {
    ajax.post('/discuss/post/restore?post_id='+ post_id, {hash: hash, feed: true}, function(r) {
      $('#discuss_post'+ post_id + ' div.reply_table').show().next().remove();
    }, function(xhr) {
    });
  },

  showReplyEditor: function(event, fid) {
    if (A.discussPostReplyOpened == fid) return;
    if (A.discussPostReplyOpened != fid) $('#reply_fakebox_discuss'+ fid).show();

    A.discussPostReplyOpened = fid;

    var id = fid.split('_'), hoop_id = id.pop(), hoop_type = id.pop();
    var $reply = $('#discuss_post_box');
    $reply.appendTo('#discuss'+ fid +' div.replies').show();
    $('#reply_fakebox_discuss'+ fid).hide();
    $reply.find('textarea').focus();
    $('#dcp_text').val('');

    event.stopPropagation();

    setTimeout(function() {
      $('body').one('click', function() {
        $('#reply_fakebox_discuss'+ fid).show()
        $reply.hide().appendTo('div[rel="reply_parking_lot"]');
        A.discussPostReplyOpened = false;
      });
    }, 100);
  },

  replyFeedPost: function(post_id, event) {
    Discuss.showReplyEditor(event, $('#discuss_post'+ post_id).attr('data-fid'));
    $('#dcp_text').focus().val($('#dcp_text').val() + '[post'+ post_id +'|'+ $('#discuss_post'+ post_id + ' a.author').attr('data-name') +'], ');
  },

  showMore: function(fid, first_id) {
    if (!A.postHeader) A.postHeader = {};

    var $txt = $('#dch_text'+ fid), $prg = $('#dch_prg'+ fid);
    if ($txt.parent().hasClass('wrh_all')) {
      $txt.text(A.postHeader[fid]).parent().removeClass('wrh_all');
      $('#discuss_replies'+ fid +' .reply').each(function(i, item) {
        if (parseInt($(item).attr('id').match(/discuss_post(\d+)/i)[1]) < first_id) $(item).remove();
      });
      return;
    }

    A.postHeader[fid] = $txt.text();
    $txt.hide();
    $prg.show();

    ajax.post('/discuss'+ fid +'?act=more', {first_id: first_id}, function(r) {
      $txt.text('Скрыть комментарии').show();
      $prg.hide();
      $txt.parent().addClass('wrh_all');

      $(r.html).insertAfter('#discuss_replies'+ fid + ' a.wr_header');
    }, function(xhr) {
      $txt.show();
      $prg.hide();
    });
  }
};

try {stmgr.loaded('discuss.js');}catch(e){}