function getUUID() {
    return A.uuid++;
}

/** VK.com (common.js) Useful functions */
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

/* Report Window */
var report_window = {
    margin: 20,

    create: function(over, pos, text) {
        var over = $(over),
            id = 'rw-'+ over.attr('id'),
            left = 0,
            top = 0;

        $('#'+ id).remove();

        var rw = $('<div id="'+ id +'" class="report_window">' +
            '<a class="close iconify_x_a"></a>'
            + text +'' +
            '</div>').appendTo('body');

        rw.find('a.close').click(report_window.remove);

        if (pos == 'left') {
            top = over.offset().top;
            left = over.offset().left - report_window.margin - rw.outerWidth();
        }
        else if (pos == 'right') {
            top = over.offset().top;
            left = over.offset().left + report_window.margin + rw.outerWidth();
        }
        else if (pos == 'top') {
            top = over.offset().top - report_window.margin - rw.outerHeight();
            left = over.offset().left + (over.outerWidth() / 2) - (rw.outerWidth() / 2);
        }
        else if (pos == 'bottom') {
            top = over.offset().top + over.outerHeight() + report_window.margin;
            left = over.offset().left + (over.outerWidth() / 2) - (rw.outerWidth() / 2);
        }

        rw.show().css({
            left: left,
            top: top
        })
    },

    remove: function() {
        $(this).parent().remove();
    }
};

/* Input Error */
var inputError = function(after, msg) {
  if ($(after).parent().hasClass('input_placeholder')) after = $(after).parent();
  $('<span/>').addClass('input_error').html(msg).insertAfter(after);
}

/* Tooltip */
var Tooltip = {
    cur: null,
    d: null,

    show: function(el) {
        if ($(el).attr('title') == '') return;

        var c = $(el),
            t = $('<div/>').addClass('tooltip')
            .html('<div class="tt_bg"></div><div class="tt_text">'+ c.attr('title') + '</div>')
            .appendTo('body'),
            b = t.find('div.tt_bg');
        Tooltip.cur = t;
        Tooltip.d = c;
        c.attr('title', '');
        t.css({
            top: parseInt(c.offset().top - 10 - t.outerHeight()),
            left: parseInt(c.offset().left + (c.outerWidth() / 2) - (t.outerWidth() / 2))
        });
        b.css({
            width: t.find('div.tt_text').outerWidth(),
            height: t.find('div.tt_text').outerHeight()
        });
    },
    hide: function() {
        if (Tooltip.cur) {
            Tooltip.d.attr('title', Tooltip.cur.text());
            Tooltip.cur.remove();
        }
        Tooltip.cur = null;
    },
    init: function() {
        $('div.tooltip').remove();
        $('.tt, [rel="tooltip"]').mouseenter(function() {
                Tooltip.show(this)
            }).mouseleave(function() {
                Tooltip.hide();
            });
    }
};

/* Popup Helper */
$.fn.popupHelp = function(text) {
  this.each(function()
  {
    var $c = $(this), rx, rbx, y, tx, ty, ax, ay;

    $c.focus(function() {
      var $t = $('<div/>').addClass('tooltip popup_helper')
          .html('<div class="tt_bg"></div><div class="tt_text">'+ text + '</div>')
          .appendTo('body'),
        $b = $t.find('div.tt_bg');

      rx = $c.offset().left + $c.outerWidth() + 10;
      rbx = rx + $t.outerWidth() + 10;

      tx = (rbx > $(window).width())
        ? ($c.offset().left + ($c.outerWidth() - $t.outerWidth()) / 2)
        : rx;
      ty = (rbx > $(window).width())
        ? ($c.offset().top - 10 - $t.outerHeight())
        : ($c.offset().top + ($c.outerHeight() - $t.outerHeight()) / 2);

      ax = (rbx > $(window).width()) ? tx : tx + 15;
      ay = (rbx > $(window).width()) ? ty - 15 : ty;

      $t.css({
        top: ay,
        left: ax
      });
      $t.animate({
        top: ty,
        left: tx
      }, 100);
      $b.css({
        width: $t.find('div.tt_text').outerWidth(),
        height: $t.find('div.tt_text').outerHeight()
      });
    });
    $c.blur(function() {
      $('div.tooltip').remove();
    });
  });
};

/* AJAX Exception Window */
var ajex = {
  show: function(msg) {
    var _lAjx = $('<div class="ajex">'+ msg +'</div>').appendTo('body');
    setTimeout(function() {
      _lAjx.remove();
    }, 8000);
  }
};

/* Input Placeholder */
var input_ph = {
    focus: function() {
        $(this).next().hide();
    },
    blur: function() {
        if ($.trim($(this).val()) == '') $(this).next().show();
    },
    subscribe: function() {
        $(this).next().css({
            top: parseInt($(this).css('paddingTop')) / 2,
            left: $(this).css('paddingLeft'),
            display: (($(this).val() == '') ? 'block' : 'none')
        }).click(function() {
            $(this).prev().focus();
        });
    },
    contentChanged: function() {
        $('#content span.input_placeholder input, #content span.input_placeholder textarea')
            .focus(input_ph.focus)
            .blur(input_ph.blur)
            .each(input_ph.subscribe);
    }
};

$.fn.inputPlaceholder = function() {
  this.each(function()
  {
    $(this).focus(function(e) {
      ($(this).val() == '')
        ? $(this).prev().find('.input_back_content').animate({color: '#c0c8d0'})
        : $(this).prev().hide();
    }).blur(function() {
      if ($.trim($(this).val()) == '' && $(this).prev().css('display') == 'none') $(this).prev().css({color: '#777'}).show();
      else if ($.trim($(this).val()) == '') $(this).prev().find('.input_back_content').animate({color: '#777'});
    }).keydown(function() {
      var $this = $(this);
      setTimeout(function() {
        if ($this.val() != '')
          $this.prev().hide();
        else if ($this.val() == '')
          $this.prev().show().find('.input_back_content').animate({color: '#777'});
      }, 1);
    });
    $(this).prev().css({
      display: (($(this).val() == '') ? 'block' : 'none')
    }).click(function() {
      $(this).next().focus();
    });
    $(this).prev().children().css({
      padding: $(this).css('padding')
    })
  });
}

/* Select menu */
var dropdown = {

}

