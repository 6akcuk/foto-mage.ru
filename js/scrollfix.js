/**
 * ScrollFix
 */
A.scrollFixArray = {};

var ScrollFix = {
  fetchHeight: function(element_id) {
    var $el = $('#'+ element_id);
    $el.parent().css({height: $el.outerHeight()});
  },
  onScroll: function() {
    $.each(A.scrollFixArray, function(id, n) {
      var $win = $(window), $inner = $('#'+ id), fY = $inner.parent().offset().top,
        scroll_type = ($inner.attr('data-scroll')) ? $inner.attr('data-scroll') : 'top';

      if (scroll_type == 'top') {
        var wY = $win.scrollTop();
        if (wY > fY) $inner.addClass('fixed');
        else $inner.removeClass('fixed');
      }
      else if (scroll_type == 'bottom') {
        var wY = $win.height() + $win.scrollTop();
        if (wY < (fY + $inner.outerHeight())) $inner.addClass('fixed');
        else $inner.removeClass('fixed');
      }
    });
  },
  removeScroll: function(element_id) {
    clearInterval(A.scrollFixArray[element_id]);
    delete A.scrollFixArray[element_id];
  },
  removeAll: function() {
    $.each(A.scrollFixArray, function(id, n) {
      ScrollFix.removeScroll(id);
    });
  }
};

$.fn.scrollfix = function() {
  this.each(function() {
    var element_id = $(this).attr('id');

    if (A.scrollFixArray[element_id]) clearInterval(A.scrollFixArray[element_id]);
    A.scrollFixArray[element_id] = setInterval(function() {
      ScrollFix.fetchHeight(element_id);
    }, 300);
  });
}

$(window).bind('scroll', ScrollFix.onScroll);

try {stmgr.loaded('scrollfix.js');}catch(e){}