function MediaMenu(el) {
  this.container = $('\
  <div id="add_media_menu_'+ (A.uuid++) +'" class="add_media_menu">\
    <div class="add_media_rows">\
      <div class="rows"></div>\
    </div>\
  </div>\
  ').appendTo('body');

  this.rows_wrap = this.container.find('div.rows');
  this.rows = {};

  // functions
  this.addHead = function(label) {
    if (this.head) this.head.remove();
    this.head = $('<div class="add_media_head noselect"><nobr>'+ label +'</nobr></div>').appendTo(this.rows_wrap);
  }
  this.addRow = function(type, handler) {
    var position = '';
    if (this.rows[type]) this.rows[type].remove();
    switch (type) {
      case 'photo':
        position = '3px 3px';
        break;
      case 'audio':
        position = '3px -42px';
        break;
      case 'video':
        position = '3px -20px';
        break;
      case 'document':
        position = '3px -64px';
        break;
    }
    this.rows[type] = $('<a class="add_media_type_'+ type +' add_media_item"><nobr>'+ getLang('upload_'+ type) +'</nobr></a>').appendTo(this.rows_wrap);
    this.rows[type].css({
      backgroundImage: 'url(/images/icons/attach_icons.png)',
      backgroundPosition: position
    });
    this.rows[type].bind('click', handler);
  }

  // event handlers
  function hideContainer(e) {
    self.container.hide();
    self.container.unbind('mouseleave', hideContainer);
  }

  // bindings
  var self = this;
  el.bind('mouseenter', function(e) {
    self.container.show();
    self.container.css({
      left: (el.offset().left + el.width() / 2) - (self.container.width() / 2),
      top: el.offset().top - 1 - 7
    });

    self.container.bind('mouseleave', hideContainer);
  });
}

