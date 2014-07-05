function lockButton(el) {
  el = $(el);
  var lock = $('<div/>', {class: 'button_lock'}).insertBefore(el), w = el.outerWidth(), h = el.outerHeight();
  el.css({textIndent: '-9999px', width: w, height: h});
  lock.css({width: w, height: h});
}
function unlockButton(el) {
  el = $(el);
  el.prev('div.button_lock').remove();
  el.css({textIndent: ''});
}

function __phCheck(el, back, editable, focus, blur) {
  var shown = el.phshown, ph = el.phcont, v = el.val();

  if (shown && (back && v || !back && (focus && !focus.type || v))) {
    ph.hide();
    el.phshown = false;
  } else if (!shown && !v && (back || blur)) {
    ph.show();
    el.phshown = true;
    if (browser.opera && blur) {
      el.attr('placeholder', ''); el.removeAttr('placeholder', '');
    }
  }
  if (back && !v) {
    if (focus && !focus.type) {
      clearTimeout(el.phanim);
      el.phanim = setTimeout(function() {
        $(ph.children().children()).animate({
          color: '#C0C8D0'
        }, 200);
      }, 100);
    }
    if (blur) {
      clearTimeout(el.phanim);
      el.phanim = setTimeout(function() {
        $(ph.children().children()).animate({
          color: '#777777'
        }, 200);
      }, 100);
    }
  }
}

function placeholderSetup(el, opts) {
  var el = $(el), ph, o = opts || {};
  if (!el || (el.phevents && !o.reload) || !(ph = (el.attr('placeholder')))) {
    return;
  }

  el.removeAttr('placeholder');

  var pad = {
    'padding': el.css('padding'),
    'margin': '1px'
  };

  if (o.reload) {
    var prev = el.prev();
    if (prev && prev.hasClass('input_back_wrap')) prev.remove();
  }
  var b1 = el.phcont = $('<div class="input_back_wrap no_select"><div class="input_back"><div class="input_back_content">'+ ph +'</div></div></div>').insertBefore(el),
  b = b1.children(':first-child'), c = b.children(':first-child');
  b.css(pad);

  if (browser.msie && !browser.msie8) {
    b.css({marginTop: 1});
  }

  var cv = __phCheck.pbind(el, o.back, o.editable), checkValue = browser.mobile ? cv : function(f, b) {
    setTimeout(cv.pbind(f, b), 0);
  }

  el.phonfocus = function(hid) {
    el.focused = true;
    cur.__focused = el;
    if (hid === true) {
      el.css({backgroundColor: '#FFF'});
      b.hide();
    }
    checkValue(true, false);
  }
  el.phonblur = function() {
    cur.__focused = el.focused = false;
    b.show();
    checkValue(false, true);
  }
  el.phshown = true, el.phanim = null;

  if (el.val() || (o.editable && ((el.textContent !== undefined ? el.textContent : el.innerText) || $('img', el).length))) {
    el.phshown = false;
    b1.hide();
  }

  if (!browser.opera_mobile) {
    b1.on('focus click', function(ev) {
      if (o.editableFocus) {
        setTimeout(o.editableFocus.pbind(el), 0);
        el.phonfocus();
      } else {
        el.blur(); el.focus();
      }
    });
    el.bind('focus'+(o.editable ? ' click' : ''), el.phonfocus);
    el.bind('keydown paste cut input', checkValue);
  }
  el.bind('blur', el.phonblur);
  el.check = checkValue;

  el.getValue = function() {
    return el.val();
  }
  el.setValue = function(v) {
    el.val(v);
    __phCheck(el, o.back, o.editable);
  }
  el.phevents = true;
  el.phonsize = function() {};

  if (o.global) return;

  if (!o.reload) {
    if (!cur.__phinputs) {
      cur.__phinputs = [];
      cur.destroy.push(function() {
        for (var i = 0, l = cur.__phinputs.length; i < l; ++i) {
          //removeData(cur.__phinputs[i]);
        }
      });
    }
    cur.__phinputs.push(el);
  }
}

// Boxes
var _box_guid = 0;
var __bq = {
  _guids: [],
  _boxes: [],
  curBox: 0,

  hideAll: function() {

  },

  hideLast: function(e) {
    if (e) e.stopPropagation();

    if (__bq.count()) {
      var box = __bq._boxes[__bq._guids[__bq.count() - 1]];
      if (__bq.skip) {
        __bq.skip = false;
        return;
      }
      box.hide();
    }
  },
  hideBGClick: function(e) {
    __bq.hideLast();
  },

  count: function() {
    return __bq._guids.length;
  },
  _showLayer: function() {
    $(document.body).css({
      overflow: 'hidden',
      cursor: 'default'
    });

    boxLayerWrap.show().css({
      width: $(window).width(),
      height: $(window).height()
    }).bind('click', __bq.hideBGClick);
    boxLayerBG.show().css({
      height: $(window).height()
    }).bind('click', __bq.hideBGClick);
  },
  _hideLayer: function() {
    $('body').css({
      overflow: 'auto'
    });

    boxLayerBG.hide().unbind('click', __bq.hideBGClick);
    boxLayerWrap.hide().unbind('click', __bq.hideBGClick);
  },
  _show: function(guid) {
    var box = __bq._boxes[guid];
    if (!box) return;
    if (__bq.count()) __bq._boxes[__bq._guids[__bq.count() - 1]].hide();
    __bq.curBox = guid;
    box._show();
    __bq._guids.push(guid);
  },
  _hide: function(guid) {
    var box = __bq._boxes[guid];
    if (!box || __bq._guids[__bq.count() - 1] != guid || !box.isVisible()) return;
    __bq._guids.pop();
    box._hide();
    if (__bq.count()) {
      var prev_guid = __bq._guids[__bq.count() - 1];
      __bq.curBox = prev_guid;
      __bq._boxes[prev_guid]._show();
    }
    else __bq._hideLayer();
  }
};

