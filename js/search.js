var Search = {
  search: function(obj) {
    $.extend(true, nav.objLoc, obj);

    $('div.summary_wrap .pg_pages').hide();
    $('div.summary_wrap .progress').show();

    nav.objLoc.offset = 0;
    nav.go("/"+ nav.toStr(nav.objLoc), null);
  },

  init: function(opts) {
    placeholderSetup('#c_name', {back: true});

    var city_dropdown = new Dropdown('c[city_id]', {
      width: 160,
      label: 'Выберите город',
      items: opts.cities,
      change: function(val) {
        Search.search({c: {city_id: val}});
      }
    });

    var role_dropdown = new Dropdown('c[role]', {
      width: 160,
      label: 'Выберите роль',
      items: opts.roles,
      change: function(val) {
        Search.search({c: {role: val}});
      }
    });

    $('#c_name').bind('keyup', function() {
      var self = $(this);
      clearTimeout(cur.tm_cname);
      cur.tm_cname = setTimeout(function() {
        Search.search({c: {name: self.val()}});
      }, 500);
    });
  }
};

try{stManager.done('search.js');}catch(e){}