var LinkRoleOperation = {
    syncTimer: null,
    syncRole: null,

    initSync: function(role) {
       LinkRoleOperation.syncRole = role;

       clearInterval(LinkRoleOperation.syncTimer);
        LinkRoleOperation.syncTimer = setInterval(function() {
            ajax.post('/users/roles/syncRoleItems', {role: role, items: roleChilds[role]}, function(r) {
                if (r.msg) msi.show(r.msg);
            });
        }, 2000);

        $('#content').bind('contentChanged', LinkRoleOperation.destroySync);
    },
    destroySync: function() {
        clearInterval(LinkRoleOperation.syncTimer);
        $('#content').unbind('contentChanged', LinkRoleOperation.destroySync);
    },
    listRoles: function() {
        var html = [];
        html.push('<ul class="blocks">');

        $.each(rolesList, function(i, role) {
            html.push('<li><a onclick="LinkRoleOperation.edit(\''+ role +'\')>'+ role +'<span class="iconify">]</span></a></li>')
        });
        html.push('</ul>');

        $('#desktop').html(html.join(''));
    },
    isRoleItem: function(role, item) {
        var found = false;
        $.each(roleChilds[role], function(i, child) {
            if (child == item) found = true;
        });

        return found;
    },
    edit: function(role) {
        var olHtml = ['<ul class="blocks">'], rolHtml = ['<ul class="blocks">'];
        //LinkRoleOperation.initSync(role);

        $.each(operationsList, function(i, op) {
            olHtml.push('<li><a id="op-'+ op[0] +'" style="display: '+ (LinkRoleOperation.isRoleItem(role, op[0]) ? 'none' : 'block') +'" onclick="LinkRoleOperation.addChild(\''+ role +'\', \''+ op[0] +'\')">'+ op[0] +'<span class="iconify">&</span></a></li>');
        });
        olHtml.push('</ul>');

        $.each(roleChilds[role], function(i, child) {
            rolHtml.push('<li id="li-'+ child +'"><a onclick="LinkRoleOperation.removeChild(\''+ role +'\', \''+ child +'\')">'+ child +'<span class="iconify">*</span></a></li>')
        });
        rolHtml.push('</ul>');

        $('#desktop').html('<h2>Настройка роли '+ role +'</h2><div class="dualblocks"><div class="left"><h4>Все доступные операции</h4>'+ olHtml.join('') +'</div><div class="right"><h4>Уже добавленные</h4>'+ rolHtml.join('') +'</div></div>');
    },
    addChild: function(role, item) {
        if (LinkRoleOperation.isRoleItem(role, item)) return;
        roleChilds[role].push(item);
        $(document.getElementById('op-'+ item)).hide();
        $('<li id="li-'+ item +'"><a onclick="LinkRoleOperation.removeChild(\''+ role +'\', \''+ item +'\')">'+ item +'<span class="iconify">*</span></a></li>').appendTo('#content .dualblocks div.right ul');

        ajax.post('/users/roles/addItemChild', {role: role, item: item}, function(r) {
            if (r.msg) msi.show(r.msg);
        });
    },
    removeChild: function(role, item) {
        $(document.getElementById('op-'+ item)).show();
        $(document.getElementById('li-'+ item)).remove();

        $.each(roleChilds[role], function(i, child) {
            if (child == item) delete roleChilds[role][i];
        });

        ajax.post('/users/roles/removeItemChild', {role: role, item: item}, function(r) {
            if (r.msg) msi.show(r.msg);
        });
    }
};

try {stmgr.loaded('link-role-operation.js');}catch(e){}