$.fn.dropdown = function() {

    this.each(function()
    {
        // Creating new element
        if (!$(this).hasClass('dropdown')) {

        }

        var c = $(this),
            h = c.children('input[type="hidden"]'),
            t = c.children('span.text'),
            i = c.children('input[type="text"]'),
            m = c.children('ul');

        if (!$(this).hasClass('clearfix')) $(this).addClass('clearfix');
        $(this).click(function(ev) {
            ev.stopPropagation();

            m.show().css({
                width: c.width() - 1,
                left: 0,// c.offset().left,
                top: t.outerHeight() //c.offset().top + c.outerHeight()
            });

            $('body').bind('click', hideDDMenu);
        });

        function hideDDMenu() {
            m.hide();
            $('body').unbind('click', hideDDMenu);
        }

        m.find('a').click(function(ev) {
            ev.stopPropagation();
            t.html($(this).text());
            h.val($(this).attr('data-value'));
            hideDDMenu();

            //if (c.attr('onchange'))
            h.change();// eval(c.attr('onchange'));
        });
    });
}

function objectLen(obj) {
    var l = 0;
    for(var i in obj) {
        l++;
    }
    return l;
}

var WideDropdown = {
    addList: function(id, items) {
        if (!A.wddIList) A.wddIList = {};
        A.wddIList[id] = $.extend(A.wddIList[id] || {}, items);
    },
    addBubbles: function(id, bubbles) {
        if (!A.wddIBubbles) A.wddIBubbles = {};
        A.wddIBubbles[id] = bubbles;
    },
    renderList: function() {
        var id, list, public, curItem = 0, totalItems = 10;

        if ($.isPlainObject(arguments[0])) {
            id = arguments[0].id;
            list = arguments[0].list;
            public = false;
        }
        else {
            id = arguments[0];
            list = A.wddList[id] || {};
            public = true;
        }

        $('#'+ id + ' div.wdd_list').html('');

        if (objectLen(list)) {
            $.each(list, function(value, item) {
                if ((!public || (public && !item.private)) && (!A.wddBubbles[id] || (A.wddBubbles[id] && !A.wddBubbles[id][value])) && curItem < totalItems) {
                    WideDropdown.renderItem(id, value, item);
                    curItem++;
                }
            });
        }
        else {
            $('#'+ id +' div.wdd_list').html('<div class="wddi_no">Пользователь не найден</div>');
        }
    },
    renderItem: function(id, value, item) {
        var $wdd = $('#'+ id),
            $list = $wdd.find('div.wdd_lwrap > .wdd_list');

        $('\
<div class="wddi" id="wddi'+ value +'_'+ id +'" onmousedown="WideDropdown.select(\''+ id +'\', event, \''+ value +'\')" onmouseover="WideDropdown.over(\''+ id +'\', \''+ value +'\')">\
<div class="wddi_data">\
    <b class="left wddi_thumb"><img class="wddi_img" src="'+ item.img +'" /></b>\
    <div class="wddi_text">'+ item.text +'</div>\
<div class="wddi_sub">'+ item.sub +'</div>\
</div>\
</div>').appendTo($list);
    },
    renderBubble: function(id, value) {
        var $wdd = $('#'+ id),
            $bubbles = $wdd.find('div.wdd_bubbles');

        $('\
<div id="wddb'+ value +'" class="summary_tab_sel left" onclick="event.cancelBubble=true">\
    <div class="summary_tab2">\
    <table>\
        <tr>\
            <td>\
                <div class="summary_tab3">\
                    <nobr>'+ A.wddBubbles[id][value].text +'</nobr>\
                </div>\
            </td>\
            <td>\
                <div class="summary_tab_x" onclick="WideDropdown.deselect(\''+ id +'\', event, \''+ value + '\')"></div>\
            </td>\
        </tr>\
    </table>\
    </div>\
</div>').appendTo($bubbles);
    },
    setup: function() {
        A.wddBasicList = {};
        A.wddList = {};
        A.wddFocus = {};
        A.wddBubbles = {};

        if (A.wddIBubbles) {
            $.each(A.wddIBubbles, function(id, bubbles) {
                A.wddBubbles[id] = bubbles;

                $.each(A.wddBubbles[id], function(bid, value) {
                    WideDropdown.addBubble(id, bid);
                });

                WideDropdown.refreshAdd(id);
            });
        }
        A.wddIBubbles = {};

        if (A.wddIList) {
            $.each(A.wddIList, function(id, list) {
                A.wddList[id] = list;
            });
        }
        A.wddIList = {};

        $('div.wdd').each(function()
        {
            var id = $(this).attr('id');
            $(this).children('.wdd_arrow').click(function() {
                WideDropdown.show(id, event);
            })
            $(this).find('input[type="text"]').bind('keydown', function(event) {
                setTimeout(function() {
                    WideDropdown.onKeyEvent(id, event);
                }, 1);
            });
        });
    },
    addBubble: function(id, value) {
        WideDropdown.renderBubble(id, value);
        $('<input/>').attr({type: 'hidden', id: 'wddh'+ value, name: id +'[]'}).val(value).appendTo('#'+ id);
    },
    select: function(id, event, value) {
        if (!A.wddBubbles[id]) A.wddBubbles[id] = {};
        A.wddBubbles[id][value] = A.wddList[id][value];
        WideDropdown.hide(id);
        WideDropdown.renderList(id);
        WideDropdown.addBubble(id, value);

        if (A.wddOnSelect && A.wddOnSelect[id]) A.wddOnSelect[id]();
    },
    deselect: function(id, event, value) {
        delete A.wddBubbles[id][value];
        $('#wddb'+ value).remove();
        $('#wddh'+ value).remove();
        WideDropdown.refreshAdd(id);
        WideDropdown.renderList(id);

        if (A.wddOnDeselect && A.wddOnDeselect[id]) A.wddOnDeselect[id]();
    },
    countBubbleSize: function(id) {
        var cnt = 0;
        for(var i in A.wddBubbles[id]) {
            cnt++;
        }
        return cnt;
    },
    refreshAdd: function(id) {
        if (WideDropdown.countBubbleSize(id) && !A.wddFocus[id]) {
            $('#'+ id +' div.wdd_add').show();
            $('#'+ id +' span.input_placeholder').hide();
        }
        else {
            $('#'+ id +' div.wdd_add').hide();
            $('#'+ id +' span.input_placeholder').show();
        }
    },
    over: function(id, value) {
        var $wdd = $('#'+ id),
            $wddlist = $wdd.find('div.wdd_list');
        $wddlist.children('div.wddi_over').removeClass('wddi_over').addClass('wddi');
        $('#wddi'+ value +'_'+ id).removeClass('wddi').addClass('wddi_over');
    },
    show: function(id, event) {
        event.stopPropagation();

        var $wdd = $('#'+ id),
            $lwrap = $wdd.children('.wdd_lwrap');

        if (!$lwrap.children('.wdd_list').children().length) {
            A.wddBasicList[id] = A.wddList[id];
            WideDropdown.renderList(id);
        }

        $lwrap.css({top: $wdd.outerHeight() - 1, width: $wdd.outerWidth()}).show();
        $('body').one('click', function() {
            WideDropdown.hide(id, event);
        });

        if (!A.wddFocus[id]) {
            A.wddFocus[id] = 1;
            $wdd.find('input').focus();
        }

        WideDropdown.refreshAdd(id);
    },
    hide: function(id, event) {
        var $wdd = $('#'+ id),
            $lwrap = $wdd.children('.wdd_lwrap');
        $lwrap.hide();
        $wdd.find('input').blur();

        WideDropdown.refreshAdd(id);
    },
    setFocused: function(id, event) {
        if (!A.wddFocus[id]) {
            A.wddFocus[id] = 1;
            //WideDropdown.show(id, event);
        }
    },
    setUnfocused: function(id, event) {
        A.wddFocus[id] = 0;
        //WideDropdown.hide(id, event);
    },
    onKeyEvent: function(id, event) {
        if (A.wddList[id]) {
            var text = $.trim($(event.target).val());
            WideDropdown.renderList((text) ? {id: id, list: WideDropdown.search(A.wddList[id], text)} : id);
        }
    },
    search: function(list, text) {
        var result = {};
        $.each(list, function(i, item) {
            if (item.text.match(new RegExp(text, 'i'))) {
                result[i] = {};
                result[i] = {img: item.img, text: item.text, sub: item.sub}
                result[i].text = result[i].text.replace(new RegExp('('+ text +')', 'i'), '<b>$1</b>');
            }
        })
        return result;
    }
};

