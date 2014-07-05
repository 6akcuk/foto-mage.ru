if (A.al == 3 && !history.pushState) A.al = 2;

window.__debugMode = false;

var cur = {destroy: [], nav: []};

for (var i in StaticFiles) {
  var f = StaticFiles[i];
  f.t = (i.indexOf('.css') != -1) ? 'css' : 'js';
  f.n = i.replace(/[\/\.]/g, '_');
  f.l = 0;
  f.c = 0;
}

if (!stVersions)
  var stVersions = {};

// VK.com Browser Detection
if (!window._ua) {
  var _ua = navigator.userAgent.toLowerCase();
}
var browser = {
  version: (_ua.match( /.+(?:me|ox|on|rv|it|era|ie)[\/: ]([\d.]+)/ ) || [0,'0'])[1],
  opera: /opera/i.test(_ua),
  msie: (/msie/i.test(_ua) && !/opera/i.test(_ua)),
  msie6: (/msie 6/i.test(_ua) && !/opera/i.test(_ua)),
  msie7: (/msie 7/i.test(_ua) && !/opera/i.test(_ua)),
  msie8: (/msie 8/i.test(_ua) && !/opera/i.test(_ua)),
  msie9: (/msie 9/i.test(_ua) && !/opera/i.test(_ua)),
  mozilla: /firefox/i.test(_ua),
  chrome: /chrome/i.test(_ua),
  safari: (!(/chrome/i.test(_ua)) && /webkit|safari|khtml/i.test(_ua)),
  iphone: /iphone/i.test(_ua),
  ipod: /ipod/i.test(_ua),
  iphone4: /iphone.*OS 4/i.test(_ua),
  ipod4: /ipod.*OS 4/i.test(_ua),
  ipad: /ipad/i.test(_ua),
  android: /android/i.test(_ua),
  bada: /bada/i.test(_ua),
  mobile: /iphone|ipod|ipad|opera mini|opera mobi|iemobile|android/i.test(_ua),
  msie_mobile: /iemobile/i.test(_ua),
  safari_mobile: /iphone|ipod|ipad/i.test(_ua),
  opera_mobile: /opera mini|opera mobi/i.test(_ua),
  opera_mini: /opera mini/i.test(_ua),
  mac: /mac/i.test(_ua)
};

