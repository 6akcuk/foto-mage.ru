
$().ready(function() {
    $('#cur_city').change(function() {
        ajax.post('/setcity', {city_id: $(this).val()}, function(r) {
            if (r.success) nav.go(location.href, null);
        });
    });
});

try {stmgr.loaded('main.js');}catch(e){}