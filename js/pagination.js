var Paginator = {
    opts: {},
    landmarks: [],

    onWindowResize: function() {
        var $pg = $('div.pagination'),
            ofs = $pg.offset();

        if (A.pgFixed && ofs)
            A.pgFixed.css({
                left: ofs.left
            });
        Paginator.initPages();
    },

    onNavGo: function() {
      A.pgTarget = null;
      A.pgUrl = null;
      A.pgForceUrl = false;
      A.pgDelta = 0;
      A.offset = 0;
      A.pgPage = 1;
      A.pgPages = 0;
      A.pgNoPages = false;
      A.pgFixedNoMore = false;
      if (A.pgFixedContent) A.pgFixedContent.html('');
    },

    init: function(opts) {
        var $pg = $('div.pagination'),
            ofs = $pg.offset();
        Paginator.opts = $.extend(Paginator.opts, opts || {});

        if (!ofs) return;

        A.pgTarget = opts.target;
        A.pgUrl = opts.url;
        A.pgForceUrl = opts.forceUrl;
        A.pgDelta = opts.delta;
        A.offset = opts.offset;
        A.pgPage = (A.offset + A.pgDelta) / A.pgDelta;
        A.pgPages = opts.pages;
        A.pgNoPages = opts.nopages;
        A.pgFixedNoMore = false;

        if (!A.pgInit) {
            A.pgFixed = $('<div/>').addClass('pg_fixed')
                .attr('id', 'pg_fixed')
                .css({
                    display: 'none',
                    left: ofs.left
                });
            A.pgFixed.html('<div class="pg_fixed_bg"></div><div class="pg_fixed_content"></div>');
            A.pgFixedBg = A.pgFixed.find('div.pg_fixed_bg');
            A.pgFixedContent = A.pgFixed.find('div.pg_fixed_content');

            A.pgFixed.appendTo(document.body);

            A.pgFixedTop = ofs.top;
            A.pgBusy = false;
            A.pgInit = true;

            Paginator.attach();
        }

        Paginator.scanLandmarks();
        Paginator.initPages();
    },

    initPages: function() {
        if (typeof A.pgFixedContent == 'undefined' || A.pgNoPages) return;

        var page = parseInt(A.pgPage);// (A.offset + A.pgDelta) / A.pgDelta,
            html = [],
            minPage = 1, maxPage = 1;

        if (page > 3 && Paginator.opts.pages > 5) minPage = page - 2;
        maxPage = (page < Paginator.opts.pages - 2) ? page + 2 : Paginator.opts.pages;

        if (page > 3 && Paginator.opts.pages > 5) html.push('<a class="pg_fixed_arr" href="'+ Paginator.opts.url +'?offset=0" onclick="return nav.go(this, event, {paginator: true})">&laquo;</a>');
        for(var i=minPage; i<=maxPage; i++) {
            var offset = i * A.pgDelta - A.pgDelta,
                cls = (i == A.pgPage) ? 'pg_fixed_lnk_sel' : 'pg_fixed_lnk';
            if (cls == 'pg_fixed_lnk' && Paginator.landmarks[i] && Paginator.landmarks[i].top) cls = 'pg_fixed_lnk_fill';
            html.push('<a class="'+ cls +'" href="'+ Paginator.opts.url +'?offset='+ offset +'" onclick="return nav.go(this, event, {paginator: true})">'+ i +'</a>');
        }
        if (Paginator.opts.pages > maxPage) {
            var offset = maxPage * A.pgDelta - A.pgDelta;
            html.push('<a class="pg_fixed_arr" href="'+ Paginator.opts.url +'?offset='+ offset +'" onclick="return nav.go(this, event, {paginator: true})">&raquo;</a>');
        }

        A.pgFixedContent.html(html.join(''));
        A.pgFixedBg.css({width: A.pgFixedContent.outerWidth(), height: A.pgFixedContent.outerHeight()});
    },

    scanLandmarks: function() {
        Paginator.landmarks = [];

        $('[rel*="page"]').each(function(idx, val) {
            var pg = parseInt($(this).attr('rel').split('-').pop());
            Paginator.landmarks[pg] = {top: $(this).offset().top, target: $(this)};
        });
    },

    attach: function() {
        $(window).on('scroll', Paginator.scrollResize);
    },

    scrollResize: function(evt) {
        var $win = $(window), scr = $win.scrollTop(), st = scr + $win.height(),
            $pgMore = $('a.pg_more'), mrt = ($pgMore.offset()) ? $pgMore.offset().top : 0;

      if (A.pgPages <= 1) return;

        if (scr > A.pgFixedTop && !A.pgFixedVisible && !A.pgNoPages) {
            A.pgFixed.stop(true, true).fadeIn();
            A.pgFixedVisible = true;
            Paginator.initPages();
        }
        else if(scr <= A.pgFixedTop && A.pgFixedVisible) {
            A.pgFixed.stop(true, true).fadeOut();
            A.pgFixedVisible = false;
        }

        if (!A.pgFixedNoMore && st >= mrt - 150) Paginator.preload();

        for(var pg in Paginator.landmarks) {
            var lm = Paginator.landmarks[pg];
            if (st >= lm.top && (!Paginator.landmarks[pg+1] || st < Paginator.landmarks[pg+1].top)) {
                if (pg != A.pgPage) {
                    A.pgPage = pg;
                    Paginator.initPages();
                }
            }
        }
    },

    preload: function() {
        var curPage = (A.offset + A.pgDelta) / A.pgDelta,
            nextOffset = A.offset + A.pgDelta,
            nextPage = (nextOffset + A.pgDelta) / A.pgDelta;

        if (A.pgFixedNoMore) return;
        if (Paginator.landmarks[nextPage]) return;
        if (A.pgBusy) return;
        A.pgBusy = true;

        if (curPage == A.pgPages || nextPage == A.pgPages) {
            A.pgFixedNoMore = true;
            $('a.pg_more').hide();

            if (curPage == A.pgPages) {
                A.pgBusy = false;
                return;
            }
        }

        var outgo = (A.pgForceUrl) ? A.pgUrl : nav.curLoc,
          loc = nav.query(outgo + ((outgo.match(/\?/)) ? '&' : '?') + 'offset='+ nextOffset +'&pages=1', {}), // (nav.curLoc.match(/\?/)) ? nav.curLoc.split('?') : [nav.curLoc, ''],
          where = loc.split('?'),
          obj = nav.q2obj(where[1]);

        loc[1] = nav.obj2q(obj);
        ajax.post(loc, obj, function(r) {
            Paginator.onDone(r, nextOffset);
        });
        //nav.go(A.pgUrl +'?offset='+ nextOffset, null, {paginator: true});
    },

    onDone: function(r, offset) {
        var page = (A.offset + A.pgDelta) / A.pgDelta;
        A.pgBusy = false;
        A.offset = offset;

        $(r.html).appendTo(A.pgTarget);
        Paginator.scanLandmarks();
        Paginator.initPages();
    },

    showMore: function() {
        Paginator.preload();
    },

    nav: function(offset) {
        var page = (parseInt(offset) + A.pgDelta) / A.pgDelta;

        if (!Paginator.landmarks[page]) return true;
        else {
            $(window).scrollTop($('[rel="page-'+ page +'"]').offset().top - 80);
            A.pgPage = page;
            Paginator.initPages();
            return false;
        }
    }
};

try{stManager.done('pagination.js');}catch(e){}