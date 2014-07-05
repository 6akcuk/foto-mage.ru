/**
 * ScrollBar
 */

function Scrollbar(obj, options) {
  this.obj = obj = $(obj);
  this.options = options || {};
  this.clPref = options.prefix || '';

  setTimeout((function() {
    obj.css({
      overflow: 'hidden'
    });

    var size = {width: obj.outerWidth(), height: obj.outerHeight()}, s = {
      marginLeft: size.width - 7,
      height: size.height
    };
    if (options.nomargin) {
      delete s.marginLeft;
      s.right = options.right || 0;
      s.left = options.left || 0;
    }
    this.scrollHeight = size.height;

    this.scrollbar = $('<div/>').addClass(this.clPref +'scrollbar_cont').css(s);
    this.inner = $('<div/>').addClass(this.clPref + 'scrollbar_inner').appendTo(this.scrollbar);

    if (options.shadows) {
      this.topShadowDiv = $('<div/>').addClass(this.clPref + 'scrollbar_top').css({width: size.width}).insertBefore(obj);
      this.bottomShadowDiv = $('<div/>').addClass(this.clPref + 'scrollbar_bottom').css({width: size.width}).insertBefore(obj.next());
    }

    this.scrollbar.insertBefore(obj);

    this.destroyList = [];

    this.mouseMove = this._mouseMove.bind(this);
    this.mouseUp = this._mouseUp.bind(this);
    var self = this;
    function down(event) {
      if (self.moveY) return;
      $(window.document).bind('mousemove', self.mouseMove);
      $(window.document).bind('mouseup', self.mouseUp);
      self.moveY = event.pageY - (self.inner.offset().top || 0);

      window.document.body.style.cursor = 'pointer';
      $(self.inner).addClass(self.clPref + 'scrollbar_hovered');
      if (options.startDrag) {
        options.startDrag();
      }
      if (options.onHold) {
        options.onHold(true);
      }
      self.isDown = true;
      return false;// cancelEvent(event);
    }
    this.mouseDown = down;
    function keydown(event) {
      switch ((event || window.event).keyCode) {
        case 40:  self.obj.scrollTop(self.obj.scrollTop() + 40); break;
        case 38:  self.obj.scrollTop(self.obj.scrollTop() - 40); break;
        case 34:  self.obj.scrollTop(self.obj.scrollTop() + self.scrollHeight); break;
        case 33:  self.obj.scrollTop(self.obj.scrollTop() - self.scrollHeight); break;
        default: return true;
      }
      self.update(true);
      return false;//cancelEvent(event);
    }
    var wheel = this.wheel.bind(this);
    obj.bind('mousewheel', wheel);
    obj.bind('DOMMouseScroll', wheel);
    $(this.scrollbar).bind('mousewheel', wheel);
    $(this.scrollbar).bind('DOMMouseScroll', wheel);

    $(this.scrollbar).bind('mouseover', this.contOver.bind(this));
    $(this.scrollbar).bind('mouseout', this.contOut.bind(this));
    $(this.scrollbar).bind('mousedown', this.contDown.bind(this));

    if (browser.safari_mobile) {
      var touchstart = function(event) {
        cur.touchY  = event.touches[0].pageY;
      };
      var touchmove = function(event) {
        var touchY = event.touches[0].pageY;
        cur.touchDiff = cur.touchY - touchY;
        obj.scrollTop += cur.touchDiff;
        cur.touchY = touchY;
        if (obj.scrollTop > 0 && self.shown !== false) {
          self.update(true);
          return false;// cancelEvent(event);
        }
      };
      var touchend = function() {
        cur.animateInt = setInterval(function() {
          cur.touchDiff = cur.touchDiff * 0.9;
          if (cur.touchDiff < 1 && cur.touchDiff > -1) {
            clearInterval(cur.animateInt);
          } else {
            obj.scrollTop += cur.touchDiff;
            self.update(true);
          }
        }, 0);
      };
      $(obj).bind('touchstart', touchstart);
      $(obj).bind('touchmove', touchmove);
      $(obj).bind('touchend', touchend);

      this.destroyList.push(function() {
        $(obj).unbind('touchstart', touchstart);
        $(obj).unbind('touchmove', touchmove);
        $(obj).unbind('touchend', touchend);
      });
    }

    this.inner.bind('mousedown', down);
    if (!options.nokeys) {
      $(window).bind('keydown', keydown);
    } else {
      this.onkeydown = keydown;
    }

    this.destroyList.push(function() {
      obj.unbind('mousewheel', wheel);
      obj.unbind('DOMMouseScroll', wheel);
      self.inner.bind('mousedown', down);
      $(window).unbind('keydown', keydown);
    });

    if (this.contHeight() <= this.scrollHeight) {
      this.bottomShadowDiv && this.bottomShadowDiv.hide();
    } else {
      this.bottomShadow = true;
    }
    this.inited = true;
    this.update(true);

    if (!options.global) {
      cur.destroy.push(this.destroy.bind(this));
    }
  }).bind(this), 0);
}

Scrollbar.prototype.contOver = function() {
  this.isOut = false;
  if (this.shown) {
    this.scrollbar.addClass('scrollbar_c_overed');
  }
}
Scrollbar.prototype.contOut = function() {
  this.isOut = true;
  if (this.isDown) return;
  this.scrollbar.removeClass('scrollbar_c_overed');
}
Scrollbar.prototype.contDown = function(ev) {
  var y = ev.offsetY - this.innerHeight / 2 + 5;// - this.innerHeight;
  var scrH = this.scrollHeight - this.innerHeight;

  var newScroll = Math.floor((this.contHeight() - this.scrollHeight) * Math.min(1, y / scrH));
  this.obj.scrollTop(newScroll);
  this.update(true);
  this.mouseDown(ev);
}

