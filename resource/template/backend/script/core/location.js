(function ($) {
    'use strict';

    function formatCustom (state) {
        var text = '';

        var attr_all = $(state.element).attr('data-all');

        if (typeof attr_all !== typeof undefined && attr_all !== false) {
            attr_all = '<span class="all">'+attr_all+'</span>'
            text = text.concat(attr_all);
        }

        var attr_group = $(state.element).attr('data-group');
        if (typeof attr_group !== typeof undefined && attr_group !== false) {
            attr_group = '<span class="group">'+attr_group+'</span>'
            text = text.concat(attr_group);
        }
        var attr_country = $(state.element).attr('data-country');
        if (typeof attr_country !== typeof undefined && attr_country !== false) {
            attr_country = '<span class="country">'+attr_country+'</span>'
            text = text.concat(attr_country);
        }

        var attr_province = $(state.element).attr('data-province');
        if (typeof attr_province !== typeof undefined && attr_province !== false) {
            attr_province = ' - <span class="province">'+attr_province+'</span>'
            text = text.concat(attr_province);
        }

        text = $('<span />').addClass($(state.element).attr('data-filter')).html(text);

        return text;
    };

    function get_options_select_location(select_location, data_location_value = '', without_provinces = false) {
        var list_location_groups = JSON.parse($('#list_location_groups').text());
        var list_countries = JSON.parse($('#list_countries').text());
        var list_provinces = without_provinces === false ? JSON.parse($('#list_provinces').text()) : [];

        var data_selected = [];
        if (data_location_value && data_location_value != '') {
            data_location_value = data_location_value.split('_');

            $.each(data_location_value, function (k, v) {
                    data_selected.push(v);
            });
        }

        if (data_selected.includes('*')) {
            $('<option />').attr({'value': '*', 'selected': 'selected',  'data-filter' : 'box-group', 'data-all': 'Select All Location'}).text('Select All Location').appendTo(select_location);
        } else {
            $('<option />').attr({'value': '*', 'data-filter' : 'box-group', 'data-all': 'Select All Location'}).text('Select All Location').appendTo(select_location);
        }

        $.each(list_location_groups, function (k, v) {
            if (data_selected.includes('g'+v.id)) {
                $('<option />').attr({'value': 'g'+v.id, 'selected': 'selected',  'data-filter' : 'box-group', 'data-group': v.group}).text(v.group).appendTo(select_location);
            } else {
                $('<option />').attr({'value' : 'g'+v.id, 'data-filter' : 'box-group', 'data-group': v.group}).text(v.group).appendTo(select_location);
            }
        });

        $.each(list_countries, function (k, v) {
            if (data_selected.includes('c'+v.id)) {
                $('<option />').attr({'value': 'c'+v.id, 'selected': 'selected', 'data-filter': 'box-country', 'data-country' : v.country}).text(v.country).appendTo(select_location);
            } else {
                $('<option />').attr({'value': 'c'+v.id, 'data-filter' : 'box-country', 'data-country' : v.country}).text(v.country).appendTo(select_location);
            }
        });

        $.each(list_provinces, function (k, v) {
            if (data_selected.includes('p'+v.id)) {
                $('<option />').attr({'value': 'p'+v.id, 'selected': 'selected', 'data-filter' : 'box-province', 'data-country' : v.country, 'data-province' : v.province}).text(v.country + ' ' + v.province).appendTo(select_location);
            } else {
                $('<option />').attr({'value': 'p'+v.id , 'data-filter' : 'box-province', 'data-country' : v.country, 'data-province' : v.province}).text(v.country + ' ' + v.province).appendTo(select_location);
            }
        });
    }

    window.boxNewGroupLocation = function () {
        $(this).click( function () {
            var key = $(this).attr('data-key');
            var value_group_name = $(this).attr('data-value-group-name');
            if (typeof value_group_name == typeof undefined || value_group_name == false) {
                value_group_name = null;
            }
            newGroupLocation(key, 0 , value_group_name, null, null, false );
        });
    }

    function newGroupLocation(key = null, value_id = 0, value_group_name = null, value_include = null, value_exclude = null, redirect = false) {
        if (key == null){
            key = $.makeUniqid();
        }

        $.unwrapContent('addNewGroupLocationFrm');

        var modal = $('<div />').addClass('osc-modal').width(650);

        $.wrapContent(modal, {key: 'addNewGroupLocationFrm',backdrop:'static'});

        var header = $('<header />').appendTo(modal);

        let title = 'Create Location group'
        if (value_id) {
            title = 'Update Location group'
        }
        $('<div />').addClass('title').attr({style:'text-align: center'}).html(title).appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('addNewGroupLocationFrm');
        }).appendTo(header);

        var modal_body = $('<div />').addClass('body post-frm post-frm-section-location').appendTo(modal);
        var modal_body_section = $('<div />').addClass('body post-frm-colleft').appendTo(modal_body);
        var modal_body_section_preview = $('<div />').addClass('body post-frm-colright').appendTo(modal_body);


        $('<div />').text('Group name').addClass('mt10').appendTo(modal_body_section);

        var row = $('<div />').addClass('mt10').appendTo(modal_body_section);

        var group_name = $('<input />').addClass('styled-input').attr({type: 'text', name: 'group_name', value: value_group_name}).appendTo($('<div />').appendTo(row));

        $('<div />').text('Include locations').addClass('mt10').appendTo(modal_body_section);

        //Col-right

        var row_location = $('<div  style="position: relative"  />').addClass('mt10').appendTo(modal_body_section);

        var select_include_location = $('<select />').attr('name', 'include_'+key+'[]').addClass('select2 location_group').attr('multiple','multiple').appendTo(row_location);


        get_options_select_location(select_include_location, value_include);

        select_include_location.select2({
            theme: 'default select2_custom__location select2_custom__location--include',
            width: '100%',
            templateResult: formatCustom,
            templateSelection: formatCustom
        });


        $('<div />').text('Exclude locations').addClass('mt10').appendTo(modal_body_section);

        var row_location = $('<div  style="position: relative"  />').addClass('mt10').appendTo(modal_body_section);

        var select_exclude_location = $('<select />').attr('name', 'exclude_'+key+'[]').addClass('select2 location_group').attr('multiple','multiple').appendTo(row_location);

        get_options_select_location(select_exclude_location, value_exclude);

        select_exclude_location.select2({
            theme: 'default select2_custom__location select2_custom__location--exclude',
            width: '100%',
            templateResult: formatCustom,
            templateSelection: formatCustom
        });

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal_body_section);
        var action_bar_preview = $('<div />').addClass('action-bar').appendTo(modal_body_section_preview);
        var row_location_group = $('<div class="row_location__group">').prependTo(modal_body_section_preview);

        $('<button />').addClass('btn btn-outline ').html('Cancel').click(function () {
            $.unwrapContent('addNewGroupLocationFrm');
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-secondary ').html('Back').click(function () {
            $('.post-frm-section-location').removeClass('active');
            $('.main-group .title').text('Create Location group');
            $(row_location_group).html('');

        }).appendTo(action_bar_preview);

        $('<button />').addClass('btn btn-outline ml10').html('Preview').click(function () {
            var data_include_location = select_include_location.val();
            var data_exclude_location = select_exclude_location.val();

            $.ajax({
                url: $.base_url + '/core/backend_country/previewLocation/hash/' + OSC_HASH,
                data: {include: data_include_location, exclude:data_exclude_location, key : key},
                method : 'POST',
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    $('.post-frm-section-location').addClass('active');
                    //$.unwrapContent('previewGroupLocationFrm');

                    //var modal = $('<div />').addClass('osc-modal').width(650);

                    //$.wrapContent(modal, {key: 'previewGroupLocationFrm',backdrop:'static'});

                    //var header = $('<header />').appendTo(modal_body_section_preview);

                    //$('<div />').addClass('title').attr({style: 'text-align: center'}).html('Preview Location Group').appendTo($('<div />').addClass('main-group').appendTo(header));
                    $('.main-group .title').text('Preview Location Group');
                    //$('<div />').addClass('close-btn').click(function () {
                        //$.unwrapContent('previewGroupLocationFrm');
                    //}).prependTo(header);

                    //var modal_body = $('<div />').addClass('body post-frm').prependTo(modal_body_section_preview);



                    var row_location = $('<div class="row_location"/>').addClass('mt10').prependTo(row_location_group);
                    $('<div/>').html(response.data).prependTo(row_location);

                    //modal.moveToCenter().css('top', '100px');
                }
            });
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-primary ml10').html('Save').click(function () {
            var data_include_location = select_include_location.val();
            var data_exclude_location = select_exclude_location.val();
            $.ajax({
                url: $.base_url + '/core/backend_country/postGroup/hash/' + OSC_HASH,
                data: {include: data_include_location, exclude:data_exclude_location, group_name: group_name.val(), key : key, id : value_id, redirect : redirect},
                method : 'POST',
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    $.unwrapContent('addNewGroupLocationFrm');

                    if (redirect == true) {
                        window.location.reload();
                    } else {
                        alert("Add new location success");
                        $('select[name="'+key+'"]').append("<option value='"+response.data.id+"' data-filter='box-group' data-group='"+response.data.text+"' selected>"+response.data.text+"</option>").trigger('change');
                    }
                }
            });
        }).appendTo(action_bar_preview);

        modal.moveToCenter().css('top', '100px');
    };

    window.initSelectGroupLocation = function (element = null) {
        var box = element ? element : $(this);

        var key = box.attr('data-key');

        var data_location_value = box.attr('data-value');

        const without_provinces = box.attr('data-without_provinces') === '1';

        const desc_country = box.attr('data-desc_country');

        if (typeof key == typeof undefined || key == false) {
            key = $.makeUniqid();
        }

        var select_location = $('<select />').attr('name', key).addClass('select2 location_group w-100').appendTo(box);

        get_options_select_location(select_location, data_location_value, without_provinces);

        select_location.select2({
            theme: 'default select2_custom__location',
            width: '100%',
            templateResult: formatCustom,
            templateSelection: formatCustom,
            language: {
                noResults: function() {
                    var value = $('.select2-search__field')[0].value;
                    return `<button style="width: 100%" type="button"
                        class="select2_custom__location-btn" data-value-group-name="`+value+`" data-insert-cb="boxNewGroupLocation" data-key="`+key+`"
                        >Create new group</button>
                        </li>`;
                }
            },

            escapeMarkup: function (markup) {
                return markup;
            }
        });

        select_location.on('select2:select', function (e) {
            if (typeof desc_country === 'undefined') {
                return;
            }

            const container = $('.' + desc_country)
            if (!container) {
                return;
            }

            const data = e.params.data;
            container.html('')
            $.ajax({
                url: $.base_url + '/core/common/getCountryFromLocation?location=' + data.id,
                method : 'GET',
                beforeSend: function(){
                    container.html('Loading...')
                },
                success: function (response) {
                    if (response.result !== 'OK') {
                        container.html('Load failed...')
                        return;
                    }
                    container.html(response.data.join(', '))
                }
            });
        });
    };

    window.initAddNewGroupLocation = function () {
        var key = $(this).attr('data-key');
        if (typeof key == typeof undefined || key == false) {
            key = $.makeUniqid();
        }

        var value_include = $(this).attr('data-value-include');
        if (typeof value_include == typeof undefined || value_include == false) {
            value_include = null;
        }

        var value_id = $(this).attr('data-value-id');
        if (typeof value_id == typeof undefined || value_id == false) {
            value_id = 0;
        }

        var value_group_name = $(this).attr('data-value-group-name');
        if (typeof value_group_name == typeof undefined || value_group_name == false) {
            value_group_name = null;
        }

        var value_exclude = $(this).attr('data-value-exclude');
        if (typeof value_exclude == typeof undefined || value_exclude == false) {
            value_exclude = null;
        }

        $(this).click(function () {
            newGroupLocation(key, value_id, value_group_name, value_include, value_exclude,true);
        });
    };

})(jQuery);