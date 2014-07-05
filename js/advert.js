var Advert = {
  showMsg: function(msg) {
    $('#advert_post_error').html('').hide();
    $('#advert_post_result').html('<div class="msg" id="advert_post_msg">'+ msg +'</div>').show();
    $('#advert_post_msg').animate({backgroundColor: '#F9F6E7'});
  },
  add: function() {
    var b = showBox('/advert/default/add', {}, {params: {}}).setButtons(getLang('global_add'), function() {
      var category_id = $('#category_id').val(), city_id = $('#city_id').val(), title = $.trim($('#title').val()),
        fullstory = $.trim($('#fullstory').val()), price = $.trim($('#price').val()), fixed = $('#fixed').val(),
        params = {}, attaches = {};

      if (!category_id || category_id == "0") {
        highlight($('#category_id').parent(), false, true);
        highlight($('#category_id').prev(), false, true);
        return false;
      }

      if (!city_id || city_id == "0") {
        highlight($('#city_id').parent(), false, true);
        highlight($('#city_id').prev(), false, true);
        return false;
      }

      if (!title && cur.postCategories[category_id].no_title == 0) {
        highlight('#title');
        return false;
      }

      if (cur.postParams) {
        var cancel = false;

        $.each(cur.postParams, function(i, param) {
          if (!document.getElementById('param_'+ param.id)) return;

          params[param.id] = $.trim($('#param_'+ param.id).val());
          if (!params[param.id] || params[param.id] == "0") {
            cancel = true;

            if (param.type == 'select') {
              highlight($('#param_'+ param.id).parent(), false, true);
              highlight($('#param_'+ param.id).prev(), false, true);
            } else if (param.type == 'input') {
              highlight('#param_'+ param.id);
            }
          }
        });

        if (cancel) return false;
      }

      if (!fullstory) {
        highlight('#fullstory');
        return false;
      }

      if (!price && cur.postCategories[category_id].no_price == 0) {
        highlight('#price');
        return false;
      }

      if (document.getElementById('ap_form')) {
        if ($('#ap_form').find('div.media_photo_preview').children().length) {
          attaches.photo = [];
          $('#ap_form').find('div.media_photo_preview').find('input[type="hidden"]').each(function(i, el) {
            attaches.photo.push($(this).val());
          });
        }
        if ($('#ap_form').find('div.media_document_preview').children().length) {
          attaches.document = [];
          $('#ap_form').find('div.media_document_preview').find('input[type="hidden"]').each(function(i, el) {
            attaches.document.push($(this).val());
          });
        }
      }

      var postdata = {
        category_id: category_id,
        city_id: city_id,
        title: title,
        params: params,
        fullstory: fullstory,
        price: price,
        attaches: attaches,
        fixed: fixed
      };

      ajax.post('/advert/default/add', postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Advert.showMsg(r.message);
            $('#title, #fullstory, #price').val('');
            $('#advert_post_title, #advert_post_title_inp').show();

            $('#advert_params_label, #advert_params').hide();
            $('#advert_params').html('');

            cur.uiPostCategory.reset();
            cur.uiPostCity.reset();

            Upload.initAttaches('advert_post_attach_wrap', {prefix: 'ap', allow: ['photo', 'document']});
          }
          else {
            $('#advert_post_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  edit: function(id) {
    var b = showBox('/advert/default/edit/id/'+ id, {}, {params: {}}).setButtons(getLang('global_save'), function() {
      var category_id = $('#category_id').val(), city_id = $('#city_id').val(), title = $.trim($('#title').val()),
        fullstory = $.trim($('#fullstory').val()), price = $.trim($('#price').val()), fixed = $('#fixed').val(),
        params = {}, attaches = {};

      if (!category_id || category_id == "0") {
        highlight($('#category_id').parent(), false, true);
        highlight($('#category_id').prev(), false, true);
        return false;
      }

      if (!city_id || city_id == "0") {
        highlight($('#city_id').parent(), false, true);
        highlight($('#city_id').prev(), false, true);
        return false;
      }

      if (!title && cur.postCategories[category_id].no_title == 0) {
        highlight('#title');
        return false;
      }

      if (cur.postParams) {
        var cancel = false;

        $.each(cur.postParams, function(i, param) {
          if (!document.getElementById('param_'+ param.id)) return;

          params[param.id] = $.trim($('#param_'+ param.id).val());
          if (!params[param.id] || params[param.id] == "0") {
            cancel = true;

            if (param.type == 'select') {
              highlight($('#param_'+ param.id).parent(), false, true);
              highlight($('#param_'+ param.id).prev(), false, true);
            } else if (param.type == 'input') {
              highlight('#param_'+ param.id);
            }
          }
        });

        if (cancel) return false;
      }

      if (!fullstory) {
        highlight('#fullstory');
        return false;
      }

      if (!price && cur.postCategories[category_id].no_price == 0) {
        highlight('#price');
        return false;
      }

      if (document.getElementById('ap_form')) {
        if ($('#ap_form').find('div.media_photo_preview').children().length) {
          attaches.photo = [];
          $('#ap_form').find('div.media_photo_preview').find('input[type="hidden"]').each(function(i, el) {
            attaches.photo.push($(this).val());
          });
        }
        if ($('#ap_form').find('div.media_document_preview').children().length) {
          attaches.document = [];
          $('#ap_form').find('div.media_document_preview').find('input[type="hidden"]').each(function(i, el) {
            attaches.document.push($(this).val());
          });
        }
      }

      var postdata = {
        category_id: category_id,
        city_id: city_id,
        title: title,
        params: params,
        fullstory: fullstory,
        price: price,
        attaches: attaches,
        fixed: fixed
      };

      ajax.post('/advert/default/edit/id/'+ id, postdata, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          if (r.success) {
            Advert.showMsg(r.message);
          }
          else {
            $('#advert_post_error').html(r.message).show();
          }
        }
      });
    }, getLang('global_cancel'));
  },
  delete: function(id) {
    var b = showFastBox('Удаление объявления', 'Вы действительно хотите удалить объявление?', getLang('global_delete'), function() {
      ajax.post('/advert/default/delete/id/'+ id, {}, {
        showProgress: b.showProgress,
        hideProgress: b.hideProgress,
        onDone: function(r) {
          b.hide();
          boxPopup(r.message);
          nav.reload();
        }
      });
    }, getLang('global_cancel'));
  },
  init: function() {
    placeholderSetup('#c_title', {back: true});
  },
  initForm: function(opts) {
    cur.postCategories = opts.categoriesJson;
    Upload.initAttaches('advert_post_attach_wrap', {prefix: 'ap', allow: ['photo', 'document'], attaches: opts.attaches || null});

    cur.uiPostCategory = new Dropdown('category_id', {
      width: 378,
      label: 'Выберите категорию',
      items: opts.categories,
      change: function(val) {
        if (cur.postCategories[val].no_title == 1) $('#advert_post_title, #advert_post_title_inp').hide();
        else $('#advert_post_title, #advert_post_title_inp').show();

        if (cur.postCategories[val].no_price == 1) $('#advert_post_price, #advert_post_price_inp').hide();
        else $('#advert_post_price, #advert_post_price_inp').show();

        $('#advert_params_label, #advert_params').hide();
        $('#advert_params').html('');

        ajax.post('/advert/default/getParams/id/'+ val, {}, {
          showProgress: curBox().showProgress,
          hideProgress: curBox().hideProgress,
          onDone: function(r) {
            cur.postParams = r.params;

            $('#advert_params_label, #advert_params').show();
            $.each(r.params, function(i, param) {
              if (!param.dependence) {
                if (param.type == 'select') {
                  $('<input/>').attr({type: 'hidden', id: 'param_'+ param.id, name: 'param['+ param.id +']'}).appendTo('#advert_params');
                  if (!cur.uiParams) cur.uiParams = {};
                  cur.uiParams[param.id] = new Dropdown('param_'+ param.id, {
                    width: 378,
                    label: param.label,
                    items: param.items,
                    change: function(val) {
                      if (!cur.uiParamDep) cur.uiParamDep = {};
                      if (cur.uiParamDep[param.id]) cur.uiParamDep[param.id].destroy();

                      $.each(r.params, function(d, depo) {
                        if (depo.dependence == val) {
                          $('<input/>').attr({type: 'hidden', id: 'param_'+ depo.id, name: 'param['+ depo.id +']'}).appendTo('#advert_params');
                          cur.uiParamDep[param.id] = new Dropdown('param_'+ depo.id, {
                            width: 378,
                            label: depo.label,
                            items: depo.items
                          });
                        }
                      });
                    }
                  });
                } else if (param.type == 'input') {
                  $('<div/>').addClass('advert_post_header').html(param.label).appendTo('#advert_params');
                  $('<div/>').addClass('advert_post_param').html('<input type="text" class="text advert_param_input" id="param_'+ param.id +'" name="param['+ param.id +']" value="" />').appendTo('#advert_params');
                }
              }
            });
          }
        });
      }
    });

    cur.uiPostCity = new Dropdown('city_id', {
      width: 378,
      label: 'Выберите город',
      items: opts.cities
    });

    autosizeSetup('#fullstory', {
      minHeight: 40,
      maxHeight: 120
    });

    // If edit
    var cid = $('#category_id').val()
    if (opts.params) {
      if (cur.postCategories[cid].no_title == 1) $('#advert_post_title, #advert_post_title_inp').hide();
      else $('#advert_post_title, #advert_post_title_inp').show();

      var params = $('#advert_params').find('input[type="hidden"]');
      cur.postParams = opts.params;

      $.each(params, function(i, p) {
        $.each(cur.postParams, function(j, param) {
          var id = parseInt($(p).attr('id').split('_').pop());
          if (param.id == id) Advert._renderParam(param);
        });
      });
/*
      $.each(cur.postParams, function(i, param) {
        if (!param.dependence) {
          if (param.type == 'select') {
            if (!cur.uiParams) cur.uiParams = {};
            Advert._renderSelectParam(param);
          }
        } else if (param.dependence) {
          $.each(params, function(i, p) {
            if (parseInt($(p).val()) == param.dependence) {
              if (!cur.uiParamDep) cur.uiParamDep = {};
              Advert._renderSelectParam(param, true);
            }
          });
        }
      });*/
    }
  },
  _renderParam: function(param) {
    if (!cur.uiParams) cur.uiParams = {};
    if (!cur.uiParamDep) cur.uiParamDep = {};
    if (param.type == 'select') Advert._renderSelectParam(param);
    else if (param.type == 'input') Advert._renderInputParam(param);
  },
  _renderInputParam: function(param) {

  },
  _renderSelectParam: function(param) {
    var o = new Dropdown('param_'+ param.id, {
      width: 378,
      label: param.label,
      items: param.items,
      change: function(val) {
        if (cur.uiParamDep[param.id]) cur.uiParamDep[param.id].destroy();

        $.each(cur.postParams, function(d, depo) {
          if (depo.dependence == val) {
            if (!document.getElementById('param_'+ depo.id)) $('<input/>').attr({type: 'hidden', id: 'param_'+ depo.id, name: 'param['+ depo.id +']'}).appendTo('#advert_params');
            cur.uiParamDep[param.id] = new Dropdown('param_'+ depo.id, {
              width: 378,
              label: depo.label,
              items: depo.items
            });
          }
        });
      }
    });

    if (param.dependence) {
      var param_id = null;

      $.each(cur.postParams, function(i, p) {
        if (param_id) return;

        $.each(p.items, function(i,item) {
          if (param_id) return;
          if (parseInt(item[0]) == param.dependence) {
            param_id = p.id;
          }
        });
      });

      cur.uiParamDep[param_id] = o;
    } else cur.uiParams[param.id] = o;
  }
}

try{stManager.done('advert.js');}catch(e){}