/* Calendar */
$.fn.calendar = function() {
    this.each(function()
    {
        if (!$(this).hasClass('input_calendar'))
            $(this).addClass('input_calendar');

        /** (C)ontainer, (H)eader, (T)itle, (W)eekdays, (L)ist, (P)revious, (N)ext */
        var over = $(this),
            c, h, t, w, l, p, n;

        var _c = Calendar = {
            months: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
            lmonths: ['Января','Февраля','Марта','Апреля','Мая','Июня','Июля','Августа','Сентября','Октября','Ноября','Декабря'],
            curMY: null,
            default: '',
            isFirst: true,

            prev: function() {
                var month = _c.curMY[0],
                    year = _c.curMY[1];

                if (month == 0) {
                    month = 11;
                    year--;
                }
                else month--;

                _c.curMY = [month, year];

                _c.renderTitle();
                _c.renderDays();
                _c.reposite();
            },
            next: function() {
                var month = _c.curMY[0],
                    year = _c.curMY[1];

                if (month == 11) {
                    month = 0;
                    year++;
                }
                else month++;

                _c.curMY = [month, year];

                _c.renderTitle();
                _c.renderDays();
                _c.reposite();
            },

            renderTitle: function() {
                t.html(_c.months[_c.curMY[0]] + ' '+ _c.curMY[1]);
            },

            renderDays: function() {
                var curDate = new Date(_c.curMY[1], _c.curMY[0]),
                    prevMonth = new Date(_c.curMY[1], _c.curMY[0], 1),
                    daysNum = new Date(_c.curMY[1], _c.curMY[0]+1, 0).getDate(),
                    firstDay = prevMonth.getDay(),
                    date = 1;

                l.html('');
                firstDay = (firstDay == 0) ? 6 : firstDay - 1;
                for(var d = 0; d < 7; d++) {
                    if (d < firstDay) $('<li><em>&nbsp;</em></li>').appendTo(l);
                    else {
                        _c.renderDay(date);
                        date++;
                    }
                }
                for(;date <= daysNum; date++) {
                    _c.renderDay(date);
                }
            },

            renderDay: function(date) {
                var $li = $('<li><a>'+ date +'</a></li>').appendTo(l),
                    $a  = $li.children('a');
                $a.click(_c.onClickDate);
            },

            onClickDate: function() {
                var date = parseInt($(this).text()),
                    month = _c.curMY[0],
                    year = _c.curMY[1];

                over.attr('title', _c.default);
                over.children('input').val(year +'-'+ (month+1) +'-'+ date);
                over.children('input').blur(); // позволяет вызывать пользовательские события
                over.children('span').html(date + ' '+ _c.lmonths[month] + ' '+ year);
                over.children('em').show();
                _c.hide();
            },

            show: function() {
                c = $('<div/>')
                    .attr({class: 'calendar'})
                    .click(function(ev){ev.stopPropagation()})
                    .appendTo('body')
                    .html('<div class="header clearfix">\
                    <a class="left iconify_prev_a"></a>\
                    <span class="left"></span>\
                    <a class="left iconify_next_a"></a>\
                </div>\
                <ul class="weekdays list"><li>Пн</li><li>Вт</li><li>Ср</li><li>Чт</li><li>Пт</li><li>Сб</li><li>Вс</li></ul>\
                <ul class="list"></ul>\
                ');
                h = c.find('div.header');
                t = h.find('span');
                w = c.find('ul.weekdays');
                l = w.next();

                h.find('a.iconify_prev_a').click(_c.prev),
                h.find('a.iconify_next_a').click(_c.next);

                if (!_c.curMY) _c.curMY = [new Date().getMonth(), new Date().getFullYear()];
                _c.renderTitle();
                _c.renderDays();

                _c.reposite();

                setTimeout(function() {
                    $('body').bind('click', _c.hide);
                }, 100);
            },
            hide: function() {
                $('body').unbind('click', _c.hide);
                c.remove();
            },
            clear: function(e) {
                e.stopPropagation();
                over.children('em').hide();
                over.children('span').html(_c.default);
                over.attr('title', '');
                over.children('input').val('');
                over.children('input').blur(); // позволяет вызывать пользовательские события
            },
            checkPreinit: function() {
                var dt = over.find('input').val(),
                    data = [];

                if (dt != '') {
                    data = dt.split('-');
                    var date = data[2], month = parseInt(data[1]) - 1, year = parseInt(data[0]);
                    _c.curMY = [month, year];
                    over.children('em').show();
                    over.children('span').html(date + ' '+ _c.lmonths[month] + ' '+ year);
                    over.attr('title', _c.default);
                }
            },

            reposite: function() {
                c.css({
                    left: over.offset().left + over.outerWidth() / 2 - c.outerWidth() / 2,
                    top: over.offset().top - 10 - c.outerHeight()
                });
            }
        }

        over.click(Calendar.show);
        over.children('em').click(Calendar.clear);
        Calendar.default = over.find('span').text();

        if (Calendar.isFirst) {
            Calendar.checkPreinit();
            Calendar.isFirst = false;
        }
    });
};