function curBox() {
  return __bq._boxes[__bq.curBox];
}

function boxPopup(text, header) {
  var $wrap = $('<div class="top_result_baloon_wrap fixed"><div class="top_result_baloon"></div></div>'),
    $bc = $wrap.children();

  if (header)
    $('<div class="top_result_header">'+ header +'</div>').appendTo($bc);
  $('<span>'+ text +'</span>').appendTo($bc);

  $wrap.appendTo('body');
  $wrap.css({
    top: ($(window).height() - $bc.outerHeight()) / 4,
    left: ($(window).width() - $bc.outerWidth()) / 2
  });

  var _hideMe = function(e) {
    $('body').unbind('click', _hideMe);
    $wrap.fadeOut();
  }

  $('body').bind('click', _hideMe);

  clearTimeout(cur._boxPopupTime);
  cur._boxPopupTime = setTimeout(_hideMe, 3000);
}

function boxRefreshCoords(cont) {
  (!cont.hasClass('popup_box_absolute'))
    ? cont.css('marginTop', Math.max(10, ($(window).height() - cont.outerHeight()) / 3))
    : cont.css({top: ($(window).height() - cont.outerHeight()) / 3, left: ($(window).width() - cont.outerWidth()) / 2});
}

function showGlobalPrg() {
  var $bx = $('#box_loader');
  $('#box_layer_wrap').show();
  $bx.show();
  $bx.css({
    top: ($(window).height() - $bx.height()) / 3,
    left: ($(window).width() - $bx.width()) / 2
  });
}
function hideGlobalPrg() {
  $('#box_layer_wrap').hide();
  $('#box_loader').hide();
}

function Box(opts, dark) {
  var defaults = {
    bodyStyle: '',
    title: false,
    width: 410,
    height: 'auto',
    selfDestruct: true,
    progress: false,
    hideButtons: false,
    hideOnBGClick: false,
    onShow: false,
    onHide: false
  };

  opts = $.extend(defaults, opts);

  var boxContainer, boxBG, boxLayout;
  var boxTitleWrap, boxTitle, boxCloseButton, boxBody;
  var boxControlsWrap, boxControls, boxProgress, boxControlsText;
  var guid = _box_guid++, visible = false;

  if (!opts.progress) opts.progress = 'box_progress'+ guid;

  var controlsStyle = (opts.hideButtons) ? ' style="display:none"' : '';
  boxContainer = $('<div/>').addClass('popup_box_container');
  if (dark) boxContainer.addClass('box_dark');
  if (opts.absolute) boxContainer.addClass('popup_box_absolute');
  boxContainer.html('\
<div class="box_layout" onclick="__bq.skip=true;">\
<div class="box_title_wrap"><div class="box_x_button">'+(dark ? 'Закрыть' : '')+'</div><div class="box_title"></div></div>\
<div class="box_body" style="' + opts.bodyStyle + '"></div>\
<div class="box_controls_wrap"' + controlsStyle + '><div class="box_controls">\
<table cellspacing="0" cellpadding="0" class="fl_r"><tr></tr></table>\
<div class="progress" id="' + opts.progress + '"></div>\
<div class="box_controls_text"></div>\
</div></div>\
</div>');
  boxContainer.hide();

  boxLayout = boxContainer.children().first();
  boxTitleWrap = boxLayout.children().first();
  boxCloseButton = boxTitleWrap.children().first();
  boxTitle = boxCloseButton.next();

  if (opts.noCloseButton) boxCloseButton.hide();

  boxBody = boxTitleWrap.next();

  boxControlsWrap = boxBody.next();
  boxControls = boxControlsWrap.children(':first-child');
  boxButtons = boxControls.first();
  boxProgress = boxButtons.next();
  boxControlsText = boxContainer.find('div.box_controls_text');

  boxContainer.appendTo((opts.absolute) ? 'body' : boxLayer);

  if (opts.buttons) {
    $.each(opts.buttons, function(i, b) {
      addButton(b);
    });
  }

  refreshBox();
  boxRefreshCoords(boxContainer);

  function refreshBox() {
    if (opts.title) {
      boxTitle.html(opts.title);
      boxBody.removeClass('box_no_title');
      boxTitleWrap.show();
    }
    else {
      boxBody.addClass('box_no_title');
      boxTitleWrap.hide();
    }

    boxContainer.css({width: opts.width, height: opts.height});
  }

  var destroyMe = function() {
    boxContainer.remove();
    delete __bq._boxes[guid];
  }

  var hideMe = function() {
    if (!visible) return;
    visible = false;

    if (opts.hideOnBGClick) {
      $(document).unbind('click', __bq.hideBGClick);
    }

    if ($.isFunction(opts.onHide)) opts.onHide();

    if (opts.selfDestruct) destroyMe();
    else boxContainer.hide();
  }

  var showMe = function() {
    if (visible || !__bq._boxes[guid]) return;
    visible = true;

    if (!opts.absolute || opts.showLayer) __bq._showLayer();
    boxContainer.show();
    boxRefreshCoords(boxContainer);
    if ($.isFunction(opts.onShow)) opts.onShow();
  }

  boxCloseButton.click(function(e) {
    __bq.skip = false;
    __bq.hideLast(e);
  }).mouseenter(function(e) {
    $(this).stop(true).animate({backgroundColor: '#ffffff'}, 300);
  }).mouseleave(function(e) {
    $(this).stop(true).animate({backgroundColor: '#9DB7D4'}, 300);
  });

  function addButton(button) {
    var title = button.title,
      onclick = (button.onclick) ? button.onclick : null,
      type = (button.type) ? button.type : 'blue',
      row = boxControls.find('table tr');

    if (type == 'no') type = 'gray';
    if (type == 'yes') type = 'blue';

    var btn = $('<td></td>').html('<div class="button_'+ type +'"><button>'+ title +'</button></div>').prependTo(row);

    if (type == 'gray') btn.find('button').click(function() {
      curBox().hide();
    });
    if (onclick) btn.find('button').click(onclick);
  }

  function removeButtons() {
    boxControls.find('table td > div').remove();
  }

  var retBox = __bq._boxes[guid] = {
    guid: guid,
    _show: showMe,
    _hide: hideMe,

    bodyNode: boxBody,

    show: function() {
      __bq._show(guid);
      return this;
    },
    progress: boxProgress,
    showProgress: function() {
      boxControlsText.hide();
      $('#box_progress'+ guid).show();
    },
    hideProgress: function() {
      boxControlsText.show();
      $('#box_progress'+ guid).hide();
    },

    hide: function() {
      __bq._hide(guid);
      return this;
    },

    isVisible: function() {
      return visible;
    },
    bodyHeight: function() {
      return boxBody.height();
    },

    content: function(html) {
      boxBody.html(html);
      boxRefreshCoords(boxContainer);
      refreshBox();
      return this;
    },

    controlsText: function(html) {
      boxControlsText.html(html);
      return this;
    },

    // Add button
    addButton: function(label, onclick, type, returnBtn) {
      var btn = addButton({title: label, onclick: onclick ? onclick : this.hide, type: type});
      return (returnBtn) ? btn : this;
    },

    setButtons: function(yes, onYes, no, onNo) {
      var b = this.removeButtons();
      if (!yes) return b.addButton(box_close);
      if (no) b.addButton(no, onNo, 'no');
      return b.addButton(yes, onYes);
    },

    lockButton: function(idx) {
      lockButton($(boxControls.find('table td > div')[idx]));
    },
    unlockButton: function(idx) {
      unlockButton($(boxControls.find('table td > div')[idx]));
    },

    // Remove buttons
    removeButtons: function() {
      removeButtons();
      return this;
    },

    // Update box options
    setOptions: function(newOptions) {
      if (opts.hideOnBGClick) {
        $(document).unbind('click', __bq.hideBGClick);
      }
      opts = $.extend(opts, newOptions);
      boxBody.css(opts.bodyStyle);
      if (opts.hideOnBGClick) {
        $(document).bind('click', __bq.hideBGClick);
      }
      boxControlsWrap.toggle(!opts.hideButtons);
      refreshBox();
      boxRefreshCoords(boxContainer);
      return this;
    },
    evalBox: function(js, url, params) {
      var scr = '((function() { return function() { var box = this; ' + (js || '') + ';}; })())'; // IE :(
      if (__debugMode) {
        var fn = eval(scr);
        fn.apply(this, [url, params]);
      } else try {
        var fn = eval(scr);
        fn.apply(this, [url, params]);
      } catch (e) {
        topError(e, {dt: 15, type: 7, url: url, query: params ? obj2q(params) : undefined, js: js});
      }
    },

    destroy: destroyMe
  };
  return retBox;
}

