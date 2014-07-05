var user = {
    login: function() {
        FormMgr.submit($('#login-form'), 'bottom', function(r) {
            location.href = '/id'+ r.id;
        });

        return false;
    }
};

try {stmgr.loaded('user.js');}catch(e){}