var Comment = {
  add: function(hoop_id) {
    if (A.commentSending) return;
    $('#comment'+ hoop_id + '_progress').show();
    A.commentSending = true;

    var $form = $('#hoop'+ hoop_id + '_form');
    FormMgr.submit($form, null, function(r) {
      $('#comment'+ hoop_id + '_progress').hide();
      A.commentSending = false;
      A.commentPhotoAttaches = 0;

      $form.find('input[type="hidden"]').remove();
      $form.find('div.comment_attach_photo').remove();
      $form.find('textarea').val('').blur();

      Comment.peer(hoop_id, true);
    }, function(xhr) {
      $('#comment'+ hoop_id + '_progress').hide();
      A.commentSending = false;
    });
  },

  replyTo: function(event, comment_id) {
    var $a = $('#comment_'+ comment_id +' div.comment_header a');
    $('#Comment_text').focus().val($a.attr('data-name') + ', ');
    $('div.comment_post div.reply_to_title').html('<a href="'+ $a.attr('href') +'">'+ $a.attr('data-lex-name') +'</a>&nbsp;<a class="icon-remove" onclick="Comment.removeReply()"></a>');
    $('#reply_to_title').val(parseInt($a.attr('data-id')));
  },

  feedReplyTo: function(event, comment_id) {
    var $a = $('#comment'+ comment_id +' div.reply_text a.author');
    Comment.showReplyEditor(event, $a.closest('div.wall_post').attr('id').replace(/comment/, ''));
    $('#Comment_text').val($a.attr('data-name') + ', ').focus();
    $('#comment_box div.reply_to_title').html('<a href="'+ $a.attr('href') +'">'+ $a.attr('data-lex-name') +'</a>&nbsp;<a class="icon-remove" onclick="Comment.feedRemoveReply()"></a>');
    $('#com_reply_to_title').val(parseInt($a.attr('data-id')));
  },

  removeReply: function() {
    $('#Comment_text').val('');
    $('div.comment_post div.reply_to_title').html('');
    $('#reply_to_title').val('');
  },

  feedRemoveReply: function() {
    $('#Comment_text').val('');
    $('#comment_box div.reply_to_title').html('');
    $('#com_reply_to_title').val('');
  },

  edit: function(comment_id) {
    if (A.commentEditing) {
      $('#comment_'+ comment_id +'_editing textarea').focus();
      return;
    }
    A.commentEditing = comment_id;
    $('#comment_'+ comment_id + '_edit').html('<img src="/images/upload.gif" />');

    ajax.post('/comment/edit/id/'+ comment_id, null, function(r) {
      $('#comment_'+ comment_id + '_edit').html('Редактировать');
      var $obj = $('#comment_'+ comment_id);

      $obj.find('div.comment_text, div.comment_attaches, div.comment_control').hide();
      $('<span class="comment_editing"> - редактирование комментария</span>').appendTo($obj.find('div.comment_header'));

      $(r.html).appendTo($obj.find('div.comment_header'));
      $('#content').trigger('contentChanged', [{noscroll: true}]);
    }, function(xhr) {
      $('#comment_'+ comment_id + '_edit').html('Редактировать');
      A.commentEditing = false;
    });
  },

  doEdit: function(comment_id) {
    $('#comment'+ comment_id + '_editprogress').show();

    var $form = $('#comment'+ comment_id + '_form');
    FormMgr.submit($form, null, function(r) {
      $('#comment'+ comment_id + '_editprogress').hide();

      Comment.cancelEdit(comment_id);
      var h = $(r.html);

      $('#comment_'+ comment_id).find('.comment_text').html(h.find('.comment_text'));
      $('#comment_'+ comment_id).find('.comment_attaches').html(h.find('.comment_attaches'));

      $('#comment_'+ comment_id +' .comment_text, #comment_'+ comment_id +' .comment_attaches').effect('highlight');
    }, function(xhr) {
      $('#comment'+ comment_id + '_editprogress').hide();
    });
  },

  cancelEdit: function(comment_id) {
    A.commentEditing = false;

    var $obj = $('#comment_'+ comment_id);
    $obj.find('form').remove();
    $obj.find('div.comment_text, div.comment_attaches, div.comment_control').show();
    $obj.find('div.comment_header span').remove();
  },

  onEditDelete: function(cont) {
    $(cont).parent().remove();
  },

  delete: function(comment_id) {
    if (A.commentDeleting) return;
    A.commentDeleting = true;
    $('#comment_'+ comment_id + '_delete').html('<img src="/images/upload.gif" />');

    ajax.post('/comment/delete/id/'+ comment_id, null, function(r) {
      A.commentDeleting = false;
      $('#comment_'+ comment_id + '_delete').html('Удалить');
      var $c = $('#comment_'+ comment_id);
      $('<div id="comment_'+ comment_id +'_deleted" class="op_report">'+ r.html +'</div>').insertAfter($c);
      $c.hide();
    }, function(xhr) {
      A.commentDeleting = false;
      $('#comment_'+ comment_id + '_delete').html('Удалить');
    });
  },

  deleteFeed: function(comment_id) {
    if (A.commentDeleting) return;
    A.commentDeleting = true;

    ajax.post('/comment/delete/id/'+ comment_id, {feed: true},function(r) {
      A.commentDeleting = false;
      var $c = $('#comment'+ comment_id);
      $('<div id="comment_'+ comment_id +'_deleted" class="op_report">'+ r.html +'</div>').insertAfter($c);
      $c.hide();
    }, function(xhr) {
      A.commentDeleting = false;
    });
  },

  massDelete: function(hoop_id, hoop_type, author_id, hash) {
    if (A.commentDeleting) return;
    A.commentDeleting = true;

    ajax.post('/comment/massdelete', {hoop_id: hoop_id, hoop_type: hoop_type, author_id: author_id, hash: hash}, function(r) {
      A.commentDeleting = false;
      boxPopup(r.html);
    }, function(xhr) {
      A.commentDeleting = false;
    });
  },

  restore: function(el, comment_id, hash) {
    if (A.commentRestoring) return;
    A.commentRestoring = true;

    $(el).html('<img src="/images/upload.gif" />');

    ajax.post('/comment/restore/id/'+ comment_id, {hash: hash}, function(r) {
      A.commentRestoring = false;
      $(el).html('Восстановить');

      $('#comment_'+ comment_id).show();
      $('#comment_'+ comment_id + '_deleted').remove();
    }, function(xhr) {
      A.commentRestoring = false;
      $(el).html('Восстановить');
    });
  },

  restoreFeed: function(el, comment_id, hash) {
    if (A.commentRestoring) return;
    A.commentRestoring = true;

    $(el).html('<img src="/images/upload.gif" />');

    ajax.post('/comment/restore/id/'+ comment_id, {hash: hash}, function(r) {
      A.commentRestoring = false;
      $(el).html('Восстановить');

      $('#comment'+ comment_id).show();
      $('#comment_'+ comment_id + '_deleted').remove();
    }, function(xhr) {
      A.commentRestoring = false;
      $(el).html('Восстановить');
    });
  },

  attachPhoto: function(hoop_id, file_id) {
    if (A.commentPhotoAttaches >= 3) {
      boxPopup('Вы не можете прикрепить более 3-х фотографий');
      return;
    }

    cur['fileDoneCustom'+ file_id] = Comment.onUploadDone.pbind(hoop_id, file_id);
    Upload.onStart(file_id);
  },

  onUploadDone: function(hoop_id, file_id, filedata) {
    var json = $.parseJSON(filedata);
    Upload.showInput(file_id);
    Upload.initFile(file_id, function() {
      Comment.attachPhoto(hoop_id, file_id);
    });

    $('<input/>').attr({type: 'hidden', id: 'hoop'+ hoop_id +'_'+ file_id +'_attach', name: 'Comment[attach][]'}).val(filedata).prependTo('#hoop'+ hoop_id +'_form');
    var cont = $('<div/>').attr({class: 'left comment_attach_photo'}).appendTo('#hoop'+ hoop_id +'_attaches');
    cont.html('<img src="http://cs'+ json['b'][2] +'.'+ A.host +'/'+ json['b'][0] +'/'+ json['b'][1] +'" alt=""/><a class="tt photo_attach_delete" title="Удалить фотографию"><span class="icon-remove icon-white"></span></a>');
    A.commentPhotoAttaches++;

    if (A.commentPhotoAttaches >= 3) {
      $('#file_button_'+ file_id).hide();
    }

    cont.children('a').click(function() {
      $('#file_button_'+ file_id).show();
      A.commentPhotoAttaches--;
      if (A.commentPhotoAttaches < 0) A.commentPhotoAttaches = 0;
      cont.remove();
      $('#hoop'+ hoop_id +'_'+ file_id +'_attach').remove();
    });
  },

  showReplyEditor: function(event, fid) {
    if (A.commentReplyOpened == fid) return;
    if (A.commentReplyOpened != fid) $('#reply_fakebox'+ fid).show();

    A.commentReplyOpened = fid;
    if (typeof A.commentPhotoAttaches == 'undefined') A.commentPhotoAttaches = 0;

    var id = fid.split('_'), hoop_id = id.pop(), hoop_type = id.pop();
    var $reply = $('#comment_box');
    $reply.appendTo('#comment'+ fid +' div.replies').show();
    $('#reply_fakebox'+ fid).hide();
    $('#reply_link'+ fid).hide();
    $reply.find('textarea').focus();
    $('#com_reply_to_title').val('');
    $('#comment_box div.reply_to_title').html('');
    $('#com_reply_text').val('');

    event.stopPropagation();

    setTimeout(function() {
      $('body').one('click', function() {
        $('#reply_fakebox'+ fid).show()
        $reply.hide().appendTo('div[rel="reply_parking_lot"]');
        A.commentReplyOpened = false;
      });
    }, 100);
  },

  doReply: function() {
    var text = $.trim($('#Comment_text').val()), com = {text: null, attach: {photo: []}};
    if (!text) {
      $('#Comment_text').focus();
      return;
    }

    com.text = text;

    var reply_to_title = parseInt($('#com_reply_to_title').val());

    $('#comment_box .reply_field_wrap input[name*="Comment[attach][photo]"]').each(function(i, item) {
      com.attach.photo.push($(item).val());
    });

    if (A.commentReplySending) return;
    A.commentReplySending = true;

    var _id = A.commentReplyOpened.split('_'), hoop_id = _id.pop(), hoop_type = _id.pop();

    ajax.post('/comment/add?hoop_type='+ hoop_type + '&hoop_id='+ hoop_id, {Comment: com, reply_to_title: reply_to_title, feed: true}, function(r) {
      A.commentReplySending = false;

      $('#Comment[text]').val('').blur();
      $('body').click();

      A.commentPhotoAttaches = 0;
      $('#comment_box .reply_attaches').html('');
      $('#comment_box .reply_field_wrap input[name*="Comment[attach][photo]"]').remove();

      Comment.peer(hoop_id, true, {hoop_type: hoop_type, feed: true});
    }, function(xhr) {
      A.commentReplySending = false;
    });
  },

  peer: function(hoop_id, drop_formal, opts) {
    if (!opts) opts = {};

    var hoop = (!opts.feed) ? A.commentHoop[hoop_id] : A.commentHoopFeed[opts.hoop_type + '_'+ hoop_id],
        hoop_type = (!opts.feed) ? hoop.type : opts.hoop_type;

    if (!opts.feed) clearTimeout(hoop.timer);
    if (!opts.feed && !$('#hoop'+ hoop_id +'_comments').length) return;

    ajax.post('/comment/peer?hoop_id='+ hoop_id +'&hoop_type='+ hoop_type, {last_id: hoop.last_id, feed: (opts.feed) ? true : false}, function(r) {
      if (!drop_formal) {
        hoop.counter += r.count;

        if (r.count > 0) {
          var nw = $('#hoop'+ hoop_id + '_new');
          if (nw.length) nw.html('Добавлено новых комментариев: '+ hoop.counter);
          else $('<a id="hoop'+ hoop_id +'_new" class="comment_show_more" onclick="Comment.showNew('+ hoop_id +')">Добавлено новых комментариев: '+ hoop.counter +'</a>').appendTo('#hoop'+ hoop_id +'_comments');
        }
      }
      else if (!opts.feed) Comment.showNew(hoop_id);
      hoop.last_id = r.last_id;

      var sett = {hide: !drop_formal, feed: (!opts.feed) ? null : true, hoop_type: hoop_type};

      $.each(r.items, function(i, item) {
        Comment.render(hoop_id, item, sett);
      });

      if (!opts.feed) {
        hoop.timer = setTimeout(function() {
          Comment.peer(hoop_id);
        }, 5000);
      }
    }, function(xhr) {
      if (!opts.feed) {
        hoop.timer = setTimeout(function() {
          Comment.peer(hoop_id);
        }, 5000);
      }
    });
  },

  render: function(hoop_id, item, opts) {
    var $i = $(item), id = (!opts.feed) ? '#hoop'+ hoop_id +'_comments' : '#replies'+ opts.hoop_type + '_'+ hoop_id;
    if (opts.hide) $i.addClass('comment_new');
    if (!opts.prepend) $i.appendTo(id);
    else $i.prependTo(id);
  },

  showNew: function(hoop_id) {
    A.commentHoop[hoop_id].counter = 0;
    $('#hoop'+ hoop_id +'_new').remove();
    $('div.comments_list .comment_new').removeClass('comment_new').effect('highlight', 500);
  },

  showMore: function(hoop_id, hoop_type, first_id, feed) {
    if (!A.commentHeader) A.commentHeader = {};

    if (A.commentLoading) return;
    A.commentLoading = true;

    var fid = hoop_type + '_' + hoop_id, $txt = $('#wrh_text'+ fid), $prg = $('#wrh_prg'+ fid);
    if ($txt.parent().hasClass('wrh_all')) {
      $txt.text(A.commentHeader[fid]).parent().removeClass('wrh_all');
      $('#replies'+ fid +' .reply').each(function(i, item) {
        if (parseInt($(item).attr('id').match(/post\d+_(\d+)/i)[1]) < first_id) $(item).remove();
      });
      return;
    }

    A.commentHeader[fid] = $txt.text();
    $txt.hide();
    $prg.show();

    ajax.post('/comment/more?hoop_id='+ hoop_id +'&hoop_type='+ hoop_type, {first_id: first_id, feed: feed}, function(r) {
      A.commentLoading = false;
      $txt.parent().remove(); //.text('Скрыть комментарии').show();
      //$prg.hide();
      //$txt.parent().addClass('wrh_all');

      $.each(r.items, function(i, item) {
        Comment.render(hoop_id, item, {prepend: true, feed: feed, hoop_type: hoop_type});
      });
    }, function(xhr) {
      A.commentLoading = false;
      $txt.show();
      $prg.hide();
    });
  }
};

try {stmgr.loaded('comments.js');}catch(e){}