function showBox(url, params, options) {
  var opts = options || {},
    boxParams = opts.params || {},
    box = new Box(boxParams, opts.dark), p = {
      onDone: function(r) {
        if (!box.isVisible()) return;
        try {
          boxLayerBG.show();
          box.setOptions({title: r.title, hideButtons: boxParams.hideButtons || false});
          if (opts.showProgress) {
            box.show();
          } else {
            box.bodyNode.show();
          }
          box.content(r.html);
          box.evalBox(r.js, url, params);
          if (opts.onDone) opts.onDone();
        }
        catch(e) {
          topError(e, {dt: 15, type: 103, url: url, query: obj2q(params), answer: r});
          if (box.isVisible()) box.hide();
        }
      },
      onFail: function(error) {
        box.failed = true;
        setTimeout(box.hide, 0);
        if ($.isFunction(opts.onFail)) return opts.onFail(error);
        // TODO: не факт, что пригодится
        else {
          try {
            var r = $.parseJSON(error.responseText);
          } catch(e) {}
          showFastBox(getLang('global_error'), (r && r.html) || getLang('global_page_error'));
        }
      },
      stat: opts.stat
    };

  box.setOptions({title: false, hideButtons: true}).show();
  if (__bq.count() < 2) {
    boxLayerBG.hide();
  }
  box.bodyNode.hide();
  p.showProgress = function() {
    boxLoader.show();
    //boxRefreshCoords(boxLoader);
  }
  p.hideProgress = function() {
    boxLoader.hide();
  }

  box.removeButtons().addButton(getLang('global_close'));

  $.extend(params, {
    box: true
  });

  ajax.post(url, params, p);
  return box;
}

function showFastBox(o, c, yes, onYes, no, onNo) {
  return new Box((typeof o == 'string') ? {title: o} : o).content(c).setButtons(yes, onYes, no, onNo).show();
}

// DD Menu
var __dd_guid = 0;

