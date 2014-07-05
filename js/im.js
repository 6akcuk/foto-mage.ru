var Im = {
    selectDialog: function(dialog_id, event) {
        nav.go('/im?sel='+ dialog_id, event);
    },

    showSendButton: function() {
        $('#im_send').html('Отправить');
    },

    sendMessage: function(is_box) {
        var $im = $('#imform'),
            recipients = $im.find('input[name^="im_wdd"]'),
            rec = [],
            title = $.trim($im.find('[name="Im[title]"]').val()),
            message = $.trim($im.find('[name="Im[message]"]').val());

        $im.find('.input_error').remove();

        if (recipients.length == 0) {
            WideDropdown.show('im_wdd', event);
            return false;
        }
        else if (recipients.length > 1 && !title) {
          inputError($im.find('[name="Im[title]"]'), 'Требуется название для беседы');
          return false;
        }
        else if (recipients.length > 0 && !message) {
          inputError($im.find('[name="Im[message]"]'), 'Напишите хоть что-нибудь');
          return false;
        }

        if (A.imSending) return;
        A.imSending = true;

        $('#im_send').html('<img src="/images/progress_small.gif" />');
        for(var i=0; i < recipients.length; i++) {
            rec.push(parseInt(recipients[i].value));
        }

        ajax.post('/im?sel=-1', {recipients: rec, title: title, message: message}, function(r) {
            A.imSending = false;
            if (r.success) {
              if (!is_box) nav.go(r.url, null);
              else {
                curBox().hide();
                boxPopup(r.msg);
              }
            }
            else report_window.create($im, 'left', r.message);

            Im.showSendButton();
        }, function(xhr) {
            A.imSending = false;
            Im.showSendButton();
        });
    },

    setLogState: function(state, message_id) {
        var $ma = $('#ma'+ message_id),
            $ms = $('#mess'+ message_id);
        $ma.css('visibility', (state == 1) ? 'visible' : 'hidden');
        if (state == 1 && $ms.hasClass('im_in') && $ms.hasClass('im_new_msg')) {
            setTimeout(function() {
                Im.setMessViewed(message_id);
            }, 1);
        }
    },

    setMessViewed: function(message_id) {
        ajax.post('/im?action=viewed&id='+ message_id, null, function(r) {
            if (r.success) {
                $('#mess'+ message_id).removeClass('im_new_msg');
                updMessCounter(parseInt(r.counters['pm']));
            }
        }, function(r) {

        });
    },

    send: function(dialog_id) {
        var $text = $('#im_text'),
            txt = $.trim($text.val());

        if (!txt) {
            $text.focus();
            return false;
        }

        if (A.imSending) return;
        A.imSending = true;

        $('#im_progress').show();
        ajax.post('/im?action=send&id='+ dialog_id, {msg: txt}, function(r) {
            A.imSending = false;
            $('#im_progress').hide();
            $text.val('').keypress();

            if (r.success) Im.peer(dialog_id);
            else {
                ajex.show(r.message);
            }
        }, function(r) {
            A.imSending = false;
            $('#im_progress').hide();
        });
    },

    // такой способ никогда нельзя использовать :)
    peer: function(dialog_id) {
        clearTimeout(A.imPeer);
        A.imPeer = setTimeout(function() {
            var $lastMsg = $('#im_log'+ dialog_id + ' tr:last-child'),
                timestamp = $lastMsg.attr('date');

            if (A.imPeerRequest) A.imPeerRequest.abort();
            A.imPeerRequest = ajax.post('/im?action=peer&id='+ dialog_id + '&timestamp='+ timestamp, {}, function(r) {
                updMessCounter(parseInt(r.counters['pm']));
                $(r.html).appendTo('#im_log'+ dialog_id);
                if (r.html) Im.resizeRows();
            }, function(r) {

            });

            Im.peer(dialog_id);
        }, 5000);
    },
    stopPeer: function() {
        clearTimeout(A.imPeer);
    },

    setup: function(dialog_id) {
        var $head = $('#header');
        A.imChecked = {};

        $('body').addClass('im_fixed');
        $('#sidebar').css({top: $head.outerHeight()});
        $('#im_nav').css({top: $head.outerHeight()});
        $('#footer').hide();
        //$('#page_layout').css({marginTop: $head.outerHeight()});

        Im.peer(dialog_id);
        Im.pointControls();
        Im.resizeRows();
    },

    pointControls: function() {
        var $head = $('#header'),
            $sb = $('#sidebar'),
            $fill = $('#im_footer_filler'),
            lowY = $sb.offset().top - $(window).scrollTop() + $sb.outerHeight() + $('#im_peer_controls').outerHeight(),
            height, minY;

        height = ($(window).height < lowY) ? 0 : $(window).height() - lowY;
        minY = $('#header').outerHeight() + $('#im_nav').outerHeight() + 50;

        $fill.css({height: height});

        $('#im_content').css({padding:
            ($head.outerHeight() + $('#im_nav').outerHeight()) +'px 0px '+
                ($('#im_peer_controls_wrap').outerHeight()) + 'px'
        });

        if ($fill.offset().top <= 0) {

        }
    },

    resizeRows: function() {
        var tHeight = $('#im_rows div.im_peer_rows').outerHeight();
        $('#im_rows').css({height: tHeight});
        $(window).scrollTop($('body').height());
    }
};

try {stmgr.loaded('im.js');}catch(e){}