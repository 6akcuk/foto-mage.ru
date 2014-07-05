var Wall = {
  showEditor: function() {
    if (A.wallPostOpened) return;
    if (!A.wallPostOpened) A.wallPostOpened = true;
    if (typeof A.wallPhotoAttaches == 'undefined') A.wallPhotoAttaches = 0;

    $('#wall_post').css({minHeight: 32, height: 32});
    $('#wall_post_btn').show();

    setTimeout(function() {
      $('body').one('click', function() {
        $('#wall_post').css({minHeight: 14, height: 14});
        $('#wall_post_btn').hide();
        A.wallPostOpened = false;
      });
    }, 100);
  },

  attachPhoto: function(file_id) {
    if (A.wallPhotoAttaches >= 3) {
      boxPopup('Вы не можете прикрепить более 3-х фотографий');
      return;
    }

    cur['fileDoneCustom'+ file_id] = Wall.onUploadDone.pbind(file_id);
    Upload.onStart(file_id);
  },

  onUploadDone: function(file_id, filedata) {
    var json = $.parseJSON(filedata);
    Upload.showInput(file_id);
    Upload.initFile(file_id, function() {
      Wall.attachPhoto(file_id);
    });

    $('<input/>').attr({type: 'hidden', id: 'wall_'+ file_id +'_attach', name: 'Wall[attach][photo][]'}).val(filedata).prependTo('#wall_post_btn');
    var cont = $('<div/>').attr({class: 'left wall_attach_photo'}).appendTo('#wall_attaches');
    cont.html('<img src="http://cs'+ json['b'][2] +'.'+ A.host +'/'+ json['b'][0] +'/'+ json['b'][1] +'" alt=""/><a class="tt photo_attach_delete" title="Удалить фотографию"><span class="icon-remove icon-white"></span></a>');
    A.wallPhotoAttaches++;

    if (A.wallPhotoAttaches >= 3) {
      $('#file_button_'+ file_id).hide();
    }

    cont.children('a').click(function() {
      $('#file_button_'+ file_id).show();
      A.wallPhotoAttaches--;
      if (A.wallPhotoAttaches < 0) A.wallPhotoAttaches = 0;
      cont.remove();
      $('#wall_'+ file_id +'_attach').remove();
    });
  },

  doPost: function(wall_id) {
    var post = $.trim($('#wall_post').val()), wall = {post: null, attach: {photo: []}};
    if (!post) {
      $('#wall_post').focus();
      return;
    }

    wall.post = post;
    $('#wall_post_btn input[name*="Wall[attach][photo]"]').each(function(i, item) {
      wall.attach.photo.push($(item).val());
    });

    if (A.wallPostSending) return;
    A.wallPostSending = true;

    $('#wall_post_progress').show();
    ajax.post('/users/profiles/wallpost?id='+ wall_id, {last_id: A.wallLastID, wall: wall}, function(r) {
      A.wallPostSending = false;
      $('#wall_post_progress').hide();

      $('#wall_post').val('').blur();
      $('body').click();

      $('#wall_header').html(r.num);
      $(r.posts).prependTo('#wall'+ wall_id);
      A.wallPhotoAttaches = 0;
      $('#wall_attaches').html('');
      $('#wall_post_btn input[name*="Wall[attach][photo]"]').remove();
      A.wallLastID = r.last_id;
    }, function(xhr) {
      A.wallPostSending = false;
      $('#wall_post_progress').hide();
    });
  },

  postOver: function(id, event) {
    if (event.target.id == 'delete_'+ id) return;
    $('#delete_'+ id).css({opacity: 0.3})
  },
  postOut: function(id, event) {
    $('#delete_'+ id).css({opacity: 0});
  },
  postDeleteOver: function(id) {
    $('#delete_'+ id).css({opacity: 1});
    Tooltip.show('#delete_'+ id);
  },
  postDeleteOut: function(id) {
    $('#delete_'+ id).css({opacity: 0.3});
    Tooltip.hide();
  },
  deletePost: function(id) {
    var id = id.split('_'), post_id = id.pop(), wall_id = id.pop();
    ajax.post('/users/profiles/deletewallpost?id='+ post_id, {}, function(r) {
      $('#wall'+ wall_id + '_'+ post_id + ' div.post_table').hide();
      $('<div id="post_del'+ wall_id + '_'+ post_id +'" class="dld">Сообщение удалено. <a onclick="Wall.restorePost(\''+ wall_id +'_'+ post_id +'\')">Восстановить</a></div>').insertAfter('#wall'+ wall_id +'_'+ post_id + ' div.post_table');
    }, function(xhr) {

    });
  },
  restorePost: function(id) {
    var id = id.split('_'), post_id = id.pop(), wall_id = id.pop();
    ajax.post('/users/profiles/restorewallpost?id='+ post_id, {}, function(r) {
      $('#wall'+ wall_id + '_'+ post_id + ' div.post_table').show().next().remove();
    }, function(xhr) {

    });
  },
  deleteReply: function(id) {
    var id = id.split('_'), post_id = id.pop(), wall_id = id.pop();
    ajax.post('/users/profiles/deletewallpost?id='+ post_id, {}, function(r) {
      $('#post'+ wall_id + '_'+ post_id + ' div.reply_table').hide();
      $('<div id="reply_del'+ wall_id + '_'+ post_id +'" class="dld">Сообщение удалено. <a onclick="Wall.restoreReply(\''+ wall_id +'_'+ post_id +'\')">Восстановить</a></div>').insertAfter('#post'+ wall_id +'_'+ post_id + ' div.reply_table');
    }, function(xhr) {

    });
  },
  restoreReply: function(id) {
    var id = id.split('_'), post_id = id.pop(), wall_id = id.pop();
    ajax.post('/users/profiles/restorewallpost?id='+ post_id, {}, function(r) {
      $('#post'+ wall_id + '_'+ post_id + ' div.reply_table').show().next().remove();
    }, function(xhr) {

    });
  },

  _replyBoxes: function(fid) {
    ($('#replies'+ fid + ' .reply').length)
      ? $('#reply_fakebox'+ fid).show()
      : $('#reply_link'+ fid).show();
  },

  replyClick: function(event, fid, reply_id) {
    Wall.showReplyEditor(event, fid);
    var id = fid.split('_'), post_id = id.pop(), wall_id = id.pop(), $a = $('#post'+ wall_id + '_' + reply_id +' a.author');
    $('#reply_text').val($a.attr('data-name') + ', ');
    $('#reply_box div.reply_to_title').html('<a href="'+ $a.attr('href') +'">'+ $a.attr('data-lex-name') +'</a>');
    $('#reply_to_title').val(parseInt($a.attr('data-id')));
  },

  showReplyEditor: function(event, fid) {
    if (A.wallReplyOpened == fid) return;
    if (A.wallReplyOpened != fid) Wall._replyBoxes(A.wallReplyOpened);

    A.wallReplyOpened = fid;
    if (typeof A.wallReplyPhotoAttaches == 'undefined') A.wallReplyPhotoAttaches = 0;

    var id = fid.split('_'), post_id = id.pop(), wall_id = id.pop();
    var $reply = $('#reply_box');
    $reply.appendTo('#wall'+ fid +' div.replies').show();
    $reply.find('#reply_to').val(post_id);
    $('#reply_fakebox'+ fid).hide();
    $('#reply_link'+ fid).hide();
    $reply.find('textarea').focus();
    $('#reply_to_title').val('');
    $('#reply_box div.reply_to_title').html('');
    $('#reply_text').val('');

    event.stopPropagation();

    setTimeout(function() {
      $('body').one('click', function() {
        Wall._replyBoxes(fid);
        $reply.hide().appendTo('div[rel="reply_parking_lot"]');
        A.wallReplyOpened = false;
      });
    }, 100);
  },

  replyAttachPhoto: function(file_id) {
    if (A.wallPhotoAttaches >= 3) {
      boxPopup('Вы не можете прикрепить более 3-х фотографий');
      return;
    }

    cur['fileDoneCustom'+ file_id] = Wall.onReplyUploadDone.pbind(file_id);
    Upload.onStart(file_id);
  },

  onReplyUploadDone: function(file_id, filedata) {
    var json = $.parseJSON(filedata);
    Upload.showInput(file_id);
    Upload.initFile(file_id, function() {
      Wall.replyAttachPhoto(file_id);
    });

    $('<input/>').attr({type: 'hidden', id: 'reply_'+ file_id +'_attach', name: 'Wall[attach][photo][]'}).val(filedata).prependTo('#reply_box .reply_field_wrap');
    var cont = $('<div/>').attr({class: 'left wall_attach_photo'}).appendTo('#reply_box .reply_attaches');
    cont.html('<img src="http://cs'+ json['b'][2] +'.'+ A.host +'/'+ json['b'][0] +'/'+ json['b'][1] +'" alt=""/><a class="tt photo_attach_delete" title="Удалить фотографию"><span class="icon-remove icon-white"></span></a>');
    A.wallReplyPhotoAttaches++;

    if (A.wallReplyPhotoAttaches >= 3) {
      $('#file_button_'+ file_id).hide();
    }

    cont.children('a').click(function() {
      $('#file_button_'+ file_id).show();
      A.wallReplyPhotoAttaches--;
      if (A.wallReplyPhotoAttaches < 0) A.wallReplyPhotoAttaches = 0;
      cont.remove();
      $('#reply_'+ file_id +'_attach').remove();
    });
  },

  doReply: function(wall_id) {
    var post = $.trim($('#reply_text').val()), wall = {post: null, reply_to: 0, attach: {photo: []}};
    if (!post) {
      $('#reply_text').focus();
      return;
    }

    wall.post = post;
    wall.reply_to = parseInt($('#reply_box input[name="reply_to"]').val());

    var reply_to_title = parseInt($('#reply_to_title').val());
    if (reply_to_title) wall.reply_to_title = reply_to_title;

    $('#reply_box .reply_field_wrap input[name*="Wall[attach][photo]"]').each(function(i, item) {
      wall.attach.photo.push($(item).val());
    });

    if (A.wallReplySending) return;
    A.wallReplySending = true;

    if (!wall_id) {
      var _id = A.wallReplyOpened.split('_'), post_id = _id.pop(), wall_id = _id.pop();
    }

    ajax.post('/users/profiles/wallpost?id='+ wall_id, {last_id: parseInt($('#replies'+ wall_id +'_'+ wall.reply_to +' input[name="last_id"]').val()), wall: wall}, function(r) {
      A.wallReplySending = false;

      $('#reply_text').val('').blur();
      $('body').click();

      A.wallReplyPhotoAttaches = 0;
      $('#reply_box .reply_attaches').html('');
      $('#reply_box .reply_field_wrap input[name*="Wall[attach][photo]"]').remove();
      $('#wrh_text'+ wall_id +'_'+ wall.reply_to).html(r.num);
      $(r.replies).appendTo('#replies'+ wall_id +'_'+ wall.reply_to);
      $('#replies'+ wall_id +'_'+ wall.reply_to +' input[name="last_id"]').val(r.last_id);
    }, function(xhr) {
      A.wallReplySending = false;
    });
  },

  showReplies: function(fid, first_id) {
    if (!A.replyHeader) A.replyHeader = {};

    var $txt = $('#wrh_text'+ fid), $prg = $('#wrh_prg'+ fid);
    if ($txt.parent().hasClass('wrh_all')) {
      $txt.text(A.replyHeader[fid]).parent().removeClass('wrh_all');
      $('#replies'+ fid +' .reply').each(function(i, item) {
        if (parseInt($(item).attr('id').match(/post\d+_(\d+)/i)[1]) < first_id) $(item).remove();
      });
      return;
    }

    if (A.repliesLoading) return;
    A.repliesLoading = true;

    A.replyHeader[fid] = $txt.text();
    $txt.hide();
    $prg.show();
    var id = fid.split('_'), post_id = id.pop(), wall_id = id.pop();

    ajax.post('/users/profiles/postreplies?post_id='+ post_id, {first_id: first_id}, function(r) {
      A.repliesLoading = false;
      $txt.text('Скрыть комментарии').show();
      $prg.hide();
      $txt.parent().addClass('wrh_all');

      $(r.html).insertAfter('#replies'+ fid + ' a.wr_header');
    }, function(xhr) {
      A.repliesLoading = false;
      $txt.show();
      $prg.hide();
    });
  }
}

try {stmgr.loaded('wall.js');}catch(e){}