function DDMenu(el, items, options) {
  var el = $(el) || $('#'+ el), opts = options || {};
  if (!el) return;

  var m, m_header_wrap, m_header, m_body, m_rows;
  m = $('<div/>').attr({id: 'dd_menu'+ __dd_guid++}).addClass('dd_menu');
  if (opts.header) {
    m_header_wrap = $('<div class="dd_menu_header"><div>'+ el.text() +'</div></div>').appendTo(m);
    m_header = m_header_wrap.children();
  }
  m_body = $('<div class="dd_menu_body"></div>').appendTo(m);
  m_body.html('<table cellspacing="0" cellpadding="0"><tr>' +
    '<td class="dd_menu_shad_l"><div></div></td>' +
    '<td>' +
    '<div class="dd_menu_rows">' +
    '<div class="dd_menu_rows2"></div>' +
    '</div> ' +
    '<div class="dd_menu_shad_b"></div>' +
    '<div class="dd_menu_shad_b2"></div>' +
    '</td>' +
    '<td class="dd_menu_shad_r"><div></div></td>' +
    '</tr></table>');

  m_rows = m_body.find('div.dd_menu_rows2');

  var m_out = null;

  var hideMenu = function() {
    m.hide();
  }

  var outMenu = function() {
    clearTimeout(m_out);
    m_out = setTimeout(hideMenu, 400);
  }
  var overMenu = function() {
    clearTimeout(m_out);
  }

  var showMenu = function() {
    var top = 0, left = 0;

    if (m_header) {
      top = el.offset().top - 2 - parseInt(m_header.css('paddingTop'));
      left = el.offset().left - 2 - parseInt(m_header.css('paddingLeft'));
    }
    else {
      top = el.offset().top + el.outerHeight();
      left = el.offset().left;
    }
    m.show().css({
      top: top,
      left: left
    });
    m_body.css({
      right: 'auto',
      top: ((m_header) ? m_header.outerHeight() + 1 : 0)
    });
    m_rows.css('width', (m_header) ? parseInt(m_header.outerWidth()) : 'auto');

    $(m).bind('mouseleave', outMenu).bind('mouseenter', overMenu);
    $(el).bind('mouseleave', outMenu).bind('mouseenter', overMenu);
  }

  var addItems = function(items) {
    $.each(items, function(i, item) {
      var html = $.isArray(item) ? item[1] : item, value = $.isArray(item) ? item[0] : item;
      $('<a/>').html(html).bind('click', clickItem.pbind(value)).appendTo(m_rows);
    });
  }

  var clickItem = function(i) {
    if (m_header) {
      m_header.html(items[i]);
    }
    opts.click(i);
    hideMenu();
  }

  addItems(items);
  el.bind('click', showMenu);
  m.appendTo('body');

  return {
    show: showMenu,
    hide: hideMenu,
    add: addItems
  };
}

