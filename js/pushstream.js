/*global PushStream WebSocketWrapper EventSourceWrapper EventSource*/
/*jshint evil: true, plusplus: false, regexp: false */
/**
 * Copyright (C) 2010-2013 Wandenberg Peixoto <wandenberg@gmail.com>, Rogério Carvalho Schneider <stockrt@gmail.com>
 *
 * This file is part of Nginx Push Stream Module.
 *
 * Nginx Push Stream Module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Nginx Push Stream Module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Nginx Push Stream Module.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * pushstream.js
 *
 * Created: Nov 01, 2011
 * Authors: Wandenberg Peixoto <wandenberg@gmail.com>, Rogério Carvalho Schneider <stockrt@gmail.com>
 */
(function (window, document, undefined) {
  "use strict";

  /* prevent duplicate declaration */
  if (window.PushStream) { return; }

  var Utils = {};

  var days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
  var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

  var valueToTwoDigits = function (value) {
    return ((value < 10) ? '0' : '') + value;
  };

  Utils.dateToUTCString = function (date) {
    var time = valueToTwoDigits(date.getUTCHours()) + ':' + valueToTwoDigits(date.getUTCMinutes()) + ':' + valueToTwoDigits(date.getUTCSeconds());
    return days[date.getUTCDay()] + ', ' + valueToTwoDigits(date.getUTCDate()) + ' ' + months[date.getUTCMonth()] + ' ' + date.getUTCFullYear() + ' ' + time + ' GMT';
  };

  var extend = function () {
    var object = arguments[0] || {};
    for (var i = 0; i < arguments.length; i++) {
      var settings = arguments[i];
      for (var attr in settings) {
        if (!settings.hasOwnProperty || settings.hasOwnProperty(attr)) {
          object[attr] = settings[attr];
        }
      }
    }
    return object;
  };

  var validChars  = /^[\],:{}\s]*$/,
      validEscape = /\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,
      validTokens = /"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,
      validBraces = /(?:^|:|,)(?:\s*\[)+/g;

  var trim = function(value) {
    return value.replace(/^\s*/, "").replace(/\s*$/, "");
  };

  Utils.parseJSON = function(data) {
    if (!data || !isString(data)) {
      return null;
    }

    // Make sure leading/trailing whitespace is removed (IE can't handle it)
    data = trim(data);

    // Attempt to parse using the native JSON parser first
    if (window.JSON && window.JSON.parse) {
      try {
        return window.JSON.parse( data );
      } catch(e) {
        throw "Invalid JSON: " + data;
      }
    }

    // Make sure the incoming data is actual JSON
    // Logic borrowed from http://json.org/json2.js
    if (validChars.test(data.replace(validEscape, "@").replace( validTokens, "]").replace( validBraces, "")) ) {
      return (new Function("return " + data))();
    }

    throw "Invalid JSON: " + data;
  };

  var getControlParams = function(pushstream) {
    var data = {};
    data[pushstream.tagArgument] = "";
    data[pushstream.timeArgument] = "";
    data[pushstream.eventIdArgument] = "";
    if (pushstream.messagesControlByArgument) {
      data[pushstream.tagArgument] = Number(pushstream._etag);
      if (pushstream._lastModified) {
        data[pushstream.timeArgument] = pushstream._lastModified;
      } else if (pushstream._lastEventId) {
        data[pushstream.eventIdArgument] = pushstream._lastEventId;
      }
    }
    return data;
  };

  var getTime = function() {
    return (new Date()).getTime();
  };

  var currentTimestampParam = function() {
    return { "_" : getTime() };
  };

  var objectToUrlParams = function(settings) {
    var params = settings;
    if (typeof(settings) === 'object') {
      params = '';
      for (var attr in settings) {
        if (!settings.hasOwnProperty || settings.hasOwnProperty(attr)) {
          params += '&' + attr + '=' + window.escape(settings[attr]);
        }
      }
      params = params.substring(1);
    }

    return params || '';
  };

  var addParamsToUrl = function(url, params) {
    return url + ((url.indexOf('?') < 0) ? '?' : '&') + objectToUrlParams(params);
  };

  var isArray = Array.isArray || function(obj) {
    return Object.prototype.toString.call(obj) === '[object Array]';
  };

  var isString = function(obj) {
    return Object.prototype.toString.call(obj) === '[object String]';
  };

  var isDate = function(obj) {
    return Object.prototype.toString.call(obj) === '[object Date]';
  };

  var Log4js = {
    logger: null,
    debug : function() { if  (PushStream.LOG_LEVEL === 'debug')                                         { Log4js._log.apply(Log4js._log, arguments); }},
    info  : function() { if ((PushStream.LOG_LEVEL === 'info')  || (PushStream.LOG_LEVEL === 'debug'))  { Log4js._log.apply(Log4js._log, arguments); }},
    error : function() {                                                                                  Log4js._log.apply(Log4js._log, arguments); },
    _log  : function() {
      if (!Log4js.logger) {
        var console = window.console;
        if (console && console.log) {
          if (console.log.apply) {
            Log4js.logger = console.log;
          } else if ((typeof console.log === "object") && Function.prototype.bind) {
            Log4js.logger = Function.prototype.bind.call(console.log, console);
          } else if ((typeof console.log === "object") && Function.prototype.call) {
            Log4js.logger = function() {
              Function.prototype.call.call(console.log, console, Array.prototype.slice.call(arguments));
            };
          }
        }
      }

      if (Log4js.logger) {
        Log4js.logger.apply(window.console, arguments);
      }

      var logElement = document.getElementById(PushStream.LOG_OUTPUT_ELEMENT_ID);
      if (logElement) {
        var str = '';
        for (var i = 0; i < arguments.length; i++) {
          str += arguments[i] + " ";
        }
        logElement.innerHTML += str + '\n';

        var lines = logElement.innerHTML.split('\n');
        if (lines.length > 100) {
          logElement.innerHTML = lines.slice(-100).join('\n');
        }
      }
    }
  };

  var Ajax = {
    _getXHRObject : function() {
      var xhr = false;
      try { xhr = new window.XMLHttpRequest(); }
      catch (e1) {
        try { xhr = new window.XDomainRequest(); }
        catch (e2) {
          try { xhr = new window.ActiveXObject("Msxml2.XMLHTTP"); }
          catch (e3) {
            try { xhr = new window.ActiveXObject("Microsoft.XMLHTTP"); }
            catch (e4) {
              xhr = false;
            }
          }
        }
      }
      return xhr;
    },

    _send : function(settings, post) {
      settings = settings || {};
      settings.timeout = settings.timeout || 30000;
      var xhr = Ajax._getXHRObject();
      if (!xhr||!settings.url) { return; }

      Ajax.clear(settings);

      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          Ajax.clear(settings);
          if (settings.afterReceive) { settings.afterReceive(xhr); }
          if(xhr.status === 200) {
            if (settings.success) { settings.success(xhr.responseText); }
          } else {
            if (settings.error) { settings.error(xhr.status); }
          }
        }
      };

      if (settings.beforeOpen) { settings.beforeOpen(xhr); }

      var params = {};
      var body = null;
      var method = "GET";
      if (post) {
        body = objectToUrlParams(settings.data);
        method = "POST";
      } else {
        params = settings.data || {};
      }

      xhr.open(method, addParamsToUrl(settings.url, extend({}, params, currentTimestampParam())), true);

      if (settings.beforeSend) { settings.beforeSend(xhr); }

      var onerror = function() {
        try { xhr.abort(); } catch (e) { /* ignore error on closing */ }
        Ajax.clear(settings);
        settings.error(304);
      };

      if (post) {
        xhr.setRequestHeader("Accept", "application/json");
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      } else {
        settings.timeoutId = window.setTimeout(onerror, settings.timeout + 2000);
      }

      xhr.send(body);
      return xhr;
    },

    _clear_script : function(script) {
      // Handling memory leak in IE, removing and dereference the script
      if (script) {
        script.onerror = script.onload = script.onreadystatechange = null;
        if (script.parentNode) { script.parentNode.removeChild(script); }
      }
    },

    _clear_timeout : function(settings) {
      settings.timeoutId = clearTimer(settings.timeoutId);
    },

    clear : function(settings) {
      Ajax._clear_timeout(settings);
      Ajax._clear_script(document.getElementById(settings.scriptId));
    },

    jsonp : function(settings) {
      settings.timeout = settings.timeout || 30000;
      Ajax.clear(settings);

      var head = document.head || document.getElementsByTagName("head")[0];
      var script = document.createElement("script");
      var startTime = getTime();

      var onerror = function() {
        Ajax.clear(settings);
        var callbackFunctionName = settings.data.callback;
        if (callbackFunctionName) { window[callbackFunctionName] = function() { window[callbackFunctionName] = null; }; }
        var endTime = getTime();
        settings.error(((endTime - startTime) > settings.timeout/2) ? 304 : 0);
      };

      var onload = function() {
        Ajax.clear(settings);
        settings.load();
      };

      script.onerror = onerror;
      script.onload = script.onreadystatechange = function(eventLoad) {
        if (!script.readyState || /loaded|complete/.test(script.readyState)) {
          onload();
        }
      };

      if (settings.beforeOpen) { settings.beforeOpen({}); }
      if (settings.beforeSend) { settings.beforeSend({}); }

      settings.timeoutId = window.setTimeout(onerror, settings.timeout + 2000);
      settings.scriptId = settings.scriptId || getTime();

      var callbackFunctionName = settings.data.callback;
      if (callbackFunctionName) { window[callbackFunctionName] = function() { window[callbackFunctionName] = null; }; }
      settings.data.callback = settings.scriptId + "_onmessage_" + getTime();
      window[settings.data.callback] = settings.success;

      script.setAttribute("src", addParamsToUrl(settings.url, extend({}, settings.data, currentTimestampParam())));
      script.setAttribute("async", "async");
      script.setAttribute("id", settings.scriptId);

      // Use insertBefore instead of appendChild to circumvent an IE6 bug.
      head.insertBefore(script, head.firstChild);
      return settings;
    },

    load : function(settings) {
      return Ajax._send(settings, false);
    },

    post : function(settings) {
      return Ajax._send(settings, true);
    }
  };

  var escapeText = function(text) {
    return (text) ? window.escape(text) : '';
  };

  var unescapeText = function(text) {
    return (text) ? window.unescape(text) : '';
  };

  Utils.parseMessage = function(messageText, keys) {
    var msg = messageText;
    if (isString(messageText)) {
      msg = Utils.parseJSON(messageText);
    }

    var message = {
        id     : msg[keys.jsonIdKey],
        channel: msg[keys.jsonChannelKey],
        text   : isString(msg[keys.jsonTextKey]) ? unescapeText(msg[keys.jsonTextKey]) : msg[keys.jsonTextKey],
        tag    : msg[keys.jsonTagKey],
        time   : msg[keys.jsonTimeKey],
        eventid: msg[keys.jsonEventIdKey] || ""
    };

    return message;
  };

  var getBacktrack = function(options) {
    return (options.backtrack) ? ".b" + Number(options.backtrack) : "";
  };

  var getChannelsPath = function(channels, withBacktrack) {
    var path = '';
    for (var channelName in channels) {
      if (!channels.hasOwnProperty || channels.hasOwnProperty(channelName)) {
        path += "/" + channelName + (withBacktrack ? getBacktrack(channels[channelName]) : "");
      }
    }
    return path;
  };

  var getSubscriberUrl = function(pushstream, prefix, extraParams, withBacktrack) {
    var websocket = pushstream.wrapper.type === WebSocketWrapper.TYPE;
    var useSSL = pushstream.useSSL;
    var url = (websocket) ? ((useSSL) ? "wss://" : "ws://") : ((useSSL) ? "https://" : "http://");
    url += pushstream.host;
    url += ((!useSSL && pushstream.port === 80) || (useSSL && pushstream.port === 443)) ? "" : (":" + pushstream.port);
    url += prefix;

    var channels = getChannelsPath(pushstream.channels, withBacktrack);
    if (pushstream.channelsByArgument) {
      var channelParam = {};
      channelParam[pushstream.channelsArgument] = channels.substring(1);
      extraParams = extend({}, extraParams, channelParam);
    } else {
      url += channels;
    }
    return addParamsToUrl(url, extraParams);
  };

  var getPublisherUrl = function(pushstream) {
    var channel = "";
    var url = (pushstream.useSSL) ? "https://" : "http://";
    url += pushstream.host;
    url += ((pushstream.port !== 80) && (pushstream.port !== 443)) ? (":" + pushstream.port) : "";
    url += pushstream.urlPrefixPublisher;
    for (var channelName in pushstream.channels) {
      if (!pushstream.channels.hasOwnProperty || pushstream.channels.hasOwnProperty(channelName)) {
        channel = channelName;
        break;
      }
    }
    url += "?id=" + channel;
    return url;
  };

  Utils.extract_xss_domain = function(domain) {
    // if domain is an ip address return it, else return ate least the last two parts of it
    if (domain.match(/^(\d{1,3}\.){3}\d{1,3}$/)) {
      return domain;
    }

    var domainParts = domain.split('.');
    // if the domain ends with 3 chars or 2 chars preceded by more than 4 chars,
    // we can keep only 2 parts, else we have to keep at least 3 (or all domain name)
    var keepNumber = Math.max(domainParts.length - 1, (domain.match(/(\w{4,}\.\w{2}|\.\w{3,})$/) ? 2 : 3));

    return domainParts.slice(-1 * keepNumber).join('.');
  };

  var linker = function(method, instance) {
    return function() {
      return method.apply(instance, arguments);
    };
  };

  var clearTimer = function(timer) {
    if (timer) {
      window.clearTimeout(timer);
    }
    return null;
  };

  /* common callbacks */
  var onmessageCallback = function(event) {
    Log4js.info("[" + this.type + "] message received", arguments);
    var message = Utils.parseMessage(event.data, this.pushstream);
    if (message.tag) { this.pushstream._etag = message.tag; }
    if (message.time) { this.pushstream._lastModified = message.time; }
    if (message.eventid) { this.pushstream._lastEventId = message.eventid; }
    this.pushstream._onmessage(message.text, message.id, message.channel, message.eventid, true);
  };

  var onopenCallback = function() {
    this.pushstream._onopen();
    Log4js.info("[" + this.type + "] connection opened");
  };

  var onerrorCallback = function(event) {
    Log4js.info("[" + this.type + "] error (disconnected by server):", event);
    if ((this.pushstream.readyState === PushStream.OPEN) &&
        (this.type === EventSourceWrapper.TYPE) &&
        (event.type === 'error') &&
        (this.connection.readyState === EventSource.CONNECTING)) {
      // EventSource already has a reconnection function using the last-event-id header
      return;
    }
    this._closeCurrentConnection();
    this.pushstream._onerror({type: ((event && ((event.type === "load") || (event.type === "close"))) || (this.pushstream.readyState === PushStream.CONNECTING)) ? "load" : "timeout"});
  };

  /* wrappers */

  var WebSocketWrapper = function(pushstream) {
    if (!window.WebSocket && !window.MozWebSocket) { throw "WebSocket not supported"; }
    this.type = WebSocketWrapper.TYPE;
    this.pushstream = pushstream;
    this.connection = null;
  };

  WebSocketWrapper.TYPE = "WebSocket";

  WebSocketWrapper.prototype = {
    connect: function() {
      this._closeCurrentConnection();
      var params = extend({}, this.pushstream.extraParams(), currentTimestampParam(), getControlParams(this.pushstream));
      var url = getSubscriberUrl(this.pushstream, this.pushstream.urlPrefixWebsocket, params, !this.pushstream._useControlArguments());
      this.connection = (window.WebSocket) ? new window.WebSocket(url) : new window.MozWebSocket(url);
      this.connection.onerror   = linker(onerrorCallback, this);
      this.connection.onclose   = linker(onerrorCallback, this);
      this.connection.onopen    = linker(onopenCallback, this);
      this.connection.onmessage = linker(onmessageCallback, this);
      Log4js.info("[WebSocket] connecting to:", url);
    },

    disconnect: function() {
      if (this.connection) {
        Log4js.debug("[WebSocket] closing connection to:", this.connection.URL);
        this.connection.onclose = null;
        this._closeCurrentConnection();
        this.pushstream._onclose();
      }
    },

    _closeCurrentConnection: function() {
      if (this.connection) {
        try { this.connection.close(); } catch (e) { /* ignore error on closing */ }
        this.connection = null;
      }
    },

    sendMessage: function(message) {
      if (this.connection) { this.connection.send(message); }
    }
  };

  var EventSourceWrapper = function(pushstream) {
    if (!window.EventSource) { throw "EventSource not supported"; }
    this.type = EventSourceWrapper.TYPE;
    this.pushstream = pushstream;
    this.connection = null;
  };

  EventSourceWrapper.TYPE = "EventSource";

  EventSourceWrapper.prototype = {
    connect: function() {
      this._closeCurrentConnection();
      var params = extend({}, this.pushstream.extraParams(), currentTimestampParam(), getControlParams(this.pushstream));
      var url = getSubscriberUrl(this.pushstream, this.pushstream.urlPrefixEventsource, params, !this.pushstream._useControlArguments());
      this.connection = new window.EventSource(url);
      this.connection.onerror   = linker(onerrorCallback, this);
      this.connection.onopen    = linker(onopenCallback, this);
      this.connection.onmessage = linker(onmessageCallback, this);
      Log4js.info("[EventSource] connecting to:", url);
    },

    disconnect: function() {
      if (this.connection) {
        Log4js.debug("[EventSource] closing connection to:", this.connection.URL);
        this.connection.onclose = null;
        this._closeCurrentConnection();
        this.pushstream._onclose();
      }
    },

    _closeCurrentConnection: function() {
      if (this.connection) {
        try { this.connection.close(); } catch (e) { /* ignore error on closing */ }
        this.connection = null;
      }
    }
  };

  var StreamWrapper = function(pushstream) {
    this.type = StreamWrapper.TYPE;
    this.pushstream = pushstream;
    this.connection = null;
    this.url = null;
    this.frameloadtimer = null;
    this.pingtimer = null;
    this.iframeId = "PushStreamManager_" + pushstream.id;
  };

  StreamWrapper.TYPE = "Stream";

  StreamWrapper.prototype = {
    connect: function() {
      this._closeCurrentConnection();
      var domain = Utils.extract_xss_domain(this.pushstream.host);
      try {
        document.domain = domain;
      } catch(e) {
        Log4js.error("[Stream] (warning) problem setting document.domain = " + domain + " (OBS: IE8 does not support set IP numbers as domain)");
      }
      var params = extend({}, this.pushstream.extraParams(), currentTimestampParam(), {"streamid": this.pushstream.id}, getControlParams(this.pushstream));
      this.url = getSubscriberUrl(this.pushstream, this.pushstream.urlPrefixStream, params, !this.pushstream._useControlArguments());
      Log4js.debug("[Stream] connecting to:", this.url);
      this.loadFrame(this.url);
    },

    disconnect: function() {
      if (this.connection) {
        Log4js.debug("[Stream] closing connection to:", this.url);
        this._closeCurrentConnection();
        this.pushstream._onclose();
      }
    },

    _clear_iframe: function() {
      var oldIframe = document.getElementById(this.iframeId);
      if (oldIframe) {
        oldIframe.onload = null;
        oldIframe.src = "about:blank";
        if (oldIframe.parentNode) { oldIframe.parentNode.removeChild(oldIframe); }
      }
    },

    _closeCurrentConnection: function() {
      this._clear_iframe();
      if (this.connection) {
        this.pingtimer = clearTimer(this.pingtimer);
        this.frameloadtimer = clearTimer(this.frameloadtimer);
        this.connection = null;
        this.transferDoc = null;
        if (typeof window.CollectGarbage === 'function') { window.CollectGarbage(); }
      }
    },

    loadFrame: function(url) {
      this._clear_iframe();
      try {
        var transferDoc = new window.ActiveXObject("htmlfile");
        transferDoc.open();
        transferDoc.write("<html><script>document.domain=\""+(document.domain)+"\";</script></html>");
        transferDoc.parentWindow.PushStream = PushStream;
        transferDoc.close();
        var ifrDiv = transferDoc.createElement("div");
        transferDoc.appendChild(ifrDiv);
        ifrDiv.innerHTML = "<iframe src=\""+url+"\"></iframe>";
        this.connection = ifrDiv.getElementsByTagName("IFRAME")[0];
        this.connection.onload = linker(onerrorCallback, this);
        this.transferDoc = transferDoc;
      } catch (e) {
        var ifr = document.createElement("IFRAME");
        ifr.style.width = "1px";
        ifr.style.height = "1px";
        ifr.style.border = "none";
        ifr.style.position = "absolute";
        ifr.style.top = "-10px";
        ifr.style.marginTop = "-10px";
        ifr.style.zIndex = "-20";
        ifr.PushStream = PushStream;
        document.body.appendChild(ifr);
        ifr.setAttribute("src", url);
        ifr.onload = linker(onerrorCallback, this);
        this.connection = ifr;
      }
      this.connection.setAttribute("id", this.iframeId);
      this.frameloadtimer = window.setTimeout(linker(onerrorCallback, this), this.pushstream.timeout);
    },

    register: function(iframeWindow) {
      this.frameloadtimer = clearTimer(this.frameloadtimer);
      iframeWindow.p = linker(this.process, this);
      this.connection.onload = linker(this._onframeloaded, this);
      this.pushstream._onopen();
      this.setPingTimer();
      Log4js.info("[Stream] frame registered");
    },

    process: function(id, channel, text, eventid, time, tag) {
      this.pingtimer = clearTimer(this.pingtimer);
      Log4js.info("[Stream] message received", arguments);
      if (id !== -1) {
        if (tag) { this.pushstream._etag = tag; }
        if (time) { this.pushstream._lastModified = time; }
        if (eventid) { this.pushstream._lastEventId = eventid; }
      }
      this.pushstream._onmessage(unescapeText(text), id, channel, eventid || "", true);
      this.setPingTimer();
    },

    _onframeloaded: function() {
      Log4js.info("[Stream] frame loaded (disconnected by server)");
      this.connection.onload = null;
      this.disconnect();
    },

    setPingTimer: function() {
      if (this.pingtimer) { clearTimer(this.pingtimer); }
      this.pingtimer = window.setTimeout(linker(onerrorCallback, this), this.pushstream.pingtimeout);
    }
  };

  var LongPollingWrapper = function(pushstream) {
    this.type = LongPollingWrapper.TYPE;
    this.pushstream = pushstream;
    this.connection = null;
    this.opentimer = null;
    this.messagesQueue = [];
    this._linkedInternalListen = linker(this._internalListen, this);
    this.xhrSettings = {
        timeout: this.pushstream.timeout,
        data: {},
        url: null,
        success: linker(this.onmessage, this),
        error: linker(this.onerror, this),
        load: linker(this.onload, this),
        beforeSend: linker(this.beforeSend, this),
        afterReceive: linker(this.afterReceive, this)
    };
  };

  LongPollingWrapper.TYPE = "LongPolling";

  LongPollingWrapper.prototype = {
    connect: function() {
      this.messagesQueue = [];
      this._closeCurrentConnection();
      this.urlWithBacktrack = getSubscriberUrl(this.pushstream, this.pushstream.urlPrefixLongpolling, {}, true);
      this.urlWithoutBacktrack = getSubscriberUrl(this.pushstream, this.pushstream.urlPrefixLongpolling, {}, false);
      this.xhrSettings.url = this.urlWithBacktrack;
      var domain = Utils.extract_xss_domain(this.pushstream.host);
      var currentDomain = Utils.extract_xss_domain(window.location.hostname);
      var port = this.pushstream.port;
      var currentPort = window.location.port ? Number(window.location.port) : (this.pushstream.useSSL ? 443 : 80);
      this.useJSONP = (domain !== currentDomain) || (port !== currentPort) || this.pushstream.useJSONP;
      this.xhrSettings.scriptId = "PushStreamManager_" + this.pushstream.id;
      if (this.useJSONP) {
        this.pushstream.messagesControlByArgument = true;
      }
      this._internalListen();
      this.opentimer = window.setTimeout(linker(onopenCallback, this), 100);
      Log4js.info("[LongPolling] connecting to:", this.xhrSettings.url);
    },

    _listen: function() {
      if (this._internalListenTimeout) { clearTimer(this._internalListenTimeout); }
      this._internalListenTimeout = window.setTimeout(this._linkedInternalListen, 100);
    },

    _internalListen: function() {
      if (this.pushstream._keepConnected) {
        this.xhrSettings.url = this.pushstream._useControlArguments() ? this.urlWithoutBacktrack : this.urlWithBacktrack;
        this.xhrSettings.data = extend({}, this.pushstream.extraParams(), this.xhrSettings.data, getControlParams(this.pushstream));
        if (this.useJSONP) {
          this.connection = Ajax.jsonp(this.xhrSettings);
        } else if (!this.connection) {
          this.connection = Ajax.load(this.xhrSettings);
        }
      }
    },

    disconnect: function() {
      if (this.connection) {
        Log4js.debug("[LongPolling] closing connection to:", this.xhrSettings.url);
        this._closeCurrentConnection();
        this.pushstream._onclose();
      }
    },

    _closeCurrentConnection: function() {
      this.opentimer = clearTimer(this.opentimer);
      if (this.connection) {
        try { this.connection.abort(); } catch (e) {
          try { Ajax.clear(this.connection); } catch (e) { /* ignore error on closing */ }
        }
        this.connection = null;
        this.xhrSettings.url = null;
      }
    },

    beforeSend: function(xhr) {
      if (!this.pushstream.messagesControlByArgument) {
        xhr.setRequestHeader("If-None-Match", this.pushstream._etag);
        xhr.setRequestHeader("If-Modified-Since", this.pushstream._lastModified);
      }
    },

    afterReceive: function(xhr) {
      if (!this.pushstream.messagesControlByArgument) {
        this.pushstream._etag = xhr.getResponseHeader('Etag');
        this.pushstream._lastModified = xhr.getResponseHeader('Last-Modified');
      }
      this.connection = null;
    },

    onerror: function(status) {
      if (this.pushstream._keepConnected) { /* abort(), called by disconnect(), call this callback, but should be ignored */
        if (status === 304) {
          this._listen();
        } else {
          Log4js.info("[LongPolling] error (disconnected by server):", status);
          this._closeCurrentConnection();
          this.pushstream._onerror({type: ((status === 403) || (this.pushstream.readyState === PushStream.CONNECTING)) ? "load" : "timeout"});
        }
      }
    },

    onload: function() {
      this._listen();
    },

    onmessage: function(responseText) {
      if (this._internalListenTimeout) { clearTimer(this._internalListenTimeout); }
      Log4js.info("[LongPolling] message received", responseText);
      var lastMessage = null;
      var messages = isArray(responseText) ? responseText : responseText.replace(/\}\{/g, "}\r\n{").split("\r\n");
      for (var i = 0; i < messages.length; i++) {
        if (messages[i]) {
          lastMessage = Utils.parseMessage(messages[i], this.pushstream);
          this.messagesQueue.push(lastMessage);
          if (this.pushstream.messagesControlByArgument && lastMessage.time) {
            this.pushstream._etag = lastMessage.tag;
            this.pushstream._lastModified = lastMessage.time;
          }
        }
      }

      this._listen();

      while (this.messagesQueue.length > 0) {
        var message = this.messagesQueue.shift();
        this.pushstream._onmessage(message.text, message.id, message.channel, message.eventid, (this.messagesQueue.length === 0));
      }
    }
  };

  /* mains class */

  var PushStreamManager = [];

  var PushStream = function(settings) {
    settings = settings || {};

    this.id = PushStreamManager.push(this) - 1;

    this.useSSL = settings.useSSL || false;
    this.host = settings.host || window.location.hostname;
    this.port = Number(settings.port || (this.useSSL ? 443 : 80));

    this.timeout = settings.timeout || 30000;
    this.pingtimeout = settings.pingtimeout || 30000;
    this.reconnectOnTimeoutInterval = settings.reconnectOnTimeoutInterval || 3000;
    this.reconnectOnChannelUnavailableInterval = settings.reconnectOnChannelUnavailableInterval || 60000;

    this.lastEventId = settings.lastEventId || null;
    this.messagesPublishedAfter = settings.messagesPublishedAfter;
    this.messagesControlByArgument = settings.messagesControlByArgument || false;
    this.tagArgument   = settings.tagArgument  || 'tag';
    this.timeArgument  = settings.timeArgument || 'time';
    this.eventIdArgument  = settings.eventIdArgument || 'eventid';
    this.useJSONP      = settings.useJSONP     || false;

    this._reconnecttimer = null;
    this._etag = 0;
    this._lastModified = null;
    this._lastEventId = null;

    this.urlPrefixPublisher   = settings.urlPrefixPublisher   || '/pub';
    this.urlPrefixStream      = settings.urlPrefixStream      || '/sub';
    this.urlPrefixEventsource = settings.urlPrefixEventsource || '/ev';
    this.urlPrefixLongpolling = settings.urlPrefixLongpolling || '/lp';
    this.urlPrefixWebsocket   = settings.urlPrefixWebsocket   || '/ws';

    this.jsonIdKey      = settings.jsonIdKey      || 'id';
    this.jsonChannelKey = settings.jsonChannelKey || 'channel';
    this.jsonTextKey    = settings.jsonTextKey    || 'text';
    this.jsonTagKey     = settings.jsonTagKey     || 'tag';
    this.jsonTimeKey    = settings.jsonTimeKey    || 'time';
    this.jsonEventIdKey = settings.jsonEventIdKey || 'eventid';

    this.modes = (settings.modes || 'eventsource|websocket|stream|longpolling').split('|');
    this.wrappers = [];
    this.wrapper = null;

    this.onchanneldeleted = settings.onchanneldeleted || null;
    this.onmessage = settings.onmessage || null;
    this.onerror = settings.onerror || null;
    this.onstatuschange = settings.onstatuschange || null;
    this.extraParams    = settings.extraParams    || function() { return {}; };

    this.channels = {};
    this.channelsCount = 0;
    this.channelsByArgument   = settings.channelsByArgument   || false;
    this.channelsArgument     = settings.channelsArgument     || 'channels';


    for (var i = 0; i < this.modes.length; i++) {
      try {
        var wrapper = null;
        switch (this.modes[i]) {
        case "websocket"  : wrapper = new WebSocketWrapper(this);   break;
        case "eventsource": wrapper = new EventSourceWrapper(this); break;
        case "longpolling": wrapper = new LongPollingWrapper(this); break;
        case "stream"     : wrapper = new StreamWrapper(this);      break;
        }
        this.wrappers[this.wrappers.length] = wrapper;
      } catch(e) { Log4js.info(e); }
    }

    this.readyState = 0;
  };

  /* constants */
  PushStream.LOG_LEVEL = 'error'; /* debug, info, error */
  PushStream.LOG_OUTPUT_ELEMENT_ID = 'Log4jsLogOutput';

  /* status codes */
  PushStream.CLOSED        = 0;
  PushStream.CONNECTING    = 1;
  PushStream.OPEN          = 2;

  /* main code */
  PushStream.prototype = {
    addChannel: function(channel, options) {
      if (escapeText(channel) !== channel) {
        throw "Invalid channel name! Channel has to be a set of [a-zA-Z0-9]";
      }
      Log4js.debug("entering addChannel");
      if (typeof(this.channels[channel]) !== "undefined") { throw "Cannot add channel " + channel + ": already subscribed"; }
      options = options || {};
      Log4js.info("adding channel", channel, options);
      this.channels[channel] = options;
      this.channelsCount++;
      if (this.readyState !== PushStream.CLOSED) { this.connect(); }
      Log4js.debug("leaving addChannel");
    },

    removeChannel: function(channel) {
      if (this.channels[channel]) {
        Log4js.info("removing channel", channel);
        delete this.channels[channel];
        this.channelsCount--;
      }
    },

    removeAllChannels: function() {
      Log4js.info("removing all channels");
      this.channels = {};
      this.channelsCount = 0;
    },

    _setState: function(state) {
      if (this.readyState !== state) {
        Log4js.info("status changed", state);
        this.readyState = state;
        if (this.onstatuschange) {
          this.onstatuschange(this.readyState);
        }
      }
    },

    connect: function() {
      Log4js.debug("entering connect");
      if (!this.host)                 { throw "PushStream host not specified"; }
      if (isNaN(this.port))           { throw "PushStream port not specified"; }
      if (!this.channelsCount)        { throw "No channels specified"; }
      if (this.wrappers.length === 0) { throw "No available support for this browser"; }

      this._keepConnected = true;
      this._lastUsedMode = 0;
      this._connect();

      Log4js.debug("leaving connect");
    },

    disconnect: function() {
      Log4js.debug("entering disconnect");
      this._keepConnected = false;
      this._disconnect();
      this._setState(PushStream.CLOSED);
      Log4js.info("disconnected");
      Log4js.debug("leaving disconnect");
    },

    _useControlArguments :function() {
      return this.messagesControlByArgument && ((this._lastModified !== null) || (this._lastEventId !== null));
    },

    _connect: function() {
      if (this._lastEventId === null) {
        this._lastEventId = this.lastEventId;
      }
      if (this._lastModified === null) {
        var date = this.messagesPublishedAfter;
        if (!isDate(date)) {
          var messagesPublishedAfter = Number(this.messagesPublishedAfter);
          if (messagesPublishedAfter > 0) {
            date = new Date();
            date.setTime(date.getTime() - (messagesPublishedAfter * 1000));
          } else if (messagesPublishedAfter < 0) {
            date = new Date(0);
          }
        }

        if (isDate(date)) {
          this._lastModified = Utils.dateToUTCString(date);
        }
      }

      this._disconnect();
      this._setState(PushStream.CONNECTING);
      this.wrapper = this.wrappers[this._lastUsedMode++ % this.wrappers.length];

      try {
        this.wrapper.connect();
      } catch (e) {
        //each wrapper has a cleanup routine at disconnect method
        if (this.wrapper) {
          this.wrapper.disconnect();
        }
      }
    },

    _disconnect: function() {
      this._reconnecttimer = clearTimer(this._reconnecttimer);
      if (this.wrapper) {
        this.wrapper.disconnect();
      }
    },

    _onopen: function() {
      this._reconnecttimer = clearTimer(this._reconnecttimer);
      this._setState(PushStream.OPEN);
      if (this._lastUsedMode > 0) {
        this._lastUsedMode--; //use same mode on next connection
      }
    },

    _onclose: function() {
      this._reconnecttimer = clearTimer(this._reconnecttimer);
      this._setState(PushStream.CLOSED);
      this._reconnect(this.reconnectOnTimeoutInterval);
    },

    _onmessage: function(text, id, channel, eventid, isLastMessageFromBatch) {
      Log4js.debug("message", text, id, channel, eventid, isLastMessageFromBatch);
      if (id === -2) {
        if (this.onchanneldeleted) { this.onchanneldeleted(channel); }
      } else if (id > 0) {
        if (this.onmessage) { this.onmessage(text, id, channel, eventid, isLastMessageFromBatch); }
      }
    },

    _onerror: function(error) {
      this._setState(PushStream.CLOSED);
      this._reconnect((error.type === "timeout") ? this.reconnectOnTimeoutInterval : this.reconnectOnChannelUnavailableInterval);
      if (this.onerror) { this.onerror(error); }
    },

    _reconnect: function(timeout) {
      if (this._keepConnected && !this._reconnecttimer && (this.readyState !== PushStream.CONNECTING)) {
        Log4js.info("trying to reconnect in", timeout);
        this._reconnecttimer = window.setTimeout(linker(this._connect, this), timeout);
      }
    },

    sendMessage: function(message, successCallback, errorCallback) {
      message = escapeText(message);
      if (this.wrapper.type === WebSocketWrapper.TYPE) {
        this.wrapper.sendMessage(message);
        if (successCallback) { successCallback(); }
      } else {
        Ajax.post({url: getPublisherUrl(this), data: message, success: successCallback, error: errorCallback});
      }
    }
  };

  PushStream.sendMessage = function(url, message, successCallback, errorCallback) {
    Ajax.post({url: url, data: escapeText(message), success: successCallback, error: errorCallback});
  };

  // to make server header template more clear, it calls register and
  // by a url parameter we find the stream wrapper instance
  PushStream.register = function(iframe) {
    var matcher = iframe.window.location.href.match(/streamid=([0-9]*)&?/);
    if (matcher[1] && PushStreamManager[matcher[1]]) {
      PushStreamManager[matcher[1]].wrapper.register(iframe);
    }
  };

  PushStream.unload = function() {
    for (var i = 0; i < PushStreamManager.length; i++) {
      try { PushStreamManager[i].disconnect(); } catch(e){}
    }
  };

  /* make class public */
  window.PushStream = PushStream;
  window.PushStreamManager = PushStreamManager;

  if (window.attachEvent) { window.attachEvent("onunload", PushStream.unload); }
  if (window.addEventListener) { window.addEventListener.call(window, "unload", PushStream.unload, false); }

})(window, document);

try{stManager.done('pushstream.js');}catch(e){}