window.locHost = location.host;
window.locProtocol = location.protocol;
window.locHash = location.hash.replace('#/', '').replace('#!', '');
window.locBase = location.toString().replace(/#.+$/, '');

function topMsg(text, seconds, color) {
  if (!color) color = '#D6E5F7';
  if (!text) {
    $('#system_msg').hide();
  } else {
    clearTimeout(window.topMsgTimer);

    $('#system_msg').css({backgroundColor: color}).html(text).show();
    if (seconds) {
      window.topMsgTimer = setTimeout(topMsg.pbind(false), seconds * 1000);
    }
  }
}

function topError(text, opts) {
  if (!opts) opts = {};
  if (text.message) {
    var e = text;
    text = '<b>JavaScript error:</b> ' + e.message;
    opts.stack = e.stack;
    if (e.stack && __debugMode) text += '<br/>' + e.stack.replace(/\n/g, '<br/>');
    try { console.log(e.stack); } catch (e2) {};
  }
  if (!opts.stack) {
    try {eval('0 = 1');} catch(e) {
      opts.stack = e.stack;
    }
  }

  if (opts.dt != -1) {
    topMsg(text, opts.dt, '#FFB4A3');
  }
  if (!__debugMode) {
    delete(opts.dt);
    //ajax.post('/errors.php', $.extend(opts, {msg: opts.msg || text, module: (window.cur || {}).module, id: A.user_id, host: locHost, lang: A.lang, loc: (window.nav || {}).strLoc, realloc: location.toString()}));
  }
}

function langNumeric(count, vars, formatNum) {
  if (!vars || !window.langConfig) { return count; }
  var res;
  if (!$.isArray(vars)) {
    res = vars;
  } else {
    res = vars[1];
    if(count != Math.floor(count)) {
      res = vars[langConfig.numRules['float']];
    } else {
      $.each(langConfig.numRules['int'], function(i,v){
        if (v[0] == '*') { res = vars[v[2]]; return false; }
        var c = v[0] ? count % v[0] : count;
        if(indexOf(v[1], c) != -1) { res = vars[v[2]]; return false; }
      });
    }
  }
  if (formatNum) {
    var n = count.toString().split('.'), c = [];
    for(var i = n[0].length - 3; i > -3; i -= 3) {
      c.unshift(n[0].slice(i > 0 ? i : 0, i + 3));
    }
    n[0] = c.join(langConfig.numDel);
    count = n.join(langConfig.numDec);
  }
  res = (res || '%s').replace('%s', count);
  return res;
}

function langSex(sex, vars) {
  if (!$.isArray(vars)) return vars;
  var res = vars[1];
  if (!window.langConfig) return res;
  each(langConfig.sexRules, function(i,v){
    if (v[0] == '*') { res = vars[v[1]]; return false; }
    if (sex == v[0] && vars[v[1]]) { res = vars[v[1]]; return false; }
  });
  return res;
}

function getLang() {
  try {
    var args = Array.prototype.slice.call(arguments);
    var key = args.shift();
    if (!key) return '...';
    var val = (window.cur.lang && window.cur.lang[key]) || (window.lang && window.lang[key]) || (window.langpack && window.langpack[key]) || window[key];
    if (!val) {
      var res = key.split('_');
      res.shift();
      return res.join(' ');
    }
    if ($.isFunction(val)) {
      return val.apply(null, args);
    } else if (args[0] !== undefined || (args[1] !== undefined && $.isArray(val))) {
      return langNumeric(args[0], val, args[1]);
    } else {
      return val;
    }
  } catch(e) {
    debugLog('lang error:' + e.message + '(' + Array.prototype.slice.call(arguments).join(', ') + ')');
  }
}

// Debug Log

var _logTimer = (new Date()).getTime();
var _debugLogHist = [];
var _debugLogHistOffset = 0;
var _debugLogHistShow = false;
function debugLog(msg){
  try {
    var t = '[' + (((new Date()).getTime() - _logTimer) / 1000) + '] ';
    if ($('#debuglog').length) {
      if (msg === null) {
        msg = '[NULL]';
      } else if (msg === undefined) {
        msg = '[UNDEFINED]';
      }
      $('#debuglog').appendChild(ce('div', {innerHTML: t + msg.toString().replace('<', '&lt;').replace('>', '&gt;')}));
    }
    if (window.console && console.log) {
      var args = Array.prototype.slice.call(arguments);
      args.unshift(t);
      if (browser.msie || browser.mobile) {
        console.log(args.join(' '));
      } else {
        console.log.apply(console, args);
      }
    }
  } catch (e) {
  }
}
function debugLogHist(msg){
  try {
    var t = '[' + (((new Date()).getTime() - _logTimer) / 1000) + '] ', line = new Array(57).join('=');
    if ($('#debuglog').length) {
      msg = t + msg.toString().replace('<', '&lt;').replace('>', '&gt;')+'<br/>';
      msg = line + '<br/>' + msg + line + '<br/>';
      _debugLogHistOffset++;
      if (_debugLogHistOffset >= 20) {
        _debugLogHist.splice(0, _debugLogHistOffset - 19);
        _debugLogHistOffset = 19;
      }
      if (!_debugLogHist[_debugLogHistOffset]) { _debugLogHist[_debugLogHistOffset] = ''; }
      _debugLogHist[_debugLogHistOffset] += msg;
    }
  } catch (e) {
  }
}

// Utils (from VK.com)
Function.prototype.pbind = function() {
  var args = Array.prototype.slice.call(arguments);
  args.unshift(window);
  return this.bind.apply(this, args);
};
Function.prototype.bind = function() {
  var func = this, args = Array.prototype.slice.call(arguments);
  var obj = args.shift();
  return function() {
    var curArgs = Array.prototype.slice.call(arguments);
    return func.apply(obj, args.concat(curArgs));
  }
}

function rand(mi, ma) { return Math.random() * (ma - mi + 1) + mi; }
function irand(mi, ma) { return Math.floor(rand(mi, ma)); }

function indexOf(arr, value, from) {
  for (var i = from || 0, l = (arr || []).length; i < l; i++) {
    if (arr[i] == value) return i;
  }
  return -1;
}

// ===
function fadeIn(el, tt) {
  $(el).stop(true).animate({opacity: 1});
  tooltips.show(el, tt, [9, 5]);
}
function fadeOut(el, back_to) {
  $(el).stop(true).animate({opacity: back_to});
}

function highlight(el, color, nofocus) {
  el = $(el);
  if (!el.length) return;

  if (!nofocus) el.focus();
  var oldBack = el.data('back') || el.data('back', el.css('backgroundColor')).data('back');
  var colors = {notice: '#FFFFE0', warning: '#FAEAEA'};
  el.css('backgroundColor', colors[color] || color || colors.warning);
  el.delay(400).animate({
    backgroundColor: oldBack
  }, 300);
}

// Events
function onCtrlEnter(ev, handler) {
  ev = ev || window.event;
  if (ev.keyCode == 10 || ev.keyCode == 13 && (ev.ctrlKey || ev.metaKey)) {
    handler();
  }
}

window.showTitleProgress = function(timeout) {
  if (document.body) {
    document.body.style.cursor = 'progress';
  }
}
window.hideTitleProgress = function() {
  document.body.style.cursor = 'default';
}

// STL
function updateSTL(resized) {
  var st = $(window).scrollTop();

  if (resized) {
    _stlLeft.css({
      left: 0,
      width: Math.max($('#page_layout').offset().left, 0),
      height: $(window).height()
    });
  }

  var mx = 200, vis = (st > mx), o = 0;
  if (!vis) {
    if (_stlShown !== 0) {
      _stlLeft.hide();
      _stlShown = 0;
    }
  }
  else {
    if (_stlShown !== 1) {
      _stlLeft.addClass('stl_active').show();
      _stlShown = 1;
    }
    if (_stlWas && st > 500) {
      _stlWas = 0;
    }
    if (st > mx) {
      o = (st - mx) / mx;
      if (_stlWasSet || _stlBack) {
        _stlWasSet = _stlBack = 0;
        _stlText.html(getLang('global_to_top'));
        _stlLeft.removeClass('back').removeClass('down');
      }
    }
    else {
      o = (mx - st) / mx;
      if (!_stlWasSet) {
        _stlWasSet = 1; f = 0;
        _stlText.html('');
        _stlText.addClass('down');
        if (_stlBack) {
          _stlBack = 0;
          _stlText.removeClass('back');
        }
      }
    }

    _stlLeft.css({
      opacity: Math.min(Math.max(o, 0), 1)
    });
  }
}

function onBodyResize(force) {
  var w = window, de = document.documentElement;

  var dwidth = Math.max(parseInt(w.innerWidth), parseInt(de.clientWidth));
  var dheight = Math.max(parseInt(w.innerHeight), parseInt(de.clientHeight));
  var changed = false;

  if (w.lastWindowWidth != dwidth || force === true) {
    changed = true;
    w.lastInnerWidth = w.lastWindowWidth = dwidth;
    boxLayerWrap.width(dwidth);

    if (bodyNode.offsetWidth < A.width + 2) {
      dwidth = A.width + 2;
    }
    if (dwidth) {
      $('.scroll_fix').width( ((w.lastInnerWidth = (dwidth - 1 - 1)) - 1) );
    }
  }
  if (w.lastWindowHeight != dheight || force === true) {
    changed = true;
    w.lastWindowHeight = dheight;
    boxLayerBG.height(dheight);
    boxLayerWrap.height(dheight);
  }
  updateSTL(1);
  if (window.tooltips) {
    tooltips.rePositionAll();
  }
}

function onBodyScroll() {
  if (!window.pageNode) return;

  updateSTL();
}

function _stlMousedown(e) {
  e = e || window.event;
  if (!__afterFocus) {
    if (_stlWasSet && _stlWas) {
      var to = _stlWas;
      _stlWas = 0;
      $(window).scrollTop(to);
    } else {
      _stlWas = $(window).scrollTop();
      $(window).scrollTop(0);
    }
  }
  return e.stopPropagation();
}
function _stlMouseover(e) {
  var ev = (e ? e.originalEvent || e : window.event || {}),
    over = (ev.type == 'mouseover') && (ev.pageX > 0 || ev.clientX > 0);

  _stlLeft.toggleClass('over', over);
  _stlLeft.toggleClass('over_fast', over && (_stlBack === 0) && _stlWasSet === 0);
}

function domStarted() {
  window.headNode = $('head');
  $.extend(window, {
    icoNode:  $('link', headNode),
    bodyNode: $('body'),
    htmlNode: $('html'),
    utilsNode: $('#utils'),
    _fixedNav: false
  });
  bodyNode.onresize = onBodyResize.pbind(false);
  debugLog('dom started');
  if (!utilsNode) return;

  for (var i in StaticFiles) {
    var f = StaticFiles[i];
    f.l = 1;
    if (f.t == 'css') {
      $('<div/>').attr({id: f.n}).appendTo(utilsNode);
    }
  }

  var l = $('#layer_bg'), lw = l.next(), bl = $('#box_layer_bg'), blw = bl.next();
  $.extend(window, {
    layerBG: l,
    boxLayerBG: bl,
    layerWrap: lw,
    layer: lw.children(),
    boxLayerWrap: blw,
    boxLayer: blw.children(),
    boxLoader: blw.children().children(),
    _stlLeft: $('#stl_left'),
    _stlShown: 0,
    _stlWas: 0,
    _stlWasSet: 0,
    _stlBack: 0,
    __afterFocus: false,
    __needBlur: false
  });

  var s = {
    className: 'fixed',
    onmousedown: _stlMousedown,
    onmouseover: _stlMouseover,
    onmouseout: _stlMouseover
  };
  _stlLeft.html('<div id="stl_bg"><nobr id="stl_text">' + getLang('global_to_top') + '</nobr></div>');
  _stlLeft.addClass('fixed').mouseover(_stlMouseover).mouseout(_stlMouseover).mousedown(_stlMousedown);
  window._stlBg = _stlLeft.children();
  window._stlText = _stlBg.children();
  $(window).blur(function(e) {
    __needBlur = false;
  });
  $(window).focus(function(e) {
    if (__needBlur) {
      return; // opera fix
    }
    __afterFocus = __needBlur = true;
    setTimeout(function() {
      __afterFocus = false;
    }, 10);
  });

  //boxLayerWrap.click(__bq.hideLastCheck);

  hab.init();
}

function domReady() {
  if (!utilsNode) return;

  $.extend(window, {
    pageNode: $('#page_wrap')
  });

  window.scrollNode = bodyNode;

  if (A.al == 1) {
    showTitleProgress();
  }

  __stm.highlimit = 600;
  __stm.lowlimit = 100;

  onBodyResize();
  setTimeout(onBodyResize.pbind(false), 0);

  $(window).scroll(onBodyScroll);

  A.loaded = true;
}

/* Static Manager v2.0 */
var stManager = {
  _waiters: [],
  _wait: function() {
    var l = __stm._waiters.length, checked = {}, handlers = [];
    if (!l) {
      clearInterval(__stm._waitTimer);
      __stm._waitTimer = false;
      return;
    }
    for (var j = 0; j < l; ++j) {
      var wait = __stm._waiters[j][0];
      for (var i = 0, ln = wait.length; i < ln; ++i) {
        var f = wait[i];
        if (!checked[f]) {
          if (!StaticFiles[f].l && StaticFiles[f].t == 'css' && $('#'+ StaticFiles[f].n).css('display') == 'none') {
            __stm.done(f);
          }
          if (StaticFiles[f].l) {
            checked[f] = 1;
          } else {
            checked[f] = -1;
            if (A.loaded) {
              var c = ++StaticFiles[f].c;
              if (c > __stm.lowlimit && stVersions[f] > 0 || c > __stm.highlimit) {
                if (stVersions[f] < 0) {
                  topError('<b>Error:</b> Could not load <b>' + f + '</b>.', {dt: 5, type: 1, msg: '', file: f});
                  StaticFiles[f].l = 1;
                  checked[f] = 1;
                } else {
                  topMsg('Some problems with loading <b>' + f + '</b>...', 5);
                  stVersions[f] = irand(-10000, -1);
                  __stm._add(f, StaticFiles[f]);
                }
              }
            }
          }
        }
        if (checked[f] > 0) {
          wait.splice(i, 1);
          --i; --ln;
        }
      }
      if (!wait.length) {
        handlers.push(__stm._waiters.splice(j, 1)[0][1]);
        --j; --l;
      }
    }
    for (var j = 0, l = handlers.length; j < l; ++j) {
      handlers[j]();
    }
  },
  _add: function(f, old) {
    var name = f.replace(/[\/\.]/g, '_'),
      f_ver = stVersions[f],
      f_full = f + '?' + f_ver,
      f_prefix = '';

    StaticFiles[f] = {v: f_ver, n: name, l: 0, c: 0};
    if (f.indexOf('.js') != -1) {
      var p = '/js/';
      StaticFiles[f].t = 'js';

      if (f == 'common.js') {
        setTimeout(stManager.done.bind(stManager).pbind('common.js'), 0);
      } else {
        $('<script/>').attr({
          type: 'text/javascript',
          src: f_prefix + p + f_full
        }).appendTo(headNode);
      }
    } else if (f.indexOf('.css') != -1) {
      var p = '/css/';
      $('<link/>').attr({
        type: 'text/css',
        rel: 'stylesheet',
        href: f_prefix + p + f_full
      }).appendTo(headNode);

      StaticFiles[f].t = 'css';

      if (!$('#'+ name).length) {
        $('<div/>').attr('id', name).appendTo(utilsNode);
      }
    }
  },

  add: function(files, callback, async) {
    var wait = [], de = document.documentElement;
    if (!$.isArray(files)) files = [files];
    for (var i in files) {
      var f = files[i];
      if (f.indexOf('?') != -1) {
        var arr = f.split('?');
        f = arr[0];
        stVersions[f] = parseInt(arr[1]);
      }
      if (/^lang\d/i.test(f)) {
        stVersions[f] = stVersions['lang'];
      } else if (!stVersions[f]) {
        stVersions[f] = 1;
      }
// Opera Speed Dial fix
      var opSpeed = browser.opera && de.clientHeight == 768 && de.clientWidth == 1024;
      if ((opSpeed || __debugMode) && f != 'common.js' && f != 'common.css' && stVersions[f] > 0 && stVersions[f] < 1000000000) stVersions[f] += irand(1000000000, 2000000000);
      var old = StaticFiles[f];
      if (!old || old.v != stVersions[f]) {
        __stm._add(f, old);
      }
      if (callback && !StaticFiles[f].l) {
        wait.push(f);
      }
    }
    if (!callback) return;
    if (!wait.length) {
      return (async === true) ? setTimeout(callback, 0) : callback();
    }
    __stm._waiters.push([wait, callback]);
    if (!__stm._waitTimer) {
      __stm._waitTimer = setInterval(__stm._wait, 100);
    }
  },
  done: function(f) {
    if (stVersions[f] < 0) {
      topMsg('<b>Warning:</b> Something is bad, please <b><a href="/page-1_1">clear your cache</a></b> and restart your browser.', 10);
    }
    StaticFiles[f].l = 1;
  }
}, __stm = stManager;

// Ajax Operations
function q2obj(qa) {
  if (!qa) return {};
  var query = {} , dec = function(str) {
    return decodeURIComponent(str);
  };
  qa = qa.split('&');
  $.each(qa, function(i, a) {
    var t = a.split('=');
    if (t[0]) {
      var v = dec(t[1] + '');
      if (t[0].substr(t.length - 2) == '[]') {
        var k = dec(t[0].substr(0, t.length - 2));
        if (!query[k]) {
          query[k] = [];
        }
        query[k].push(v);
      }
      else if (t[0].match(/(\[|\])/g)) {
        var k = t[0].split('['),
          n = dec(k[0]),
          m = dec(k[1].replace(']', ''));
        if (!query[n]) {
          query[n] = {};
        }
        query[n][m] = v;
      } else {
        query[dec(t[0])] = v;
      }
    }
  });
  return query;
}
function obj2q(qa) {
  var query = [], enc = function(str) {
    return encodeURIComponent(str);
  };

  for (var key in qa) {
    if (qa[key] == null || $.isFunction(qa[key])) continue;
    if ($.isArray(qa[key])) {
      for (var i = 0, c = 0, l = qa[key].length; i < l; ++i) {
        if (qa[key][i] == null || $.isFunction(qa[key][i])) {
          continue;
        }
        query.push(enc(key) + '[' + c + ']=' + enc(qa[key][i]));
        ++c;
      }
    }
    else if ($.isPlainObject(qa[key])) {
      for(var i in qa[key]) {
        query.push(enc(key) + '['+ enc(i) +']=' + enc(qa[key][i]));
      }
    } else {
      query.push(enc(key) + '=' + enc(qa[key]));
    }
  }
  query.sort();
  return query.join('&');
}

// Navigation System v2.0

var ajax = {
  STATUS_OK: 1,
  STATUS_ERR: 0,
  STATUS_EXCEPTION: -1,

  post: function(url, params, options) {
    if (url.substr(0, 1) != '/') url = '/' + url;
    var o = $.extend(options, {});

    if (o.progress) {
      o.showProgress = $(o.progress).show;
      o.hideProgress = $(o.progress).hide;
    }

    if (o.loader) {
      o.showProgress = function() {
        boxRefreshCoords(boxLoader);
        boxLoader.show();
        boxLayerWrap.show();
      }
      o.hideProgress = function() {
        boxLoader.hide();
        boxLayerWrap.hide();
      }
    }

    return ajax._post(url, params, o);
  },

  _post: function(url, p, o) {
    if (o.showProgress) o.showProgress();

    var fail = function(x, s, e) {
      if (o.hideProgress) o.hideProgress();
      if (o.onFail && o.onFail(x) === true) return;
      if (!p.al) x.status = 666;

      switch (x.status) {
        case 403:
        case 500:
          try {
            var r = $.parseJSON(x.responseText);
          } catch(e) {}
          showFastBox(getLang('global_error'), (r && r.html) || getLang('global_page_error'));
          break;
        case 404:
          showFastBox(getLang('global_error'), getLang('global_page_not_found'));
          break;
        default:
          if (e == 'abort' || x.responseText == 'abort') break;
          topError(e || x.responseText, {dt: 5, type: 3, status: s, url: url, query: p && obj2q(p)});
          break;
      }
    }

    var done = function(r) {
      if (o.hideProgress) o.hideProgress();

      var loaded = function(r) {
        if (o.onDone) o.onDone(r);
      }
      if (r.files) {
        stManager.add(r.files, loaded.pbind(r));
      }
      else loaded(r);
    }

    var request = $.ajax({
      type: 'POST',
      dataType: (o.dataType) ? o.dataType : 'json',
      url: url,
      data: p,
      error: fail,
      success: done
    });
    return request;
  }
};

function HistoryAndBookmarks(params) {
  // strict check for cool hash display in ff.
  var fixEncode = function(loc) {
    var h = loc.split('#');
    var l = h[0].split('?');
    return l[0] + (l[1] ? ('?' + obj2q(q2obj(l[1]))) : '') + (h[1] ? ('#' + h[1]) : '');
  }

  var options = $.extend({onLocChange: function() {}}, params);

  var getLoc = function() {
    var loc = '';
    if (A.al == 3) {
      loc = (location.pathname || '') + (location.search || '') + (location.hash || '');
    } else {
      loc = (location.toString().match(/#(.*)/) || {})[1] || '';
      if (loc.substr(0, 1) != A.navPrefix) {
        loc = (location.pathname || '') + (location.search || '') + (location.hash || '');
      }
    }
    if (!loc && A.al > 1) {
      loc = (location.pathname || '') + (location.search || '');
    }
    return fixEncode(loc.replace(/^(\/|!)/, ''));
  }

  var curLoc = getLoc(true);

  var setLoc = function(loc) {
    //curLoc = fixEncode(loc.replace(/#(\/|!)?/, ''));
    curLoc = fixEncode(loc);
    var l = (location.toString().match(/#(.*)/) || {})[1] || '';
    if (!l && A.al > 1) {
      l = (location.pathname || '') + (location.search || '');
    }
    l = fixEncode(l);
    if (l.replace(/^(\/|!)/, '') != curLoc) {
      if (A.al == 3) {
        try {
          history.pushState({}, '', '/' + curLoc);
          return;
        } catch(e) {}
      }
      window.chHashFlag = true;
      location.hash = '#' + A.navPrefix + curLoc;
    }
  }

  var checker = function(force) {
    var l = getLoc();
    if (l == curLoc && force !== true) {
      return;
    }
    options.onLocChange(l);

    curLoc = l;
  }
  var checkTimer;
  var init = function() {
    if (A.al == 1) {
      checker(true);
    }
    if (A.al == 3) {
      $(window).bind('popstate', checker);
    } else if ('onhashchange' in window) {
      $(window).bind('hashchange', function() {
        if (window.chHashFlag) {
          window.chHashFlag = false;
        } else {
          checker();
        }
      });
    } else {
      checkTimer = setInterval(checker, 200);
    }
  }
  return {
    setLoc: setLoc,
    getLoc: getLoc,
    init: init,
    setOptions: function(params) {
      options = $.extend(options, params);
    },
    checker: checker,
    stop: function() {
      if (A.al < 3) {
        clearInterval(checkTimer);
      } else if (vk.al == 3) {
        $(window).unbind('popstate', checker);
      }
    }
  }
}

window.hab = new HistoryAndBookmarks({onLocChange: function(loc) {
  nav.go('/' + loc, undefined, {back: true, hist: true});
}});

var globalHistory = [];
function globalHistoryDestroy(loc) {
  for (var i = 0, l = globalHistory.length; i < l; ++i) {
    if (globalHistory[i].loc == loc) {
      var h = globalHistory.splice(i, 1)[0];
      h.content.innerHTML = '';
      --i; --l;
    }
  }
}

var nav = {
  reload: function(opts) {
    opts = opts || {};
    if (opts.force) {
      hab.stop();
      location.href = '/' + nav.strLoc;
    } else {
      nav.go('/' + nav.strLoc, undefined, $.extend({nocur: true}, opts));
    }
  },
  go: function(loc, ev, opts) {
    if (loc.tagName && loc.tagName.toLowerCase() == 'a' && loc.href) {
      if (loc.target == '_blank') {
        return;
      }
      loc = loc.href;
    }

    var strLoc = '', objLoc = {}, changed = {};
    if (typeof(loc) == 'string') {
      loc = loc.replace(new RegExp('^(' + locProtocol + '//' + locHost + ')?/?', 'i'), '');
      strLoc = loc;
      objLoc = nav.fromStr(loc);
    } else {
      if (!loc[0]) loc[0] = '';
      strLoc = nav.toStr(loc);
      objLoc = loc;
    }

    opts = opts || {};

    if (A.al == 4) {
      setTimeout(function() {
        location.href = '/' + (strLoc || '').replace('%23', '#');
      }, 0);
      return false;
    }

    if (opts.back) {
      for (var i = 0, l = globalHistory.length; i < l; ++i) {
        if (globalHistory[i].loc == strLoc) {
          var h = globalHistory.splice(i, 1)[0];
          var cNode = $('#content');

          if (window.tooltips) tooltips.destroyAll();

          cur = h.cur;

          setTimeout(function() {
            cNode.html('').replaceWith(h.content);
            $(window).scrollTop(h.scrollTop);
            document.title = h.title;

            nav.setLoc(strLoc);
          }, 10);
          return false;
        }
      }
    }

    var dest = objLoc[0];
    delete(objLoc[0]);

    var where = {url: strLoc, params: $.extend(objLoc, opts.params || {})};

    if (cur.request)
      cur.request.abort();

    $.extend(where.params, {al: 1});
    cur.request = ajax.post(where.url, where.params, {
      onDone: function(r) {
        curDestroy(cur);
        cur = {destroy: []};

        $('#content').html(r.html);
        $('title').html(r.title);

        nav.curLoc = strLoc;
        if (r.js) {
          eval('(function() {'+ r.js +';})()');
        }

        // counters
        // only for foto-mage.ru
        if (r.counters) {
          updateFriendsCounter(r.counters['friends']);
          updatePMCounter(r.counters['pm']);
        }

        setTimeout(function() {
          nav.setLoc(strLoc);
        }, 100);
      },
      showProgress: opts.showProgress || showTitleProgress,
      hideProgress: opts.hideProgress || hideTitleProgress
    });

    return false;
  },
  setLoc: function(loc) {
    if (typeof(loc) == 'string') {
      nav.strLoc = loc;
      nav.objLoc = nav.fromStr(loc);
    } else {
      nav.strLoc = nav.toStr(loc);
      nav.objLoc = loc;
    }
    hab.setLoc(nav.strLoc);
  },
  change: function(loc, ev, opts) {
    var params = $.clone(nav.objLoc);
    each(loc, function(i,v) {
      if (v === false) {
        delete params[i];
      } else {
        params[i] = v;
      }
    });
    return nav.go(params, ev, opts);
  },
  fromStr: function(str) {
    str = str.split('#');
    var res = str[0].split('?');
    var param = {'0': res[0] || ''}
    if (str[1]) {
      param['#'] = str[1];
    }
    return $.extend(q2obj(res[1] || ''), param);
  },
  toStr: function(obj) {
    obj =  $.extend(true, {}, obj);
    var hash = obj['#'] || '';
    var res = obj[0] || '';
    delete(obj[0]);
    delete(obj['#']);
    var str = obj2q(obj);
    return (str ? (res + '?' + str) : res) + (hash ? ('#' + hash) : '');
  },
  init: function() {
    nav.strLoc = hab.getLoc();
    nav.objLoc = nav.fromStr(nav.strLoc);
  }
}

function curDestroy(c) {
  if (!c.destroy || !c.destroy.length) return;
  for (var i in c.destroy) {
    try {
      c.destroy[i](c);
    } catch (e) {

    }
  }
}

nav.init();

// Other stuff
function checkTextLength(maxLen, inp, warn, nobr, cut) {
  var inp = $(inp),
    value = inp.val(),
    lastLen = inp.lastLen || 0;
  if (inp.lastLen === value.length) return;
  inp.lastLen = value.length;
  var countRealLen = function(text, nobr) {
    var spec = {'&':5,'<':4,'>':4,'"':6,"\n":(nobr?1:4),"\r":0,'!':5,"'":5,'$':6,'\\':6},
      good = {0x490:1,0x491:1,0x2013:1,0x2014:1,0x2018:1,0x2019:1,0x201a:1,0x2026:1,0x2030:1,0x2039:1,0x203a:1,0x20ac:1,0x2116:1,0x2122:1,0xfffd:1},
      bad = {0x40d:1,0x450:1,0x45d:1};
    if (cut) spec[','] = 5;
    var res = 0;
    for (var i = 0, l = text.length; i < l; i++) {
      var k = spec[text.charAt(i)], c = text.charCodeAt(i);
      if (k !== undefined) res += k;
      else if (c >= 0x80 && (c < 0x401 || bad[c] || c > 0x45f) && !good[c] && (c < 0x201c || c > 0x201e) && (c < 0x2020 || c > 0x2022)) res += ('&#' + c + ';').length;
      else res += 1;
    }
    return res;
  }
  var realLen = countRealLen(value, nobr);
  warn = $(warn);
  if (realLen > maxLen - 100) {
    warn.show();
    if (realLen > maxLen) {
      if (cut) {
        var cutVal = val(inp, value.substr(0, Math.min(maxLen, lastLen)));
        inp.lastLen = cutVal.length;
        warn.html(getLang('text_N_symbols_remain', 0));
      } else {
        warn.html(getLang('text_exceeds_symbol_limit', realLen - maxLen));
      }
    } else {
      warn.html(getLang('text_N_symbols_remain', maxLen - realLen));
    }
  } else {
    warn.hide();
  }
}

function autosizeSetup(el, options) {
  el = $(el);
  if (!el) return;
  if (el.autosize) {
    el.autosize.update();
    return;
  }

  options.minHeight = parseInt(options.minHeight) || parseInt(el.innerHeight());
  options.maxHeight = parseInt(options.maxHeight);

  var elwidth = parseInt(el.innerWidth()),
    fs = el.css('fontSize'),
    lh;

  if (fs.indexOf('em') > 0) {
    fs = parseFloat(fs) * 11;
  }
  fs = parseInt(fs);
  el.autosize = {
    options: options,
    helper: $('<textarea/>', {className: 'ashelper'}).css({
      width: elwidth,
      height: 10,
      fontFamily: el.css('fontFamily'),
      fontSize: fs + 'px',
      lineHeight: (lh = el.css('lineHeight'))
    }),
    handleEvent: function(v, e) {
      var ch = e.charCode ? String.fromCharCode(e.charCode) : e.charCode;
      if (ch === undefined) {
        ch = String.fromCharCode(e.keyCode);
        if (e.keyCode == 10 || e.keyCode == 13) {
          ch = '\n';
        } else if (!browser.msie && e.keyCode <= 40) {
          ch = '';
        }
      }
      if (!ch) {
        return v;
      }
      if (!browser.msie) {
        var elle = document.getElementById(el.prop('id'));
        return v.substr(0, elle.selectionStart) + ch + v.substr(elle.selectionEnd);
      }
      var r = document.selection.createRange();
      if (r.text) {
        v = v.replace(r.text, '');
      }
      return v + ch;
    },
    update: function(e) {
      var value = el.val();
      if (e && e.type != 'blur' && e.type != 'keyup' && (!browser.msie || e.type == 'keypress')) {
        if (!e.ctrlKey && !e.altKey && !e.metaKey) {
          value = el.autosize.handleEvent(value, e);
        }
      }
      if (!value) {
        value = ' ';
      }
      if (el.autosize.helper.val() != value) {
        el.autosize.helper.val(value);
      }
      var opts = el.autosize.options;

      var oldHeight = parseInt(el.outerHeight());
      var newHeight = parseInt(el.autosize.helper.prop('scrollHeight')), df;
      if (opts.exact && (df = newHeight % lh) > 2) {
        newHeight -= (df - 2);
      }
      if (newHeight < opts.minHeight) {
        newHeight = opts.minHeight;
      }
      var newStyle = {overflow: 'hidden'}, curOverflow = el.css('overflow').indexOf('auto') > -1 ? 'auto' : 'hidden';
      if (opts.maxHeight && newHeight > opts.maxHeight) {
        newHeight = opts.maxHeight;
        $.extend(newStyle, {overflow: 'auto', overflowX: 'hidden'});
      }
      if (oldHeight != newHeight || curOverflow != newStyle.overflow) {
        newStyle.height = newHeight;
        el.css(newStyle);
        if ($.isFunction(opts.onResize)) {
          opts.onResize(newHeight);
        }
      }
    }
  }
  if (options.exact) {
    if (lh == 'normal') lh = '120%';
    lh = parseInt((lh.indexOf('%') > 0) ? fs * parseInt(lh) / 100 : lh);
  }
  utilsNode.append(el.autosize.helper);
  if (browser.opera_mobile) {
    el.css({overflow: 'hidden'});
    el.autosize.update();
    el.bind('blur', el.autosize.update);
  } else {
    el.bind('keydown keyup keypress', el.autosize.update);
    setTimeout(function() {
      el.css({overflow: 'hidden', resize: 'none'});
      el.autosize.update();
      var t = el.val(); el.val(' '); el.val(t);
    }, 0);
  }
}

function updateFriendsCounter(c) {
  if (c > 0) {
    if ($('#friends_link').find('span').length) $('#friends_link').find('span').html('+'+ c);
    else $('#friends_link').append('<span class="fl_r menu_counter">+ '+ c +'</span>');

    $('#friends_link').prop('href', '/friends?section=requests');
  } else {
    $('#friends_link').prop('href', '/friends');
    $('#friends_link').find('span').remove();
  }

}
function updatePMCounter(c) {
  if (c > 0) {
    if ($('#pm_link').find('span').length) $('#pm_link').find('span').html('+'+ c);
    else $('#pm_link').append('<span class="fl_r menu_counter">+ '+ c +'</span>');
  } else {
    $('#pm_link').find('span').remove();
  }
}

try{stManager.done('common.js');}catch(e){}