// Dropdown
function Dropdown(el, options) {
  el = document.getElementById(el);
  if (!el) return;
  el = $(el);

  var _defaults = {
    divider: false,
    width: 140,
    height: 250,
    big: false,
    tokens: false,
    autocomplete: false
  };
  var wrap = el.parent(), opts = $.extend({}, _defaults, options), disabled, cont_id = A.uuid++,
  cont = $('\
  <div id="container'+ cont_id +'" class="selector_container'+ ((opts.autocomplete) ? '' : ' dropdown_container') + ''+ ((opts.big) ? ' big' : '') +'">\
    <table cellspacing="0" cellpadding="0" class="selector_table">\
      <tr>\
        <td class="selector">\
          <span class="selected_items clear_fix"></span>\
          <input type="text" class="selector_input">\
        </td>\
        <td class="selector_dropdown">&nbsp;</td>\
      </tr>\
    </table>\
    <div class="results_container'+ ((opts.divider) ? ' dividing_line' : '') +'" onclick="event.cancelBubble=true;">\
      <div class="result_list"><ul></ul></div>\
      <div class="result_list_shadow">\
        <div class="shadow1"></div>\
        <div class="shadow2"></div>\
      </div>\
    </div>\
  </div>').appendTo(wrap);

  var selector_cont = cont.find('td.selector'),
    selector_table = cont.find('table.selector_table'),
    selected_items_cont = cont.find('span.selected_items'),
    selector_input = cont.find('input.selector_input'),
    selector_dropdown = cont.find('td.selector_dropdown'),
    result_list_cont = cont.find('div.result_list'),
    result_list_sh = result_list_cont.next(),
    result_list = cont.find('div.result_list ul');

  el.addClass('resultField').attr('type', 'hidden').appendTo(selector_cont);

  // functions
  var self = this, shown = false, curActiveItem = null;
  this.showSelected = function(value, selected) {
    if (opts.autocomplete) {
      if (opts.tokens) {
        selector_input.prop('placeholder', opts.label);
      } else {
        selector_input.val(value);
      }
    } else {
      selector_input.val(value);
      if (selected) selector_input.css({color: ''}).addClass('selected');
      else selector_input.css({color: '#777'});
    }
  }
  this.showMenu = function() {
    if (shown == true) return;

    result_list_cont.show();
    result_list_sh.show().css({width: (opts.big) ? opts.width - 2 : opts.width, marginTop: result_list_cont.innerHeight()});
    self.activateItem();
    shown = true;
    setTimeout(function() {
      $(document).bind('mousedown', self.hideMenu);
      $(document).bind('keydown', self.keyEvent);
    }, 1);

    if (opts.autocomplete)
      selector_input.focus();
  }
  this.hideMenu = function(e) {
    if ($(e.target).hasClass('result_list')) return;

    result_list_cont.hide();
    result_list_sh.hide();
    shown = false;
    $(document).unbind('mousedown', self.hideMenu);
    $(document).unbind('keydown', self.keyEvent);
  }
  this.disableMenu = function(label) {
    cont.addClass('disabled');
    $('<div/>').addClass('hide_mask').css({
      position: 'absolute',
      opacity: 0,
      width: cont.outerWidth(),
      height: cont.outerHeight(),
      marginTop: -cont.outerHeight()
    }).appendTo(cont);
    self.showSelected(label || opts.disabledLabel || opts.label, false);
    disabled = true;
  }
  this.enableMenu = function() {
    cont.removeClass('disabled');
    cont.children('div.hide_mask').remove();
    self.showSelected(opts.label, false);
    disabled = false;
  }
  this.renderItems = function(items) {
    result_list.html('');
    if (opts.label && !opts.autocomplete) {
      self.createListItem(opts.label, '').appendTo(result_list);
    }
    if (!items.length) {
      result_list_cont.removeClass('result_list_scrollable').css({height: 'auto'});
      return;
    }
    $.each(items, function(i, item) {
      self.createListItem(
        ($.isArray(item)) ? item[1] : item,
        ($.isArray(item)) ? item[0] : item,
        ($.isArray(item)) ? item[2] : null,
        ($.isArray(item)) ? item[3] : null,
        ($.isArray(item)) ? item[4] : null,
        ($.isArray(item)) ? item[5] : null,
        ($.isArray(item)) ? item[6] : null
      ).appendTo(result_list);
    });

    result_list.children().first().addClass('first');
    result_list.children().last().addClass('last');

    if (items.length > 14) {
      result_list_cont.addClass('result_list_scrollable').height(opts.height);
    } else {
      result_list_cont.removeClass('result_list_scrollable').css({height: 'auto'});
    }
  }
  this.createListItem = function(label, value, important, nonselectable, level, description, thumb) {
    var item = $('<li val="'+ value +'">'+ ((thumb) ? '<b class="fl_l thumb"><img src="'+ thumb +'" /></b>' : '') + ((important) ? '<b>' : '') + label + ((important) ? '</b>' : '') + ((description) ? '<span>'+ description + '</span>' : '') +'</li>');
    if (level) item.addClass('level'+ level);
    if (!nonselectable) {
      item.bind('mouseover mousedown', function(e) {
        if (e.type == 'mouseover') {
          curActiveItem && curActiveItem.removeClass('active');
          curActiveItem = $(this).addClass('active');

          var ch = curActiveItem.outerHeight() - (curActiveItem.hasClass('last') ? 0 : 1), idx = curActiveItem.index(), y1 = idx * ch, y2 = (idx + 1) * ch,
            rh = result_list_cont.height(), t1 = result_list_cont.scrollTop(), t2 = t1 + rh;

          if (y1 < t1) result_list_cont.scrollTop(y1);
          else if (y2 > t2) result_list_cont.scrollTop(y2 - rh + 1);
        }
        else if (e.type == 'mousedown') {
          self.selectItem(e, label, value);
        }
      });
    } else {
      item.prop('sel', 0);
    }
    return item;
  }
  this.activateItem = function() {
    if ($.trim(el.val()) == '') result_list.children().first().mouseover();
    else {
      result_list.children('[val="'+ el.val() +'"]').mouseover();
    }
  }
  this.selectItem = function(e, label, value) {
    if (opts.tokens) {
      var values = el.val() ? el.val().split(',') : [];
      values.push(value);
      el.val(values.join(','));

      self.addToken(e, label, value);
    } else {
      el.val(value);
      self.showSelected(label, (label == opts.label) ? false : true);
    }

    if (e && opts.change) opts.change(el.val());
    if (e) self.hideMenu(e);
  }
  this.addToken = function(e, label, value) {
    var token = $('<div id="bit_'+ cont_id +'_'+ value +'" class="token">\
        <span class="l">'+ label +'</span>\
        <span class="x"></span>\
      </div>').appendTo(selected_items_cont);
    token.bind('mousedown mouseenter mouseleave', function(ev) {
      if (ev.type == 'mousedown') {
        ev.stopPropagation();
        self.removeToken(ev, label, value);
      } else if (ev.type == 'mouseenter') {
        token.addClass('token_hover');
      } else if (ev.type == 'mouseleave') {
        token.removeClass('token_hover');
      }
    });
    token.css({maxWidth: selector_input.width() - token.find('span.x').outerWidth()});

    self.checkInputVisibility();
    result_list.find('[val="'+ value +'"]').hide();
  }
  this.removeToken = function(e, label, value) {
    $('#bit_'+ cont_id +'_'+ value).remove();
    var values = el.val().split(',');
    $.each(values, function(i, v) {
      if (v == value) values.splice(i, 1);
    });
    el.val(values.join(','));

    self.checkInputVisibility();
    self.hideMenu(e);
    result_list.find('[val="'+ value +'"]').show();

    if (opts.change) opts.change(el.val());
  }
  this.checkInputVisibility = function() {
    if (opts.autocomplete) return;

    if (selected_items_cont.find('div.token').length) {
      selector_input.hide();
    } else {
      selector_input.show();
    }
  }
  this.clearElem = function() {
    self.selectItem(opts.label, 0);
  }
  this.findLabelByValue = function(value) {
    var founded = false;
    $.each(opts.items, function(i, item) {
      if ($.isArray(item) && item[0] == value) {
        founded = item[1];
        return;
      }
      else if (!$.isArray(item) && item == value) {
        founded = item;
        return;
      }
    });

    return founded;
  }
  this.chooseFirst = function() {
    var f = opts.items[0];
    ($.isArray(f)) ? this.selectItem(null, f[1], f[0]) : this.selectItem(null, f, f);
  }
  this.keyEvent = function(e) {
    if (e.type == 'keydown') {
      if (e.which == 38) {
        e.preventDefault();

        var founded = false, prev = curActiveItem.prev();
        while (!founded) {
          if (prev.prop('sel') != "0") {
            founded = true;
            prev.mouseover();
          }
          else prev = prev.prev();
        }
        //result_list.find('li.active').prevAll().last('[sel!="0"]').mouseover();
      }
      else if (e.which == 40) {
        e.preventDefault();

        var founded = false, next = curActiveItem.next();
        while (!founded) {
          if (next.prop('sel') != "0") {
            founded = true;
            next.mouseover();
          }
          else next = next.next();
        }
        //result_list.find('li.active').nextAll().first('[sel!="0"]').mouseover();
      }
      else if (e.which == 13) curActiveItem.mousedown();
    }
  }
  this.reset = function() {
    el.val('');
    self.showSelected(opts.label, false);
  }
  this.destroy = function() {
    cont.remove();
  }

  // bindings
  if (!opts.big) {
    cont.bind('mouseenter mouseleave', function(e) {
      if (!disabled) {
        if (e.type == 'mouseenter') {
          selector_dropdown.stop(true).animate({
            backgroundColor: '#e1e8ed',
            borderLeftColor: '#d2dbe0'
          });
        } else if (e.type == 'mouseleave') {
          selector_dropdown.stop(true).animate({
            backgroundColor: '#fff',
            borderLeftColor: '#fff'
          })
        }
      }
    });
  }
  selector_table.bind('mousedown', this.showMenu.bind(this));
  selector_input.bind('focus', this.showMenu.bind(this));

  // ending
  if (!opts.autocomplete) selector_input.attr('readonly', true);
  cont.width(opts.width);
  selector_dropdown.width(opts.big ? 27 : 16);
  selector_input.width(opts.width - ((opts.big) ? 38 : 27)); //16 arrow - 1 border - 1 border
  result_list_cont.width((opts.big) ? opts.width - 2 : opts.width);

  if (opts.items) this.renderItems(opts.items);

  if (el.val() == 0 || !el.val()) {
    (opts.label) ? this.showSelected(opts.label, false) : this.chooseFirst();
  } else {
    if (opts.tokens) {
      var values = el.val().split(',');
      $.each(opts.items, function(i, item) {
        $.each(values, function(i2, v) {
          if (v == item[0])
            self.addToken(null, item[1], item[0]);
        });
      });
      this.showSelected();
    } else {
      var lbv = this.findLabelByValue(el.val());
      if (lbv) this.showSelected(lbv, true);
      else if (opts.label) this.showSelected(opts.label, false);
      else this.chooseFirst();
    }
  }

  result_list_cont.hide();
  if (opts.disabled) this.disableMenu(opts.disabledLabel);

  // Autocomplete part
  if (opts.autocomplete) {
    var ac_timeout = null;

    selector_input.bind('keyup', function(e) {
      if (e.which == 38 || e.which == 40) {
        e.type = 'keydown';
        self.keyEvent(e);
        return;
      }
      if(e.which != 8 && e.which != 46 && (e.which < 48 || e.which > 90)) //65=a, 90=z
        return false;

      var input = $(this);
      if ($.trim(input.val()).length == 0) {
        //$el.data('a-items', null);
        opts.items = [];
        self.renderItems(opts.items);
        return;
      }

      clearTimeout(ac_timeout);
      ac_timeout = setTimeout(function() {
        var query = opts.query.replace(/\%s/i, encodeURIComponent(input.val()));
        ajax.post(query, {}, {
          onDone: function(r) {
            //$el.data('a-items', r);
            opts.items = r;
            self.renderItems(opts.items);
            shown = false;
            self.showMenu();
          }
        });
      }, 300);
    });
  }

  return {
    reset: this.reset,
    clear: this.clearElem,
    select: this.selectItem,
    render: this.renderItems,
    show: this.showMenu,
    hide: this.hideMenu,
    disable: this.disableMenu,
    enable: this.enableMenu,
    destroy: this.destroy
  };
}