/* Autosize */
/* Source from project: Petsface.ru */
$.fn.autosize = function(options) {
    this.each(function()
    {
        var settings = {
                minheight: 30,
                maxheight: 160,
                lineHeight: '16px',
                padding: 5
            },
            timeout = null,
            enabled = true;

        var ps = $.extend(settings, options),
            $this = $(this),
            $as = $('<div/>')
                .attr({
                    class: 'autosize'
                })
                .css({
                    width: $this.width(),
                    fontSize: $this.css('fontSize'),
                    padding: (ps.padding*2),
                    lineHeight: $this.css('lineHeight')
                })
                .appendTo('#utils');

        oldValue = $this.val();
        if ($this.attr('minheight')) ps.minheight = parseInt($this.attr('minheight'));
        if ($this.attr('maxheight')) ps.maxheight = parseInt($this.attr('maxheight'));
        ps.id = '#'+ $this.attr('id');

        function check(event) {
            var html = $this.val().replace(/\n/g, "<br>");
            if(event.type != 'keyup') {
                if(event.keyCode == 13 && !event.ctrlKey && !event.altKey) {
                    html += '\n';
                }
            }
            if(html == oldValue) return;
            oldValue = html;

            $as.html(html);

            if(ps.maxheight) $this.css('height', Math.max(ps.minheight, Math.min( ($as.outerHeight() + (ps.padding+2)), ps.maxheight) ));
            else $this.css('height', Math.max(ps.minheight, ($as.outerHeight() + (ps.padding+2)) ));

            // покажем scroll при превышении высоты
            if(ps.maxheight) {
                if($as.outerHeight() > ps.maxheight) $this.css('overflow', 'auto');
                else $this.css('overflow', 'hidden');
            }
        }

        function destroy() {
            $as.remove();
            $this.unbind('keyup').unbind('keydown').unbind('keypress');

            enabled = false;
        }

        $this.bind('keyup keydown keypress', check);
    });
}

/* Tabs */
$.fn.activeTabs = function()
{
    this.each(function()
    {
        var $this = $(this);
        $(this).children('a').click(function()
        {
          if ($(this).hasClass('right')) return;
            var $link = $($this.attr('data-link'));
            $this.children('a').removeClass('selected');
            $link.children('div').hide();
            $link.children($(this).attr('target')).show();
            $(this).addClass('selected');
        });
    });
}

/* Quick Menu */
$.fn.quickMenu = function()
{
  this.each(function()
  {
    var $this = $(this);
    $(this).click(function()
    {
      var $nxt = $this.next(), top = 0, left = 0;

      if ($nxt.attr('rel') && $nxt.attr('rel') == 'qmenu') {
        if ($nxt.find('.dd_menu_header').length) {
          top = $this.position().top - 1;
          left = $this.position().left - 1
        }
        else {
          top = $this.position().top + $this.outerHeight();
          left = $this.position().left;
        }
      }
      else {
        top = $this.position().top + $this.outerHeight();
        left = $this.position().left;
      }
      $nxt.show().css({
        top: top,
        left: left
      });

      setTimeout(function() {
        $('body').one('click', function() {
          $this.next().hide();
        });
      }, 1);
    });
  });
}

