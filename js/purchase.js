var Purchase = {
    create: function() {
        FormMgr.submit('#purchaseform', 'right');
        return false;
    },

    stateChanged: function(obj) {
      if (obj.value == 'Order Collection') $('#sc_button').show();
      else $('#sc_button').hide();
    },

    edit: function() {
        FormMgr.submit('#purchaseform', 'right', function(r) {
            msi.show('Изменения сохранены');
            //nav.go(r.url, null, null);
        });
        return false;
    },

    sendToModerator: function() {
        $('input[name="mod_request"]').val(1);
        FormMgr.submit('#purchaseform', 'right', function(r) {
            msi.show('Изменения сохранены. Заявка отправлена модераторам');
            $('input[name="mod_request"]').val(0);
            nav.reload();
        });
        return false;
    },

    restore: function(id) {
        ajax.post('/purchase'+ id +'/restore', null);
    },

    addgood: function(direction) {
      $('#direction').val(direction);
        FormMgr.submit('#addgoodform', 'right');
        return false;
    },

    editgood: function() {
        FormMgr.submit('#purchaseform', 'right');
        return false;
    },

    goEditGood: function(c, good, event) {
        A.glCancelClick = true;

        event.preventDefault();
        event.stopPropagation();

        var sp = good.split('_'),
            purchase_id = sp[0],
            good_id = sp[1];

        nav.go('/good'+ purchase_id +'_'+ good_id +'/edit', null);
        return false;
    },

    deletegood: function(c, good, event) {
        event.preventDefault();
        event.stopPropagation();

        var sp = good.split('_'),
            purchase_id = sp[0],
            good_id = sp[1];

        $(c).mouseleave();

        var $a = $('#good'+ good + ' a.good_row_relative'), $m;
        $a.addClass('good_row_deleted');
        $m = $('<div class="good_row_deleted_msg"></div>').appendTo($a);

        ajax.post('/good'+ purchase_id +'_'+ good_id +'/delete', null, function(r) {
            $m.html(r.html);
            $m.css({top: ($a.outerHeight() - $m.outerHeight()) / 2});
        });
    },

    restoregood: function(purchase_id, good_id) {
        A.glCancelClick = true;

        ajax.post('/good'+ purchase_id +'_'+ good_id +'/restore', null, function(r) {
            if (r.success) {
                var $a = $('#good'+ purchase_id +'_'+ good_id + ' a.good_row_relative');
                $a.removeClass('good_row_deleted');
                $a.find('div.good_row_deleted_msg').remove();
            }
            else ajex.show(r.html);
        });
    },

    overGood: function(c, event) {
        if (!$(c).hasClass('good_row_deleted')) $(c).find('div.good_row_controls').show();
    },

    outGood: function(c, event) {
        $(c).find('div.good_row_controls').hide();
    },

    overIcon: function(c) {
        $(c).css({opacity: 1});
    },
    outIcon: function(c) {
        $(c).css({opacity: 0.8});
    },

    uploadGoodImage: function(purchase_id, good_id, file_id) {
        cur['fileDoneCustom'+ file_id] = Purchase.onUploadDone.pbind(purchase_id, good_id, file_id);
        Upload.onStart(file_id);
    },
    onUploadDone: function(purchase_id, good_id, file_id, filedata) {
        var json = $.parseJSON(filedata);
        Upload.showInput(file_id);
        Upload.initFile(file_id, function() {
            Purchase.uploadGoodImage(purchase_id, good_id, file_id);
        });

        ajax.post('/goods/addimage', {purchase_id: purchase_id, good_id: good_id, image: filedata}, function(r) {
            if (r.success) {
                var cont = $('<div/>').attr({class: 'left good_image'}).appendTo('#images_list');
                cont.html('<img src="http://cs'+ json['b'][2] +'.spmix.ru/'+ json['b'][0] +'/'+ json['b'][1] +'" alt=""/><a class="tt iconify_x_a" title="Удалить изображение"></a>');

                msi.show('Изображение успешно добавлено в галерею');

                cont.children('a').click(function() {
                    Purchase.removeImage.call(this, purchase_id, good_id, r.id);
                });
            }
            else {
                var msg = [];
                $.each(r, function(i, v) {
                    msg.push(v);
                });
                report_window.create($('#images_list'), 'top', msg.join('<br/>'));
            }
        }, function(xhr) {
            ajex.show(xhr.responseText);
        });
    },
    removeImage: function(purchase_id, good_id, image_id) {
        var $this = $(this);
        ajax.post('/goods/removeimage', {purchase_id: purchase_id, good_id: good_id, image_id: image_id}, function(r) {
            $this.parent().remove();
        });
    },

    showImages: function(good_id) {
        ajax.post('/goods/getImages', {good_id: good_id}, function(list) {
            Photoview.list('good'+ good_id, list);
            Photoview.show('good'+ good_id);
        });
    },

    saveOic: function() {
        FormMgr.submit('#oicform', 'right', function(r) {
            msi.show(r.msg);
            nav.go(r.url, null, null);
        });
        return false;
    },

    order: function() {
        FormMgr.submit('#orderform', 'right', function(r) {
          boxPopup(r.msg);
            //msi.show(r.msg);
            //nav.go(r.url, null, null);
        });
        return false;
    },

    acquire: function(purchase_id) {
        if (A.acqPurchase) return;
        A.acqPurchase = true;

        ajax.post('/purchases/acquire', {id: purchase_id, confirm: 1}, function(r) {
            A.acqPurchase = false;

            var $p = $('#purchase'+ purchase_id),
                $h = $('<div/>').attr({class: 'purchase_hider'}).appendTo($p),
                $t = $('<div/>').attr({class: 'purchase_hider_tag'}).appendTo($p);
            $h.css({width: $p.outerWidth(), height: $p.outerHeight()});
            $t.html(r.html).css({
                top: ($p.outerHeight() - $t.outerHeight()) / 2,
                left: ($p.outerWidth() - $t.outerWidth()) / 2
            });
        }, function(xhr) {
            A.acqPurchase = false;
        });
    },
    cancelAcquire: function(purchase_id, request_id, hash) {
        if (A.acqPurchase) return;
        A.acqPurchase = true;

        ajax.post('/purchases/acquire', {id: purchase_id, request_id: request_id, hash: hash, confirm: 0}, function(r) {
            A.acqPurchase = false;

            var $p = $('#purchase'+ purchase_id);
            $p.find('div.purchase_hider').remove();
            $p.find('div.purchase_hider_tag').remove();
        }, function(xhr) {
            A.acqPurchase = false;
        });
    },

    sendWarning: function(purchase_id) {
        var $p = $('#purchase'+ purchase_id +'_warning'),
            $w = ($('#acquire_warning').size()) ? $('#acquire_warning') : $('<div/>').attr({id: 'acquire_warning'}).appendTo('body');
        $w.html('<textarea></textarea><div><a class="button" onclick="return Purchase.doSendWarning('+ purchase_id +')">Отправить</a></div>');
        $w.css({
            top: $p.offset().top - $w.outerHeight() - 10,
            left: $p.offset().left + ($p.outerWidth() - $w.outerWidth()) / 2
        });
        $w.click(function(event) {
            event.stopPropagation();
        });

        setTimeout(function() {
            $('body').one('click', function() {
                $w.remove();
            });
        }, 1);
    },
    doSendWarning: function(purchase_id) {
        var msg = $.trim($('#acquire_warning textarea').val());
        if (!msg) {
            $('#acquire_warning textarea').focus();
            return;
        }

        if (A.acqPurchase) return;
        A.acqPurchase = true;

        $('#acquire_warning').remove();

        ajax.post('/purchases/acquire', {id: purchase_id, confirm: -1, message: msg}, function(r) {
            A.acqPurchase = false;

            var $p = $('#purchase'+ purchase_id),
                $h = $('<div/>').attr({class: 'purchase_hider'}).appendTo($p),
                $t = $('<div/>').attr({class: 'purchase_hider_tag'}).appendTo($p);
            $h.css({width: $p.outerWidth(), height: $p.outerHeight()});
            $t.html(r.html).css({
                top: ($p.outerHeight() - $t.outerHeight()) / 2,
                left: ($p.outerWidth() - $t.outerWidth()) / 2
            });
        }, function(xhr) {
            A.acqPurchase = false;
        });
    },

  shareToFriends: function(id) {
    showGlobalPrg();

    ajax.post('/purchases/purchases/sharetofriends?id='+ id, {}, function(r) {
      hideGlobalPrg();

      var box = new Box({
        hideButtons: true,
        bodyStyle: 'padding: 0px',
        width: 500
      });
      box.content(r.html);
      box.show();
    });
  },

  doShareToFriends: function(id) {
    $('div.box_progress').show();

    ajax.post('/purchases/purchases/sharetofriends?id='+ id, {msg: $('#share_message').val()}, function(r) {
      $('div.box_progress').hide();

      if (r.success) {
        curBox().hide();
        boxPopup('Сообщение успешно отправлено');
      }
      else boxPopup('Произошла ошибка. Повторите запрос позже');
    }, function(r) {
      $('div.box_progress').hide();
    });
  },

  subscribe: function(id) {
    ajax.post('/purchases/purchases/subscribe?id='+ id, {}, function(r) {
      $('#subscribe'+ id).html('<span class="icon-check"></span> '+ ((r.step == 0) ? 'Отписаться от новостей' : 'Подписаться на новости'));
    }, function(r) {

    });
  },

  shareGoodToFriends: function(id) {
    showGlobalPrg();

    ajax.post('/purchases/goods/sharetofriends?id='+ id, {}, function(r) {
      hideGlobalPrg();

      var box = new Box({
        hideButtons: true,
        bodyStyle: 'padding: 0px',
        width: 500
      });
      box.content(r.html);
      box.show();
    });
  },

  doShareGoodToFriends: function(id) {
    $('div.box_progress').show();

    ajax.post('/purchases/goods/sharetofriends?id='+ id, {msg: $('#share_message').val()}, function(r) {
      $('div.box_progress').hide();

      if (r.success) {
        curBox().hide();
        boxPopup('Сообщение успешно отправлено');
      }
      else boxPopup('Произошла ошибка. Повторите запрос позже');
    }, function(r) {
      $('div.box_progress').hide();
    });
  },

  subscribeGood: function(id) {
    ajax.post('/purchases/goods/subscribe?id='+ id, {}, function(r) {
      $('#subscribe'+ id).html('<span class="icon-check"></span> '+ ((r.step == 0) ? 'Отписаться от новостей' : 'Подписаться на новости'));
    }, function(r) {

    });
  },

  showGoodsFrom: function(purchase_id) {
    nav.go('/purchase'+ purchase_id +'/addfromanother?from_id='+ $('#from_id').val());
  },

  addPurchaseGood: function(purchase_id, good_id) {
    var $form = $('#good'+ good_id + '_form');
    $('#good'+ good_id +'_progress').show();

    ajax.post('/purchase'+ purchase_id +'/addfromanother?good_id='+ good_id, $form.serialize(), function(r) {
      $('#good'+ good_id +'_progress').hide();

      $('#good'+ good_id +' .photo img').hide();
      $('#good'+ good_id +' .info form').hide();

      $('<div class="dld">Товар добавлен в закупку</div>').insertAfter('#good' + good_id + ' .info form');
    }, function() {
      $('#good'+ good_id +'_progress').hide();
    })

    return false;
  },

  hidePurchaseGood: function(good_id) {
    $('#good'+ good_id +' .photo img').hide();
    $('#good'+ good_id +' .info form').hide();

    $('<div class="dld">Товар скрыт. <a onclick="Purchase.showPurchaseGood('+ good_id +')">Открыть</a>.</div>').insertAfter('#good' + good_id + ' .info form');
  },
  showPurchaseGood: function(good_id) {
    $('#good'+ good_id +' .photo img').show();
    $('#good'+ good_id +' .info form').show().next().remove();
  },

  setAACopy: function(type_id) {
    switch (type_id) {
      case 0:
        $('#aa_copy_menu').text('все товары');
        $('#aa_copy_menu').next().find('div.header div').text('все товары');
        A.aaCopyType = 0;
        break;
      case 1:
        $('#aa_copy_menu').text('только видимые');
        $('#aa_copy_menu').next().find('div.header div').text('только видимые');
        A.aaCopyType = 1;
        break;
    }
  },
  copyAA: function(target_id, from_id) {
    $('#purchase_aa_copy_progress').show();
    $('#purchase_aa_copy_btn').hide();

    ajax.post('/purchase'+ target_id +'/copyaa?from_id='+ from_id, {type: (A.aaCopyType) ? 1 : 0}, function(r) {
      $('#purchase_aa_copy_progress').hide();
      boxPopup(r.msg);
    }, function() {
      $('#purchase_aa_copy_progress').hide();
      $('#purchase_aa_copy_btn').show();
    });
  }
};

try {stmgr.loaded('purchase.js');}catch(e){}