// Calendar
function Calendar(el, options) {
  var id = el;
  el = document.getElementById(el);
  if (!el) return;
  el = $(el);

  var defaults = {
    width: 140,
    default: true
  };
  var opts = $.extend({}, defaults, options), wrap = $(el).parent();

  this.container = $('\
  <div id="'+ id +'_calendar_container" class="calendar_container">\
    <div class="calendar_control">\
      <input readonly="1" type="text" class="calendar_text" id="'+ id +'_calendar_input">\
    </div>\
    <div id="'+ id +'_cal_box" class="cal_box" style="display:none" onclick="event.cancelBubble=true;">\
      <div id="'+ id +'_cal_div" class="cal_div">\
        <div>\
        </div>\
      </div>\
    </div>\
  </div>\
  ').appendTo(wrap);

  this.control = this.container.find('div.calendar_control');
  this.input = this.container.find('#'+ id + '_calendar_input');
  this.cal_box = this.container.find('#'+ id +'_cal_box');
  this.cal_div = this.container.find('#'+ id +'_cal_div > div');

  el.prependTo(this.container);

  // renderings
  this.renderHead = function(days) {
    var head = $('\
    <table class="cal_table_head" cellspacing="0" cellpadding="0">\
      <tr>\
        <td class="month_arr">\
          <a class="arr left"></a>\
        </td>\
        <td align="center" class="month">\
          <a class="cal_month_sel">'+ ((days) ? getLang('global_month')[curMonth] +' ' : '') +''+ curYear +'</a>\
        </td>\
        <td class="month_arr">\
          <a class="arr right"></a>\
        </td>\
      </tr>\
    </table>\
    ');

    head.find('a.left').bind('click', function(e) {
      if (curMonth == 1) {
        curMonth = 12;
        curYear--;
      } else curMonth--;

      self.renderMonth.apply(self)
    });
    head.find('a.right').bind('click', function(e) {
      if (curMonth == 12) {
        curMonth = 1;
        curYear++;
      } else curMonth++;

      self.renderMonth.apply(self);
    });

    return head;
  }

  this.renderMonth = function() {
    if (this.month_table) this.month_table.remove();
    this.month_table = $('<table class="cal_table" cellspacing="0" cellpadding="0"></table>');

    var head_wrap = $('<tr><td colspan="7"></td></tr>').appendTo(this.month_table);
    this.renderHead(1).appendTo(head_wrap.children());

    var weeks_wrap = $('<tr/>').appendTo(this.month_table);
    for(var i=0; i <= 6; i++) {
      $('<td class="daysofweek">'+ getLang('global_dayofweek')[i] +'</td>').appendTo(weeks_wrap);
    }

    var prevMonth = new Date(curYear, curMonth - 1, 1),
      daysNum = new Date(curYear, curMonth, 0).getDate(),
      firstDay = prevMonth.getDay(),
      date = 1;

    firstDay = (firstDay == 0) ? 6 : firstDay - 1;
    var row = $('<tr/>').appendTo(this.month_table);
    for(var d = 0; d < 7; d++) {
      if (d < firstDay) $('<td class="day no_month">&nbsp;</td>').appendTo(row);
      else {
        this.renderDay(date).appendTo(row);
        date++;
      }
    }
    var cellCount = 1;
    for(;date <= daysNum; date++) {
      if (cellCount == 1) row = $('<tr/>').appendTo(this.month_table);
      this.renderDay(date).appendTo(row);
      cellCount++;
      if (cellCount > 7) cellCount = 1;
    }
    if (cellCount > 1) {
      for(;cellCount <= 7; cellCount++) {
        $('<td class="day no_month">&nbsp;</td>').appendTo(row);
      }
    }

    this.month_table.appendTo(this.cal_div);
  }
  this.renderDay = function(date) {
    var day = $('<td>'+ date +'</td>'), nowDay = new Date().getDate(), nowMonth = new Date().getMonth() + 1, nowYear = new Date().getFullYear();
    day.addClass('day');

    if ((nowDay > date && nowMonth == curMonth && nowYear == curYear) || (nowMonth > curMonth && nowYear == curYear) || (nowYear > curYear)) day.addClass('past_day');
    else if (nowDay == date && nowMonth == curMonth && nowYear == curYear) day.addClass('today');
    if (selDay == date && curMonth == selMonth && curYear == selYear) day.addClass('sel');

    day.bind('mouseover mouseout click', function(e) {
      if (e.type == 'mouseover') {
        $(this).addClass('hover');
      }
      else if (e.type == 'mouseout') {
        $(this).removeClass('hover');
      }
      else if (e.type == 'click') {
        self.selectDay(date);
      }
    });
    return day;
  }
  this.selectDay = function(date) {
    selDay = date; selMonth = curMonth; selYear = curYear;
    var date_val = el.val(), dt = date_val.split(' ');
    dt[0] = selYear + '-' + ((selMonth < 10) ? '0'+ selMonth : selMonth) + '-' + ((date < 10) ? '0'+ date : date);
    el.val(dt.join(' '));

    if (opts.onSelect) opts.onSelect();

    this.renderMonth();
    this.renderValue();
    this.hide();
  }
  this.renderValue = function() {
    this.input.val(parseInt(selDay) + ' '+ getLang('global_cal_month')[selMonth] +' '+ selYear);
  }

  // handlers
  var self = this;
  this.show = function() {
    if (selMonth != curMonth && selYear == curYear || selYear != curYear) {
      curMonth = selMonth;
      curYear = selYear;
      this.renderMonth();
    }

    this.cal_box.show();

    setTimeout(function() {
      $(document).bind('click', self.hide);
    }, 1);
  }
  this.hide = function(e) {
    //if ($(e.target).closest('div.cal_div')) return;

    self.cal_box.hide();
    $(document).unbind('click', self.hide);
  }

  // events
  this.control.bind('click', this.show.bind(this));

  // finish
  this.container.width(opts.width);
  this.input.width(opts.width - 25);

  var date_val = el.val(), curDay, curMonth, curYear, selDay, selMonth, selYear;
  if (date_val) {
    var dt = date_val.split(' '), date = dt[0].split('-');
    selDay = curDay = parseInt(date[2]); selMonth = curMonth = parseInt(date[1]); selYear = curYear = parseInt(date[0]);
  }
  else {
    selDay = curDay = new Date().getDate(); selMonth = curMonth = new Date().getMonth() + 1; selYear = curYear = new Date().getFullYear();
  }

  this.renderMonth();
  if (opts.default) this.renderValue();
  else if (!opts.default && el.val() != '') this.renderValue();
}