var Upload = {
  isFileAPIEnabled: function() {
    return !!window.FileReader;
  },

  initAttaches: function(el, options) {
    el = document.getElementById(el);
    if (!el) return;
    el = $(el);
    if (!cur.uploadId) cur.uploadId = 1;
    if (!cur.uploading) cur.uploading = false;

    var defaults = {
      allow: ['photo', 'video', 'audio', 'document']
    }, opts = $.extend({}, defaults, options), upload_id = cur.uploadId++;
    if (!opts.prefix) return;

    el.html('');

    $('\
    <form id="'+ opts.prefix +'_form" onsubmit="return false">\
    <div id="'+ opts.prefix +'_preview" class="clear_fix multi_media_preview"></div>\
    </form>\
    <div class="clear_fix">\
      <div class="fl_r" id="'+ opts.prefix +'_attach">\
        <span class="add_media_lnk">'+ getLang('upload_attach') +'</span>\
      </div>\
    </div>\
    ').appendTo(el);

    var media_preview_wrap = el.find('#'+ opts.prefix +'_preview'),
      media_preview = {},
      media_lnk = el.find('span.add_media_lnk'),
      media_menu = new MediaMenu(media_lnk);

    media_menu.addHead(getLang('upload_attach'));

    opts.allow.push('progress');
    $.each(opts.allow, function(i, allow) {
      if (allow != 'progress') media_menu.addRow(allow, function(e) {
        Upload.media_attach(el, opts.prefix, upload_id, allow);
      });
      media_preview[allow] = $('<div/>').attr({id: 'media_'+ allow + '_preview'+ upload_id, class: 'media_'+ allow +'_preview media_preview clear_fix'}).appendTo(media_preview_wrap);
    });

    if (opts.attaches) {
      if (opts.attaches.photo) {
        $.each(opts.attaches.photo, function(i, photo) {
          Upload.media_preview(upload_id, 'photo', photo);
        });
      }
      if (opts.attaches.document) {
        $.each(opts.attaches.document, function(i, doc) {
          Upload.media_preview(upload_id, 'document', doc);
        });
      }
    }
    // event handlers
  },
  media_attach: function(el, prefix, upload_id, type) {
    if (cur.uploading) {
      topError(getLang('upload_in_process'), {dt: 5});
      return;
    }
    $('#'+ prefix +'_'+ type +'_input').remove();
    var input = $('<input/>').attr({type: 'file', multiple: true, id: prefix + '_' + type + '_input', name: 'Filedata', class: 'media_input'}).appendTo(el);
    input.bind('change', function(e) {
      Upload.media_change.call(this, input, upload_id, type);
    });
    input.click();
  },
  media_change: function(el, upload_id, type) {
    if (!cur.fileNum) cur.fileNum = 1;

    var progress_wrap = $('\
    <div id="upload'+ cur.fileNum +'_progress_wrap" class="clear_fix" style="margin-top: 6px">\
      <div class="fl_l">\
        <div class="upload_attach_progress_wrap">\
          <div id="upload'+ cur.fileNum +'_progress" class="upload_attach_progress" style="width:0%"></div>\
        </div>\
      </div>\
      <div class="attach_label fl_l"></div>\
      <div class="progress_x fl_l" onmouseover="$(this).stop(true).animate({opacity: 1}, 200)" onmouseout="$(this).stop(true).animate({opacity: 0.6}, 200)"></div>\
    </div>\
    ').appendTo('#media_progress_preview'+ upload_id);

    cur.uploadProgressID = "";
    for (i = 0; i < 32; i++) {
      cur.uploadProgressID += Math.floor(Math.random() * 16).toString(16);
    }

    var cn = $('<div/>').prop({id: 'upload'+ upload_id +'_container'}).appendTo('#utils'),
      iframe = $('<iframe/>').prop({name: 'upload'+ upload_id +'_iframe'}).appendTo(cn),
      form = $('<form/>').prop({
        id: 'upload'+ upload_id +'_form',
        action: cur.uploadAction + '?X-Progress-ID='+ cur.uploadProgressID,
        enctype: 'multipart/form-data',
        method: 'post',
        target: 'upload'+ upload_id +'_iframe'
      }).appendTo(cn);

    $('<input/>').prop({type: 'hidden', name: 'upload_id', value: upload_id}).appendTo(form);
    $('<input/>').prop({type: 'hidden', name: 'type', value: type}).appendTo(form);

    el.appendTo(form);

    cur['uploadDone'+ upload_id] = function(filedata) {
      cur.uploading = false;

      $('#upload'+ upload_id +'_container').remove();
      clearInterval(cur.uploadProgress);
      $('#upload'+ cur.fileNum +'_progress_wrap').remove();

      Upload.media_preview(upload_id, type, filedata);
    }
    cur['uploadFailed'+ upload_id] = function(message) {
      cur.uploading = false;

      topError(message, {dt: 5});
      clearInterval(cur.uploadProgress);
      $('#upload'+ cur.fileNum +'_progress_wrap').remove();
    }

    form.submit();
    cur.uploading = true;
    cur.uploadProgress = setInterval(function() {
      Upload.media_progress();
    }, 1000);
  },
  media_progress: function() {
    var server = cur.uploadAction.match(/(http:\/\/[A-z0-9]*\.[A-z0-9\-]*\.[A-z]{2,6}\/).*/)[1];

    $.ajax({
      url: server +'progress?X-Progress-ID='+ cur.uploadProgressID,
      type: 'GET',
      dataType: 'text',
      success: function(r) {
        r = eval(r);

        if (r.state == 'done' || r.state == 'uploading') {
          $('#upload'+ cur.fileNum +'_progress').css('width', Math.floor((r.received / r.size) * 100) + '%');
        }
        /* we are done, stop the interval */
        if (r.state == 'done' || r.state == 'error') {
          cur.uploading = false;
          clearInterval(cur.uploadProgress);
          $('#upload'+ cur.fileNum +'_progress_wrap').remove();
        }
      }
    });
  },
  media_preview: function(upload_id, type, filedata) {
    if ($.isPlainObject(filedata) || $.isArray(filedata)) filedata = JSON.stringify(filedata);
    var json = $.parseJSON(filedata);

    if (type == 'photo') {
      var wrap = $('\
      <div class="media_preview_photo_wrap fl_l">\
        <input type="hidden" name="attach[photo][]" /> \
        <div class="fl_l media_preview_photo">\
          <img class="media_preview_photo" src="'+ Upload.build_url(type, json, 'b') +'" />\
        </div>\
        <div class="media_x_wrap inl_bl" onclick="$(this).parent().remove()">\
          <div class="media_x"></div>\
        </div>\
      </div>\
      ').appendTo('#media_photo_preview'+ upload_id);

      wrap.find('input[type="hidden"]').val(filedata);
    } else if (type == 'document') {
      var wrap = $('\
      <div class="media_preview_document_wrap fl_l">\
        <input type="hidden" name="attach[document][]" /> \
        <a target="_blank" class="medadd_h medadd_h_doc inl_bl" href="'+ Upload.build_url(type, json) +'">'+ getLang('upload_document_title') +'</a>\
        <div class="media_x_wrap inl_bl" onclick="$(this).parent().remove()">\
          <div class="media_x"></div>\
        </div>\
        <div class="medadd_c medadd_c_doc">\
          <a target="_blank" href="'+ Upload.build_url(type, json) +'">'+ json[3] +'</a>\
        </div>\
      </div>\
      ').appendTo('#media_document_preview'+ upload_id);

      wrap.find('input[type="hidden"]').val(filedata);
    }
  },
  build_url: function(type, json, size) {
    switch (type) {
      case 'photo':
        var j = json[size];
        return 'http://cs'+ j[2] +'.'+ A.host +'/'+ j[0] +'/'+ j[1];
        break;
      case 'audio':
        return 'http://cs'+ json[2] +'.'+ A.host +'/'+ json[0] +'/'+ json[1];
        break;
      case 'video':
        return 'http://cs'+ json[2] +'.'+ A.host +'/'+ json[0] +'/'+ json[1];
        break;
      case 'document':
        return 'http://cs'+ json[2] +'.'+ A.host +'/'+ json[0] +'/'+ json[1];
        break;
    }
  },

  initSinglePhoto: function(el, options) {
    el = document.getElementById(el);
    if (!el) return;
    el = $(el);
    if (!cur.uploadId) cur.uploadId = 1;

    var defaults = {
      selector_html: '<div class="button_blue"><button>'+ getLang('upload_select_file') +'</button></div>'
      }, opts = $.extend({}, defaults, options), wrap = el.parent(), upload_id = cur.uploadId++;

    $('\
    <div class="upload_selector_wrap"></div>\
    <div class="upload_progress_wrap">\
      <div id="upload'+ upload_id +'_progress" class="upload_progress"></div>\
      <a>Отмена</a>\
    </div>\
    <div class="upload_thumbs_wrap"></div>\
    ').appendTo(wrap);

    var selector_wrap = wrap.children('div.upload_selector_wrap'),
      selector_input,
      progress_wrap = wrap.children('div.upload_progress_wrap'),
      progress = progress_wrap.children('div.upload_progress'),
      cancel_link = progress.next(),
      thumbs_wrap = wrap.children('div.upload_thumbs_wrap'),
      selector = $(opts.selector_html).appendTo(selector_wrap);

    // functions
    var showThumb = function() {
      selector_wrap.hide();
      progress_wrap.hide();
      thumbs_wrap.show();
    }
    var showInput = function() {
      $('#upload'+ upload_id +'_container').remove();

      selector_input = $('<input/>').prop({id: 'upload'+ upload_id +'_file', type: 'file', name: 'Filedata'}).prependTo(selector_wrap);
      selector_input.bind('change', onChange);

      selector_wrap.show();
      progress_wrap.hide();
      thumbs_wrap.hide();
    }
    var onChange = function(e) {
      var cn = $('<div/>').prop({id: 'upload'+ upload_id +'_container'}).appendTo('#utils'),
        iframe = $('<iframe/>').prop({name: 'upload'+ upload_id +'_iframe'}).appendTo(cn),
        form = $('<form/>').prop({
          id: 'upload'+ upload_id +'_form',
          action: opts.action,
          enctype: 'multipart/form-data',
          method: 'post',
          target: 'upload'+ upload_id +'_iframe'
        }).appendTo(cn);
      $('<input/>').prop({type: 'hidden', name: 'upload_id', value: upload_id}).appendTo(form);
      $('<input/>').prop({type: 'hidden', name: 'type', value: 'photo'}).appendTo(form);

      selector_input.appendTo(form);

      cur['uploadDone'+ upload_id] = function(filedata) {
        $('#upload'+ upload_id +'_container').remove();
        el.val(filedata);
        showThumb();

        var json = $.parseJSON(filedata);
        Upload.renderThumb(upload_id, {
          json: json,
          el: el,
          opts: opts,
          thumbs_wrap: thumbs_wrap,
          handler: showInput
        });
      }
      cur['uploadFailed'+ upload_id] = function(message) {
        topError(message, {dt: 5});
        showInput();
      }

      form.submit();
      selector_wrap.hide();
      progress_wrap.show();
      progress.width('100%');
    }

    // bindings
    cancel_link.bind('click', function(e) {
      showInput();
    });
    selector.bind('click', function(e) {
      e.preventDefault();
      $(this).prev().click();
    });

    // finish
    var filedata = el.val();
    if (filedata) {
      var json = $.parseJSON(filedata);
      showThumb();
      Upload.renderThumb(upload_id, {
        json: json,
        el: el,
        opts: opts,
        thumbs_wrap: thumbs_wrap,
        handler: showInput
      });
    } else showInput();
  },
  renderThumb: function(id, options) {
    var json = options.json, opts = options.opts, handler = options.handler, el = options.el,
      thumbs_wrap = options.thumbs_wrap;

    var thumb = $('\
        <div class="upload_photo_thumb">\
          <img class="preview" width="'+ json[opts.size][3] +'" height="'+ json[opts.size][4] +'" src="http://cs'+ json[opts.size][2] +'.'+ A.host +'/'+ json[opts.size][0] +'/'+ json[opts.size][1] +'" /> \
          <div class="thumb_x_button"><div class="thumb_x"></div>\
        </div>\
        ').appendTo(thumbs_wrap);

    thumb.find('div.thumb_x').bind('mouseover mouseout click', function(e) {
      if (e.type == 'mouseover') $(this).stop(true).animate({opacity: 1}, 200);
      else if (e.type == 'mouseout') $(this).stop(true).animate({opacity: 0.6}, 200);
      else if (e.type == 'click') {
        el.val('');
        thumb.remove();
        handler();
      }
    });
  }
};

try{stManager.done('upload.js');}catch(e){}