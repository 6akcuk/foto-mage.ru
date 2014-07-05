var Photoview = {
  beforeShow: function () {
  },

  setupLayer: function () {
    cur.pvBackground = $('#layer_bg');
    cur.pvLayerWrap = $('#layer_wrap');
    cur.pvLayer = $('#layer');

    cur.pvLayer.html('\
<div class="pv_content">\
<table cellspacing="0" cellpadding="0">\
<tr><td class="sidesh s1"><div></div></td><td>\
<table cellspacing="0" cellpadding="0">\
<tr><td class="sidesh s2"><div></div></td><td>\
<table cellspacing="0" cellpadding="0">\
<tr><td class="sidesh s3"><div></div></td><td>\
\
<div id="pv_box">\
    <a class="fl_r pv_close_link" onclick="Photoview.hide()">Закрыть</a>\
    <div id="pv_summary"></div>\
    <div id="pv_photo_wrap">\
        <div id="pv_loader"></div>\
        <a id="pv_photo" style="display:none;" onclick="Photoview.next()"></a>\
        <div id="pv_html" class="clearfix" style="display:none;"></div>\
    </div>\
</div>\
\
</td><td class="sidesh s3"><div></div></td></tr>\
<tr><td colspan="3" class="bottomsh s3"><div></div></td></tr></table>\
</td><td class="sidesh s2"><div></div></td></tr>\
<tr><td colspan="3" class="bottomsh s2"><div></div></td></tr></table>\
</td><td class="sidesh s1"><div></div></td></tr>\
<tr><td colspan="3" class="bottomsh s1"><div></div></td></tr></table>\
</div>\
<div id="pv_left_nav" onmouseover="Photoview.activate(cur.pvLeft)" onmouseout="Photoview.deactivate(cur.pvLeft)" onmousedown="Photoview.prev()"></div>\
<div id="pv_right_nav" onmouseover="Photoview.activate(cur.pvClose)" onmouseout="Photoview.deactivate(cur.pvClose)" onmousedown="Photoview.hide()"></div>');

    $('\
<div class="pv_fixed fixed">\
    <div class="pv_left" onmouseover="Photoview.activate(this)" onmouseout="Photoview.deactivate(this)" onmousedown="Photoview.prev()"><div></div></div>\
    <div class="pv_close" onmouseover="Photoview.activate(this)" onmouseout="Photoview.deactivate(this)" onmousedown="Photoview.hide()"><div></div></div>\
</div>\
').appendTo('body');

    $.extend(cur, {
      pvBox: $('#pv_box'),
      pvSummary: $('#pv_summary'),
      pvLoader: $('#pv_loader'),
      pvPhoto: $('#pv_photo'),
      pvHtml: $('#pv_html'),

      pvLeftNav: $('#pv_left_nav'),
      pvRightNav: $('#pv_right_nav'),
      pvLeft: $('div.pv_left'),
      pvClose: $('div.pv_close')
    });
  },

  list: function (listId, data) {
    if (!cur.pvList) cur.pvList = {};
    cur.pvList[listId] = data;
  },

  show: function (listId, photo) {
    if (!cur.pvLayer) {
      Photoview.setupLayer();
    }

    if (!cur.pvList || !cur.pvList[listId]) {
      return;
    }

    listId = cur.pvList[listId];

    $.extend(cur, {
      pvCount: listId.count,
      pvBase: listId.base,
      pvItems: listId.items
    });

    cur.pvOffset = 0;
    if (photo >= 0) cur.pvOffset = photo;

    $.each(cur.pvItems, function (i, item) {
      //cur.pvOffset =
      /*if (item.a[1] == photo) {
       cur.pvOffset = i;
       return false;
       }*/
    });

    $win = $(window);
    $(document.body).css({
      overflow: 'hidden',
      cursor: 'default'
    });
    cur.pvBackground.addClass('pv_dark').show().css({
      height: $win.height()
    });
    cur.pvLayerWrap.show().css({
      width: $win.width(),
      height: $win.height()
    });
    cur.pvLeft.show().css({
      opacity: 0.4
    });
    cur.pvClose.show().css({
      opacity: 0.4,
      left: $win.width() - 57
    });
    cur.pvFirstStart = true;

    cur.pvSummary.html('<div class="loader"/>');
    Photoview.doShow();
  },

  hide: function () {
    $(document.body).css({
      overflow: 'auto'
    });
    cur.pvBackground.removeClass('pv_dark').hide();
    cur.pvLayerWrap.hide();
    cur.pvLeft.hide();
    cur.pvClose.hide();

    cur.pvLayer.html('');
    cur.pvLayer = null;
  },

  doShow: function () {
    if (!cur.pvList || !cur.pvItems || (cur.pvCount && typeof cur.pvOffset == 'undefined')) {
      return;
    }

    if (!cur.pvFirstStart) Photoview.beforeShow();
    cur.pvPhoto.html('<img src="' + Photoview.genUrl() + '" alt="" />').children().first().load(Photoview.doImageLoaded);

    cur.pvFirstStart = false;
  },

  doImageLoaded: function () {
    var hw = ($win.width() - cur.pvBox.width()) / 2;
    cur.pvLeftNav.css({
      width: hw - 20,
      height: $win.height() - 42
    });
    cur.pvRightNav.css({
      left: hw + cur.pvBox.width(),
      width: hw,
      height: $win.height() - 42
    });

    cur.pvSummary.html('Фотография ' + (cur.pvOffset + 1) + ' из ' + cur.pvCount);
    cur.pvLoader.hide();
    cur.pvPhoto.show();
    cur.pvHtml.show();
  },

  genUrl: function () {
    //var path = cur.pvItems[cur.pvOffset].x //cur.pvItems[cur.pvOffset].src.split('/');
    var item = cur.pvItems[cur.pvOffset].w;
    return 'http://cs' + item[2] + '.' + A.host + '/' + item[0] + '/' + item[1];
  },

  prev: function () {
    cur.pvOffset = (cur.pvOffset == 0) ? (cur.pvCount - 1) : cur.pvOffset - 1;
    Photoview.doShow();
  },

  next: function () {
    cur.pvOffset = (cur.pvOffset == (cur.pvCount - 1)) ? 0 : cur.pvOffset + 1;
    Photoview.doShow();
  },

  activate: function (arrow) {
    $(arrow).stop().animate({opacity: 1}, 200);
  },

  deactivate: function (arrow) {
    $(arrow).stop().animate({opacity: 0.4}, 200);
  },
  // много прям html ;)
  html: function (html) {
    cur.pvHtml.html(html);
  }
};

try {
  stManager.done('photoview.js');
} catch (e) {
}