// Checkbox
function Checkbox(el, options) {
  var id = el;
  el = document.getElementById(el);
  if (!el) return;
  el = $(el);

  var defaults = {
    width: 140
  };
  var opts = $.extend({}, defaults, options), wrap = $(el).parent();

  this.container = $('\
  <div id="container'+ (A.uuid++) +'" class="checkbox_container">\
    <table cellspacing="0" cellpadding="0">\
      <tr>\
        <td class="checkbox"><div class="checkbox_off"></div></td>\
        <td class="checkbox_label"></td>\
      </tr>\
    </table>\
  </div>\
  ').appendTo(wrap);

  this.checkbox = this.container.find('div.checkbox_off');
  this.checkbox_label = this.container.find('td.checkbox_label');
  this.checkbox_label.text(opts.label || 'Checkbox');

  el.appendTo(this.checkbox_label);

  // handlers
  this.updateCheckbox = function(e) {
    if (e.type == 'mouseover') {
      this.checkbox.prop('class', 'checkbox_'+ ((el.val() == '1') ? 'on' : 'off') +'_over');
    } else if (e.type == 'mouseout') {
      this.checkbox.prop('class', 'checkbox_'+ ((el.val() == '1') ? 'on' : 'off') +'');
    } else {
      this.checkbox.prop('class', 'checkbox_'+ ((el.val() == '1') ? 'on' : 'off') +'');
    }
  }

  // bindings
  var self = this;
  this.container.bind('mouseover mouseout mousedown', function(e) {
    if (e.type == 'mousedown') {
      el.val((el.val() == '1') ? 0 : 1);
      self.updateCheckbox({type: 'mouseover'});
      if (opts.change) opts.change(el.val());
    }
    else self.updateCheckbox.call(self, e);
  });

  // finish
  this.updateCheckbox({});
}

// Tooltips
var tooltips = {
  show: function(el, text, pos) {
    if ($(el).data('tooltip')) return;

    var tt = $('<div class="ttb"><div class="toup"><div class="ttb_cont"><div class="tt_text"></div></div><div class="bottom_pointer"></div></div></div>')
      .appendTo('body'),
      tt_text = tt.find('div.tt_text');
    tt_text.text(text);
    tt.css({
      display: 'none',
      opacity: 0,
      position: 'absolute',
      top: $(el).offset().top - 28,
      left: $(el).offset().left - 13
    });

    $(el).data('tooltip', tt)
      .mouseenter(function() {
        var tt = $(this).data('tooltip');
        tt.css('display', 'block').stop(true).animate({opacity: 1}, 'fast');
      })
      .mouseleave(function() {
        var tt = $(this).data('tooltip');
        tt.stop(true).animate({opacity: 0}, 'fast', function() { tt.css('display', 'none') });
      });

    $(el).mouseenter();
  },
  rePositionAll: function() {

  },
  hideAll: function() {
    $('div.ttb').remove();
  }
};