Scrollbar.prototype._mouseMove = function(event) {
  this.obj.scrollTop(Math.floor((this.contHeight() - this.scrollHeight) * Math.min(1, (event.pageY - this.moveY) / (this.scrollHeight - this.innerHeight - 6))));
  this.update(true);
  return false;
}

Scrollbar.prototype._mouseUp = function(event) {
  this.moveY = false;
  this.isDown = false;
  if (this.isOut) {
    this.contOut();
  }
  $(window.document).unbind('mousemove', this.mouseMove);
  $(window.document).unbind('mouseup', this.mouseUp);
  window.document.body.style.cursor = 'default';
  $(this.inner).removeClass(this.clPref + 'scrollbar_hovered');
  if (this.options.stopDrag) {
    this.options.stopDrag();
  }
  if (this.options.onHold) {
    this.options.onHold(false);
  }
  return false;
}

Scrollbar.prototype.wheel = function(event) {
  if (this.disabled) {
    return;
  }
  event = event.originalEvent;
  if (!event) event = window.event;
  var delta = 0;
  if (event.wheelDeltaY || event.wheelDelta) {
    delta = (event.wheelDeltaY || event.wheelDelta) / 2;
  } else if (event.detail) {
    delta = -event.detail * 10
  }
  var stWas = this.obj.scrollTop();
  this.obj.scrollTop(this.obj.scrollTop() - delta);

  if (this.options.onScroll) {
    this.options.onScroll(delta);
  }

  if (stWas != this.obj.scrollTop() && this.shown !== false) {
    this.update(true);
    this.inner.addClass(this.clPref + 'scrollbar_hovered');
    clearTimeout(this.moveTimeout);
    this.moveTimeout = setTimeout((function() {
      this.inner.removeClass(this.clPref + 'scrollbar_hovered');
    }).bind(this), 300);
  }
  if (this.shown) {
    return false;
  }
}

Scrollbar.prototype.hide = function() {
  this.topShadowDiv && this.topShadowDiv.hide(); this.bottomShadowDiv && this.bottomShadowDiv.hide(); this.scrollbar.hide();
  this.hidden = true;
}
Scrollbar.prototype.show = function() {
  this.topShadowDiv && this.topShadowDiv.show(); this.bottomShadowDiv && this.bottomShadowDiv.show(); this.scrollbar.show();
  this.hidden = false;
}
Scrollbar.prototype.disable = function() {
  this.hide();
  this.scrollTop(0);
  this.disabled = true;
}
Scrollbar.prototype.enable = function() {
  this.show();
  this.update();
  this.disabled = false;
}

Scrollbar.prototype.scrollTop = function(top) {
  this.obj.scrollTop(parseInt(top));
  this.update(false, true);
}

Scrollbar.prototype.destroy = function(top) {
  $.each(this.destroyList, function (k, f) {f();});
}

Scrollbar.prototype.contHeight = function() {
  if (this.options.contHeight) {
    return this.options.contHeight;
  }
  if (this.contHashCash) {
    return this.contHashCash;
  }
  var nodes = this.obj.children();
  var height = 0;
  var i = nodes.length;
  while (i--) {
    height += $(nodes[i]).height() || 0;
  }
  this.contHashCash = height;
  return height;
}

Scrollbar.prototype.val = function(value) {
  if (value) {
    this.obj.scrollTop(value);
    this.update(true, true);
  }
  return this.obj.scrollTop();
}

Scrollbar.prototype.update = function(noChange, updateScroll) {
  if (!this.inited || this.hidden) {
    return;
  }
  if (!noChange) {
    this.contHashCash = false;
    if (this.moveY) {
      return true;
    }
  }
  if (updateScroll) {
    this.scrollHeight = $(this.obj).height();
    this.scrollbar.css('height', $(this.obj).height());
  }
  var height = this.contHeight();
  if (height <= this.scrollHeight) {
    this.inner.hide(); this.bottomShadowDiv && this.bottomShadowDiv.hide(); this.topShadowDiv && this.topShadowDiv.hide();
    $(this.scrollbar).css({pointerEvents: 'none'});
    this.topShadow = this.bottomShadow = false;
    this.shown = false;
    return;
  } else if (!this.shown) {
    this.inner.show();
    $(this.scrollbar).css({pointerEvents: 'auto'});
    this.shown = true;
  }

  var topScroll = this.val();

  if (this.options.scrollChange) {
    this.options.scrollChange(topScroll);
  }

  var progress = this.lastProgress = Math.min(1, topScroll / (height - this.scrollHeight));

  if (progress > 0 != (this.topShadow ? true : false)) {
    (this.topShadowDiv) ? (this.topShadow ? this.topShadowDiv.hide() : this.topShadowDiv.show()) : '';
    this.topShadow = !this.topShadow;
  }
  if (progress < 1 != (this.bottomShadow ? true : false)) {
    (this.bottomShadowDiv) ? (this.bottomShadow ? this.bottomShadowDiv.hide() : this.bottomShadowDiv.show()) : '';
    this.bottomShadow = !this.bottomShadow;
  }

  this.innerHeight = Math.max(40, Math.floor(this.scrollHeight * this.scrollHeight / height));
  this.inner.height(this.innerHeight);
  this.inner.css({marginTop: Math.floor((this.scrollHeight - this.innerHeight - 4) * progress + 2)});

  if (this.options.more && $.isFunction(this.options.more) && (this.options.contHeight || (height - this.obj.scrollTop() < this.scrollHeight * 2))) {
    this.options.more();
  }
}

try{stManager.done('scrollbar.js');}catch(e){}