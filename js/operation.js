var operation = {
    create: function() {
        var $form = $('#addoperation-form'),
            url = $form.attr('action');

        $form.find('input').removeClass('error');

        ajax.post(url, $form.serialize(), function(response) {
            if (response.success) {
                var scs = $('<div/>').addClass('success').insertAfter($form.children('h2'));
                scs.html('Операция '+ $form.find('#OperationForm_name').val() +' добавлена успешно');
            }
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

                report_window.create($form, 'right', string.join('<br/>'));
            }
        });

        return false;
    }
}

try {stmgr.loaded('operation.js');}catch(e){}