// Autocomplete
function autocomplete(el, opts) {
  el = document.getElementById(el);
  if (!el) return;
  var $el = $(el);

  var wrap = $el.parent(),
    cont = $('\
      <div class="results_container'+ ((opts.divider) ? ' dividing_line' : '') +'" onclick="event.cancelBubble=true;">\
        <div class="result_list"><ul></ul></div>\
        <div class="result_list_shadow">\
          <div class="shadow1"></div>\
          <div class="shadow2"></div>\
        </div>\
      </div>\
      ').appendTo(wrap);

  var result_list_cont = cont.find('div.result_list'),
    result_list = result_list_cont.find('ul'),
    result_list_sh = cont.find('div.result_list_shadow');

  // functions
  var self = this, shown = false, curActiveItem = null;
  this.showMenu = function() {
    if (shown == true) return;

    result_list_cont.show();
    result_list_sh.show().css({width: $el.outerWidth(), marginTop: result_list_cont.innerHeight()});
    self.activateItem();
    shown = true;
    setTimeout(function() {
      $(document).bind('mousedown', self.hideMenu);
      $(document).bind('keydown', self.keyEvent);
    }, 1);
  }
  this.hideMenu = function(e) {
    if ($(e.target).hasClass('result_list')) return;

    result_list_cont.hide();
    result_list_sh.hide();
    shown = false;
    $(document).unbind('mousedown', self.hideMenu);
    $(document).unbind('keydown', self.keyEvent);
  }
  this.renderItems = function() {
    result_list.html('');

    var items = $el.data('a-items');
    if (!items || (items && !items.length)) {
      return;
    }
    $.each(items, function(i, item) {
      self.createListItem(item.title, i, item.description, item.image).appendTo(result_list);
    });

    result_list.children().first().addClass('first');
    result_list.children().last().addClass('last');

    if (items.length > 14) {
      result_list_cont.addClass('result_list_scrollable').height(opts.height);
    }
  }
  this.createListItem = function(label, index, description, image) {
    var item = $('<li val="'+ index +'">'+ label + ((description) ? '<span>'+ description + '</span>' : '') +'</li>');
    item.bind('mouseover mousedown', function(e) {
      if (e.type == 'mouseover') {
        curActiveItem && curActiveItem.removeClass('active');
        curActiveItem = $(this).addClass('active');

        var ch = curActiveItem.outerHeight() - (curActiveItem.hasClass('last') ? 0 : 1), idx = curActiveItem.index(), y1 = idx * ch, y2 = (idx + 1) * ch,
          rh = result_list_cont.height(), t1 = result_list_cont.scrollTop(), t2 = t1 + rh;

        if (y1 < t1) result_list_cont.scrollTop(y1);
        else if (y2 > t2) result_list_cont.scrollTop(y2 - rh + 1);
      }
      else if (e.type == 'mousedown') {
        self.selectItem(e, label, index);
      }
    });

    return item;
  }
  this.activateItem = function() {
    result_list.children().first().mouseover();
  }
  this.selectItem = function(e, label, index) {
    if (e && opts.handler) {
      var items = $el.data('a-items');
      opts.handler(items[index]);
    }
    if (e) self.hideMenu(e);
  }
  this.clearElem = function() {
    self.selectItem(opts.label, 0);
  }
  this.keyEvent = function(e) {
    if (e.type == 'keydown') {
      if (e.which == 38) {
        e.preventDefault();

        var founded = false, prev = curActiveItem.prev();
        while (!founded) {
          if (prev.prop('sel') != "0") {
            founded = true;
            prev.mouseover();
          }
          else prev = prev.prev();
        }
        //result_list.find('li.active').prevAll().last('[sel!="0"]').mouseover();
      }
      else if (e.which == 40) {
        e.preventDefault();

        var founded = false, next = curActiveItem.next();
        while (!founded) {
          if (next.prop('sel') != "0") {
            founded = true;
            next.mouseover();
          }
          else next = next.next();
        }
        //result_list.find('li.active').nextAll().first('[sel!="0"]').mouseover();
      }
      else if (e.which == 13) curActiveItem.mousedown();
    }
  }
  this.destroy = function() {
    cont.remove();
  }

  // bindings
  var ac_timeout = null;

  $el.bind('focus blur', function(e) {
    if (e.type == 'focus') {
      self.showMenu();
    } else if (e.type == 'blur') {
      self.hideMenu(e);
    }
  });
  $el.bind('keyup', function(e) {
    if (e.which == 38 || e.which == 40) {
      e.type = 'keydown';
      self.keyEvent(e);
      return;
    }
    if(e.which != 8 && e.which != 46 && (e.which < 48 || e.which > 90)) //65=a, 90=z
      return false;

    var input = $(this);
    if ($.trim(input.val()).length == 0) {
      $el.data('a-items', null);
      self.renderItems();
      return;
    }

    clearTimeout(ac_timeout);
    ac_timeout = setTimeout(function() {
      var query = opts.query.replace(/\%s/i, encodeURIComponent(input.val()));
      ajax.post(query, {}, {
        onDone: function(r) {
          $el.data('a-items', r);
          self.renderItems();
          shown = false;
          self.showMenu();
        }
      });
    }, 300);
  });

  // ending
  result_list_cont.width($el.outerWidth());
  result_list_cont.hide();
}

try{stManager.done('ui_controls.js');}catch(e){}