$.fn.ddMenu = function(opts) {
  this.each(function()
  {
    var $this = $(this);

    if (!$this.data('dd_menu')) {
      var $menu = $('<div class="dd_menu" style="display: none"><div class="dd_menu_header"><div></div></div><div class="dd_menu_body"></div></div>'),
        $header = $menu.find('div.dd_menu_header div'),
        $body = $menu.find('div.dd_menu_body'), $rows;

      (!opts.header) ? $header.parent().remove() : $header.html(opts.header);
      $body.html('<table cellspacing="0" cellpadding="0"><tr>' +
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
      $rows = $body.find('div.dd_menu_rows2');

      $.each(opts.list, function(i, data) {
        $('<a></a>').html(data.title).click(data.click).appendTo($rows);
      });

      $menu.appendTo('body');
      $this.data('dd_menu', $menu);
    }

    var $menu = $this.data('dd_menu'), top = 0, left = 0,
      $header = $menu.find('div.dd_menu_header div'),
      $rows = $menu.find('div.dd_menu_rows2');

    if ($menu.find('.dd_menu_header').length) {
      top = $this.offset().top - 2 - parseInt($header.css('paddingTop'));
      left = $this.offset().left - 2 - parseInt($header.css('paddingLeft'));
    }
    else {
      top = $this.offset().top + $this.outerHeight();
      left = $this.offset().left;
    }
    $menu.show().css({
      top: top,
      left: left
    });
    $menu.find('div.dd_menu_body').css({
      right: 'auto',
      top: $header.outerHeight() + 1
    });
    $rows.css('width', parseInt($header.outerWidth()));

    setTimeout(function() {
      $('body').one('click', function() {
        $menu.hide();
      });
    }, 1);
  });
}

/* Boxes */
var _box_guid = 0;
var _bq = {
  _guids: [],
  _boxes: [],
  curBox: 0,

  hideAll: function() {

  },

  hideLast: function(e) {
    if (e) e.stopPropagation();

    if (_bq.count()) {
      var box = _bq._boxes[_bq._guids[_bq.count() - 1]];
      if (_bq.skip) {
        _bq.skip = false;
        return;
      }
      box.hide();
    }
  },
  hideBGClick: function(e) {
    _bq.hideLast();
  },

  count: function() {
    return _bq._guids.length;
  },
  _showLayer: function() {
    $(document.body).css({
      overflow: 'hidden',
      cursor: 'default'
    });

    A.boxLayerWrap.show().css({
      width: $(window).width(),
      height: $(window).height()
    }).bind('click', _bq.hideBGClick);
    A.boxLayerBG.show().css({
      height: $(window).height()
    }).bind('click', _bq.hideBGClick);
  },
  _hideLayer: function() {
    $('body').css({
      overflow: 'auto'
    });

    A.boxLayerBG.hide().unbind('click', _bq.hideBGClick);
    A.boxLayerWrap.hide().unbind('click', _bq.hideBGClick);
  },
  _show: function(guid) {
    var box = _bq._boxes[guid];
    if (!box) return;
    if (_bq.count()) _bq._boxes[_bq._guids[_bq.count() - 1]].hide();
    _bq.curBox = guid;
    box._show();
    _bq._guids.push(guid);
  },
  _hide: function(guid) {
    var box = _bq._boxes[guid];
    if (!box || _bq._guids[_bq.count() - 1] != guid || !box.isVisible()) return;
    _bq._guids.pop();
    box._hide();
    if (_bq.count()) {
      var prev_guid = _bq._guids[_bq.count() - 1];
      _bq.curBox = prev_guid;
      _bq._boxes[prev_guid]._show();
    }
    else _bq._hideLayer();
  }
};

function curBox() {
  return _bq._boxes[_bq.curBox];
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

  clearTimeout(A.boxPopupTime);
  A.boxPopupTime = setTimeout(_hideMe, 3000);
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
<div class="box_layout" onclick="_bq.skip=true;">\
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
  boxControlsText = boxProgress.next();

  if (!A.boxLayerBG) A.boxLayerBG = $('#box_layer_bg');
  if (!A.boxLayerWrap) A.boxLayerWrap = $('#box_layer_wrap');
  if (!A.boxLayer) A.boxLayer = $('#box_layer');
  boxContainer.appendTo((opts.absolute) ? 'body' : A.boxLayer);

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
    delete _bq._boxes[guid];
  }

  var hideMe = function() {
    if (!visible) return;
    visible = false;

    if ($.isFunction(opts.onHide)) opts.onHide();

    if (opts.selfDestruct) destroyMe();
    else boxContainer.hide();
  }

  var showMe = function() {
    if (visible || !_bq._boxes[guid]) return;
    visible = true;

    if (!opts.absolute || opts.showLayer) _bq._showLayer();
    boxContainer.show();
    boxRefreshCoords(boxContainer);
    if ($.isFunction(opts.onShow)) opts.onShow();
  }

  boxCloseButton.click(function(e) {
    _bq.skip = false;
    _bq.hideLast(e);
  }).mouseenter(function(e) {
    $(this).stop(true).animate({backgroundColor: '#ffffff'}, 300);
  }).mouseleave(function(e) {
    $(this).stop(true).animate({backgroundColor: '#9DB7D4'}, 300);
  });

  function addButton(button) {
    var title = button.title,
      onclick = (button.onclick) ? button.onclick : null,
      gray = (button.gray) ? true : false,
      row = boxControls.find('table tr'),
      btn = $('<td></td>').html('<div class="'+ ((gray) ? 'button_gray' : 'button_blue') +'"><button>'+ title +'</button></div>').appendTo(row);

    if (gray) btn.find('button').click(function() {
      curBox().hide();
    });
    if (onclick) btn.find('button').click(onclick);
  }

  var retBox = _bq._boxes[guid] = {
    guid: guid,
    _show: showMe,
    _hide: hideMe,

    bodyNode: boxBody,

    show: function() {
      _bq._show(guid);
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
      _bq._hide(guid);
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

    destroy: destroyMe
  };
  return retBox;
}

function showConfirmBox(title, msg, yes, onYes, no, onNo) {
  var box = new Box({
    title: title,
    buttons: [{title: yes, onclick: onYes}, {title: no, onclick: onNo, gray: true}]
  });
  box.content(msg);
  box.show();
}

/* Form Easy Control */
var FormMgr = {
    c200: function($form) {
        var url = $form.attr('action');
        $form.attr({action: '/nullForm', target: 'iframe_200', method: 'post'}).submit();
        $form.attr('action', url);
    },
    validate: function(el, pos, onDone) {
        var $form = $(el),
            url = $form.attr('action'),
            data = {};

        if (!pos) pos = 'right';

        $form.find('input').removeClass('error');
        data = $form.serialize();
        data += "&validate=1";

        ajax.post(url, data, function(response) {
            if (response.success) $form.submit();
            else {
                var string = [];
                $.each(response, function(i, v) {
                    $('#'+ i).addClass('error');
                    if ($.isArray(v)) {
                        $.each(v, function(i2, v2) {
                            string[string.length] = v2;
                        })
                    }
                    else string[string.length] = v;
                });

                report_window.create($form, pos, string.join('<br/>'));
            }
        });
    },
    submit: function(el, pos, onDone, onFail) {
        var $form = $(el),
            url = $form.attr('action');

        if (!pos) pos = 'right';

        $form.find('input').removeClass('error');
        $form.find('.input_error').remove();

        ajax.post(url, $form.serialize(), function(response) {
            if (response.success) {
                if ($.isFunction(onDone)) onDone(response);
                else {
                    var scs = $('<div/>').addClass('success').insertAfter($form.children('h2'));
                    scs.html(response.message);
                    if (response.msg) boxPopup(response.msg);
                    if (response.url) nav.go(response.url);
                }
            }
            else {
              if ($.isFunction(onFail)) onFail(response);

              $.each(response, function(i, v) {
                $('#'+ i).addClass('error');
                if ($.isArray(v)) {
                  var string = [];
                  $.each(v, function(i2, v2) {
                    string.push(v2);
                  })
                  //$('<span/>').addClass('input_error').html(string.join('')).insertAfter($('#'+ i));
                  inputError($('#'+ i), string.join(''));
                }
                else inputError($('#'+ i), v);
              });

              //report_window.create($form, pos, string.join('<br/>'));
            }
        }, function(xhr) {
          if ($.isFunction(onFail)) onFail(xhr);
        });

        return false;
    }
};

/* Static Manager */
var stmgr = {
    _css: new RegExp('.css', 'i'),
    _js: new RegExp('.js', 'i'),
    _waiters: [],
    _iv: null,
    _ivWork: false,
    _failover: 10,

    init: function() {
        stmgr._ivWork = true;
        stmgr._iv = setInterval(stmgr.wait, 500);
    },

    wait: function() {
        if (!stmgr._waiters.length) {
            stmgr._waiters = [];
            stmgr._ivWork = false;
            clearInterval(stmgr._iv);
            return;
        }

        $.each(stmgr._waiters, function(i, waiter) {
            if (!waiter) return;

            if (stmgr._css.test(waiter[0])) {
                //alert(waiter.n);
                //alert($('#'+ waiter.n +'_css').css('display'));

                if ($('#'+ waiter[0].replace(/.css/ig, '') +'_css').css('display') == 'none') {
                    stmgr.loaded(waiter[0]);
                    stmgr._waiters.splice(i, 1);
                }
                else stmgr._waiters[i][1]++;

                if (stmgr._waiters[i] && stmgr._waiters[i][1] >= stmgr._failover) {
                    stmgr._waiters.splice(i, 1);
                    ajex.show('Не удалось загрузить '+ waiter[0] +'');
                }
            }
            else if (stmgr._js.test(waiter[0])) {
                if (staticFiles[waiter[0]].l == 1) stmgr._waiters.splice(i, 1);
                else stmgr._waiters[i][1]++;

                if (stmgr._waiters[i] && stmgr._waiters[i][1] >= stmgr._failover) {
                    stmgr._waiters.splice(i, 1);
                    ajex.show('Не удалось загрузить '+ waiter[0] +'');
                }
            }
        });
    },

    add: function(url, name, version) {
        if (staticFiles[name] && staticFiles[name].v == parseInt(version) && staticFiles[name].l) {
            logger.add(name +' уже загружен');
            return;
        }

        logger.add('Загрузка '+ name +' версии '+ version);

        staticFiles[name] = {v: parseInt(version), l: 0};
        stmgr._waiters.push([name, 0]);
        if (!stmgr._ivWork) stmgr.init();

        logger.add(name+ ' в очереди ожидания');

        if (stmgr._css.test(name)) {
            logger.add(name+ ' прошел тест на CSS');

            $('<div/>')
                .attr('id', name.replace(/\.css/ig, '') + '_css')
                .appendTo('#utils');

            $('<link/>')
                .attr({
                    type: 'text/css',
                    rel: 'stylesheet',
                    href: url
                })
                .appendTo('head');
        }
        else if (stmgr._js.test(name)) {
            logger.add(name+ ' прошел тест на JS');
            //alert('Adding '+ name +' '+ url);

            $('<script/>')
                .attr({
                    type: 'text/javascript',
                    src: url
                })
                .appendTo('head');
        }
    },

    loaded: function(name) {
        staticFiles[name]['l'] = 1;
    }
};

/* Logger */
var logger = {
    _vault: [],

    add: function(msg) {
        logger._vault[logger._vault.length] = msg;
    },

    showAll: function() {
        ajex.show(logger._vault.join('<br/>'));
        logger._vault = [];
    }
};

/* Upload */
var Upload = {
    assign: function(map) {
        if (!A.uploadMap) A.uploadMap = {};
        $.extend(A.uploadMap, map || {});
    },
    cancel: function(id) {
        Upload.showInput(id);
        Upload.initFile(id);
    },
    initFile: function(id, customChange) {
        if (!$('#file_button_'+ id +' div.filebutton input[type="file"]').size()) {
            $('<input/>')
                .attr({type: 'file', name: 'photo', id: 'file_upload_'+ id})
                .change(function(){
                    if (customChange) customChange();
                    else Upload.onStart(id)
                })
                .prependTo('#file_button_'+ id +' div.filebutton');
        }
    },
    deleteFile: function(id) {
        var $button = $('#file_button_'+ id);
        $button.prev().val('');
        $button.find('div.filedata img').remove();
        Upload.showInput(id);
        Upload.initFile(id);
    },
    onDOMReady: function($button) {
        var $button = $($button),
            id = parseInt($button.attr('id').replace('file_button_', '')),
            filedata = $.parseJSON($button.prev().val());

        if (filedata && (filedata.x || filedata.doc)) {
            Upload.showData(id);
            Upload.renderData(id, filedata);
        }
        else Upload.showInput(id);
    },
    showProgress: function(id) {
        var $button = $('#file_button_'+ id);
        $button.children('div.fileprogress').show();
        $button.children('div.filedata').hide();
        $button.children('div.filebutton').hide();
    },
    showInput: function(id) {
        var $button = $('#file_button_'+ id);
        $button.children('div.fileprogress').hide();
        $button.children('div.filedata').hide();
        $button.children('div.filebutton').show();
    },
    showData: function(id) {
        var $button = $('#file_button_'+ id);
        $button.children('div.fileprogress').hide();
        $button.children('div.filedata').show();
        $button.children('div.filebutton').hide();
    },
    renderData: function(id, json) {
        if (json['x']) {
          var size = $('#file_button_'+ id).attr('data-image');
          $('<img/>').attr({src: 'http://cs'+ json[size][2] +'.spmix.ru/'+ json[size][0] + '/'+ json[size][1], alt: ''})
            .prependTo('#file_button_'+ id +' div.filedata');
          $('#file_button_'+ id +' div.filedata a.tt').find('em').addClass('icon-white');
        }
        else if (json['doc']) {
          $('<div/>').html('<span class="icon-file"></span> '+ json['doc'][1]).prependTo('#file_button_'+ id +' div.filedata');
          $('#file_button_'+ id +' div.filedata a.tt').find('em').removeClass('icon-white');
        }
    },
    onStart: function(id) {
        var $div = $('<div/>').attr({id: 'file_ctrl_'+ id}).addClass('filectrl').appendTo('#utils'),
            $iframe = $('<iframe/>').attr({
                name: 'upload_iframe_'+ id
            }).appendTo($div),
            $form = $('<form/>').attr({
                id: 'file_form_'+ id,
                action: A.uploadMap.action,
                enctype: 'multipart/form-data',
                method: 'post',
                target: 'upload_iframe_'+ id
            }).appendTo($div),
            $button = $('#file_button_'+ id);

        $('<input/>').attr({type: 'hidden', name: 'form_id', value: id}).appendTo($form);

        cur['fileDone'+ id] = Upload.onDone.pbind(id);
        cur['fileFailed'+ id] = Upload.onFail.pbind(id);

        $form.append($('#file_upload_'+ id));
        $form.submit();
        Upload.showProgress(id);
    },
    onDone: function(id, filedata) {
        $('#file_button_'+ id).prev('input').val(filedata);
        $('#file_ctrl_'+ id).remove();
        Upload.showData(id);
        Upload.renderData(id, $.parseJSON(filedata));
        if (cur['fileDoneCustom'+ id]) cur['fileDoneCustom'+ id](filedata);
    },
    onFail: function(id, message) {
        $('#file_ctrl_'+ id).remove();
        Upload.showInput(id);
        Upload.initFile(id);
        report_window.create('#file_button_'+ id, 'left', message);
    }
};

/* Search Filters */
$.fn.filters = function() {
    this.each(function()
    {
        var $el = $(this).children();

        if ($el.hasClass('input_placeholder')) {
            var $this = $el.find('input');
            $this.blur(function() {
                nav.go('?'+ $this.attr('name') +'='+ $this.val(), event, {search: true, revoke: ($this.val() == '')});
            }).keypress(function(ev) {
                if (ev.keyCode == 13) nav.go('?'+ $this.attr('name') +'='+ $this.val(), event, {search: true});
            });
        }
        else if ($el.hasClass('input_calendar')) {
            var $this = $el.find('input');
            $this.blur(function() {
                nav.go('?'+ $this.attr('name') +'='+ $this.val(), event, {search: true, revoke: ($this.val() == '')});
            });
        }
        else if ($el.attr('tag', 'select')) {
            var $this = $el;
            $this.change(function() {
                nav.go('?'+ $this.attr('name') +'='+ $this.val(), event, {search: true, revoke: ($this.val() == '')});
            });
        }
    });
}

/* Focuser */
$.fn.focuser = function(opts) {
  this.each(function() {
    var settings = {
      startBackgroundColor: '#dae1e8',
      endBackgroundColor: '#ffffff',
      duration: 5000,
      scroll: true,
      focus: true
    };
    var ps = $.extend(settings, opts),
      $win = $(window), scr = $win.scrollTop(),
      st = scr + ($win.height() / 2);

    if (ps.startBackgroundColor) $(this).css({backgroundColor: ps.startBackgroundColor});
    if (ps.endBackgroundColor) $(this).animate({backgroundColor: ps.endBackgroundColor}, ps.duration);
    if (ps.scroll) $win.scrollTop($(this).offset().top - $win.height() / 2);
    if (ps.focus) $(this).find('input[type="text"], textarea')[0].focus();
  });
}

/* Navigation Object */
/* Great respect for VK.com developers */
var nav = {
    _ivCheck: null,
    _tmPage: null,
    curLoc: false,
    objLoc: {},
    request: null,

    q2obj: function(qa) {
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
    },
    obj2q: function(qa) {
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
    },
    revoke: function(qa, str) {
        qa = qa.split('&');
        $.each(qa, function(i, a) {
            if (!a) return;
            var t = a.split('='),
                s = str.split('=');

            if (t[0] == s[0]) qa.splice(i, 1);
        });
        return qa.join('&');
    },
    query: function(loc, opts) {
        var a = loc.split('?'),
            curLoc = nav.curLoc.split('?'),
            obj = nav.q2obj(a[1]),
            curObj = nav.q2obj(curLoc[1]);

        if (a[1]) {
            if (a[0] == '') a[0] = curLoc[0];
            if (!opts.search) nav.objLoc = {};
            $.extend(true, nav.objLoc, (!opts.search) ? {} : curObj, obj);
            q = nav.obj2q(nav.objLoc);
            if (opts.revoke) {
                q = nav.revoke(q, a[1]);
                nav.objLoc = nav.q2obj(q);
            }
          // paginator fix offset
          // них не помню зачем ввел
            if (opts.search) {
              q = nav.revoke(q, 'pages=1');
              //q = nav.revoke(q, 'offset=x');
              nav.objLoc = nav.q2obj(q);
            }
            a[1] = q;
            return a.join('?');
        }
        else {
            nav.objLoc = {};
            return loc;
        }
    },
    go: function(loc, event, _opts) {
      if (loc.tagName && loc.tagName.toLowerCase() == 'a' && loc.href) {
        if (loc.target == '_blank') {
          return;
        }
        loc = loc.href;
      }

      loc = loc.replace(new RegExp('(http://'+ A.host + ')', 'i'), '');

        if (loc) {
          var opts = {
            same: false,
            container: '#content',
            revoke: false
          };
          opts = $.extend(opts, _opts);

          loc = nav.query(loc, opts);

          if (opts.paginator) {
            todo = Paginator.nav(loc.match(/offset=(\d+)/)[1]);
            if (!todo) return false;
          }

          if (loc.match(/\?z=/i)) {
            loc = loc.match(/\?z=(.*)/i)[1];
            opts.box = true;
          }

          var where = loc.split('?');
          if (where[1]) {
              where.url = loc;
              where.params = where[1];
          }
          else {
              where.url = loc;
              where.params = '';
          }

          if (opts.box) where.params += (where.params) ? '&box_request=1' : 'box_request=1';

          if (nav.request) {
            clearTimeout(nav._tmPage);
            nav.request.abort();
          }
          nav.request = $.ajax({
            type: 'POST',
            dataType: 'json',
            url: where.url,
            data: where.params
          });

          if (!opts.box) $('body').addClass('loading');
          else {
            showGlobalPrg();
          }

            nav.request.done(function(response, status, xhr) {
                if (response.guest && A.user_id > 0) {
                    location.href = A.host;
                    return;
                }

                nav.curLoc = (!opts.box) ? loc : nav.curLoc.replace(/\?z=.*/, '') + '?z='+ loc.replace(/\//, '');
                location.hash = (!opts.box) ? loc : nav.curLoc;

                (!opts.box) ? $('body').removeClass('loading') : hideGlobalPrg();
                clearTimeout(nav._tmPage);
                nav.request = null;

                $.each(response.static, function(i, st) {
                    stmgr.add(st[0], st[1], st[2])
                });

                //logger.showAll();

                // Paginator fix
                if (typeof Paginator !== "undefined")
                  Paginator.onNavGo();

                if (!opts.box) {
                  $('title').html(response.title);
                  $(opts.container).html(response.html);
                }
                else {
                  var z = location.hash.match(/\?z=(.*)/i)[1], width = 410;

                  if (z.match(/write/)) width = 502;
                  if (response.boxWidth) width = response.boxWidth;

                  var box = Box({
                    hideButtons: true,
                    bodyStyle: 'padding:0px;border:0px',
                    width: width,
                    onHide: function() {
                      nav.go(nav.curLoc.replace(/\?z=.*/, ''), null);
                    }
                  });
                  box.content(response.html);
                  box.show();
                }

              // ScrollFix Clear
              ScrollFix.removeAll();

              $('#content').trigger('contentChanged');
            });
            nav.request.fail(function(xhr, textStatus, errorThrown) {
              try {
                var r = $.parseJSON(xhr.responseText);
              } catch(e) {}

                if (r && r.guest && A.user_id > 0) {
                    location.href = A.host;
                    return;
                }

              (!opts.box) ? $('body').removeClass('loading') : hideGlobalPrg();
                clearTimeout(nav._tmPage);
                nav.request = null;

                switch (xhr.status) {
                    case 403:
                        ajex.show(r.html);
                        break;
                    case 404:
                        ajex.show('Страница не найдена');
                        break;
                    case 500:

                      if (A.user_id != 1) {
                        $.post('/site/autoreportPhpError', {url: where.url, text: xhr.responseText});
                      }

                      ajex.show('Ошибка в работе приложения. Повторите попытку позже');
                        //ajex.show('Ошибка: '+ (r && r.html) ? r.html : xhr.responseText);
                        break;
                    default:

                      if (A.user_id != 1) {
                        $.post('/site/autoreportPhpError', {url: where.url, text: xhr.responseText});
                      }

                      if (xhr.responseText)
                          ajex.show('Ошибка связи с сервером. Перезагрузите страницу, нажав <b>F5</b>. '+ xhr.responseText);
                }
            });

            clearTimeout(nav._tmPage);
            nav._tmPage = setTimeout(function() {
                //$.jGrowl('Соединение с сервером разорвано, перезагрузите страницу', {theme: 'danger'});
                ajex.show('Соединение с сервером потеряно, попробуйте заново');
              (!opts.box) ? $('body').removeClass('loading') : hideGlobalPrg();
                nav.request.abort();
                nav.request = null;
            }, 30000);
        }
        return false;
    },

    reload: function() {
        nav.curLoc = null;
    },

    check: function() {
        var loc = location.hash.replace(/^#/i, '');
        if (loc == nav.curLoc) return;
        if (loc != '' && !loc.match(/^\//i)) return;
        if (loc == '') {
          location.hash = nav.curLoc;
          return;
        }

        nav.curLoc = loc;
        nav.go(loc);
    },

    init: function() {
        nav.curLoc = location.pathname + location.search;//.replace(/^\/{1}/i, '');
        clearInterval(nav._ivCheck);
        nav._ivCheck = setInterval(nav.check, 200);
    }
};

var ajax = {
    STATUS_OK: 1,
    STATUS_ERR: 0,
    STATUS_EXCEPTION: -1,

    post: function(url, params, onDone, onFail) {
        var request = $.ajax({
            type: 'POST',
            dataType: 'json',
            url: url,
            data: params
        });

        request.done(function(response, status, xhr) {
            clearTimeout(A._tmPost);
            if ($.isFunction(onDone)) onDone(response);
        });
        request.fail(function(xhr, textStatus) {
            clearTimeout(A._tmPost);
            if ($.isFunction(onFail)) onFail(xhr);

            switch (xhr.status) {
                case 403:
                    var r = $.parseJSON(xhr.responseText);
                    ajex.show(r.html);
                    break;
                case 404:
                    ajex.show('Страница не найдена');
                    break;
                case 500:
                  var r = null;
                  try {
                    r = $.parseJSON(xhr.responseText);
                  } catch(e) {}

                  if (A.user_id != 1) {
                    $.post('/site/autoreportPhpError', {url: url, text: xhr.responseText});
                  }

                  if (r && r.html) ajex.show(r.html);
                  else ajex.show('Ошибка в работе приложения. Повторите попытку позже');
                    //var r = $.parseJSON(xhr.responseText);
                    //ajex.show((r && r.html) ? r.html : xhr.responseText);
                    break;
                default:

                  if (A.user_id != 1) {
                    $.post('/site/autoreportPhpError', {url: url, text: xhr.responseText});
                  }

                  ajex.show('Ошибка связи с сервером:<br/> '+ xhr.responseText);
            }
        });

        clearTimeout(A._tmPost);
        A._tmPost = setTimeout(function() {
            //$.jGrowl('Соединение с сервером разорвано, перезагрузите страницу', {theme: 'danger'});
            ajex.show('Соединение с сервером потеряно, попробуйте заново');
            request.fail({responseText: 'соединение разорвано по таймауту'});
            request.abort();
            request = null;
        }, 30000);

        return request;
    },

    script: function(url, options) {
        options = $.extend(options || {}, {
            dataType: "script",
            cache: true,
            url: url
        });

        return jQuery.ajax(options);
    }
};
nav.init();

var cur = [], domReady = false;
window['cur'] = cur;

/* DOM ready */
$().ready(function() {
  $win = $(window);

  $(window).resize(function() {
    $('#stl_left').css({
      width: $('#page_layout').offset().left,
      height: $(window).height()
    });

    if (typeof Paginator !== "undefined") Paginator.onWindowResize();
  }).scroll(function() {
    if ($win.scrollTop() > 150 && !$('body').hasClass('im_fixed')) {
      $('#stl_left').show();
      $('#stl_left').css({
        width: $('#page_layout').offset().left
      });
    }
    else $('#stl_left').hide();
  });

  // up
  $('#stl_left').mouseenter(function() {
      $(this).children('#stl_bg').addClass('hover');
  }).mouseleave(function() {
      $(this).children('#stl_bg').removeClass('hover');
  }).click(function() {
     $win.scrollTop(0);
  });

  // input placeholder
  $('span.input_placeholder input, span.input_placeholder textarea').inputPlaceholder();

  $('div.dropdown').dropdown();
  $('a.input_calendar').calendar();
  Tooltip.init();

  // file uploader
  $('div.fileupload').each(function()
  {
      Upload.onDOMReady(this);
  });

  $('div.tabs').activeTabs();
  $('.smarttext textarea').autosize();
  $('[rel="filters"]').filters();
  WideDropdown.setup();

  $('[rel="menu"]').quickMenu();
  $('div[rel="scrollfix"]').scrollfix();

  // content updates with ajax
  $('#content').on('contentChanged', function(ev, opts) {
      $('#content span.input_placeholder input, #content span.input_placeholder textarea').inputPlaceholder();
      $('div.dropdown').dropdown();
      Tooltip.init();
      $('a.input_calendar').calendar();
      $('div.tabs').activeTabs();

      $('div.autosize').remove();
      $('.smarttext textarea').autosize();

      $('div.fileupload').each(function()
      {
          Upload.onDOMReady(this);
      });

      $('[rel="filters"]').filters();
      WideDropdown.setup();

    $('[rel="menu"]').quickMenu();
    $('div[rel="scrollfix"]').scrollfix();

    $(window).resize();

    if (A.pageScroll) {
      A.pageScroll = false;
    }
    else if (!opts || !opts.noscroll) $(window).scrollTop(0);

    cur = {};
  });

  $(window).resize();
  if (!domReady && nav.curLoc.match(/\?z=/)) {
    nav.go(nav.curLoc, null);
  }

  domReady = true;
});

try {stmgr.loaded('activehtmlelements.js');}catch(e){}