(function ($) {
    'use strict';

    var PROVINCE_LOADED = null;

    $.ajax({
        url: $.base_url + '/core/common/browseCountryProvince',
        type: 'get',
        success: function (response) {
            if (response.result !== 'OK') {
                console.log(response.message);
            } else {
                PROVINCE_LOADED = response.data.items;

                for (var x in PROVINCE_LOADED) {
                    var titles = [];
                    var map = {};

                    PROVINCE_LOADED[x].forEach(function (province, y) {
                        var province_title = typeof province === 'string' ? province : province.title;

                        if (province_title.substring(0, 12) === 'Armed Forces') {
                            province_title = 'ZZZZZZZZZ' + province_title;
                        }

                        map[province_title] = y;

                        titles.push(province_title);
                    });

                    titles.sort();

                    var buff = [];

                    titles.forEach(function (title) {
                        buff.push(PROVINCE_LOADED[x][map[title]]);
                    });

                    PROVINCE_LOADED[x] = buff;
                }
            }
        }
    });

    window.initAddress2FrmHandler = function () {
        var frm = $(this);

        var SELECTED_DATA = {};
        var PREV_SELECTED_COUNTRY = frm.find('[data-frm="country"] select').val();

        var skip_contact_frm = parseInt(frm.attr('data-skip-contact-frm')) === 1;

        var __verifyInputSettup = function (key) {
            var input_name_prefix = frm.attr('data-input-name-prefix');
            var input = frm.find('[name="' + input_name_prefix + '[' + key + ']"]');

            input.unbind('.verify').bind('focus.verify', function () {
                $(this).closest('.minified-input').removeClass('error').find('.error__message').remove();
            });

            if (key === 'province') {
                input.bind('verify.verify', function () {
                    if (input[0].nodeName == 'SELECT' && input.val() == 'N/A') {
                        input.trigger('error', ['Please select a province']);
                    }
                });
            } else if (key === 'full_name') {
                input.bind('verify.verify', function () {
                    if (input.val().trim().replace(/\s{2,}/i, ' ').split(' ').length < 2) {
                        input.trigger('error', ['Your name must include both first and last name']);
                    }
                });
            } else {
                input.bind('verify.verify', function () {
                    if (input.val().trim() == '') {
                        input.trigger('error', ['Please enter a valid ' + input.closest('.minified-input').find('> span:not(.error__message)').text()]);
                    }
                });
            }

            input.bind('blur.verify', function () {
                input.trigger('verify');
            }).bind('error.verify', function (e, error_message) {
                $(this).closest('.minified-input').addClass('error').append($('<span />').addClass('error__message').text(error_message));
            });
        };

        var __provinceRenderer = function () {
            if (PROVINCE_LOADED === null) {
                setTimeout(function () {
                    __provinceRenderer();
                }, 100);

                return;
            }

            var country_code = frm.find('[data-frm="country"] select option[value="' + PREV_SELECTED_COUNTRY + '"]').attr('data-code');

            var province_container = frm.find('[data-frm="province"] .minified-input');

            var province_input = province_container.find('select, input');

            var input_id = province_input.attr('id');
            var input_name = province_input.attr('name');

            province_container.html('');

            if (typeof PROVINCE_LOADED[country_code] === 'undefined') {
                $('<input />').attr({
                    type: 'text',
                    placeholder: 'Province',
                    name: input_name,
                    id: input_id,
                    value: SELECTED_DATA[PREV_SELECTED_COUNTRY]
                }).appendTo(province_container);

                $('<span />').text('Province').appendTo(province_container);
            } else {
                var select = $('<select />').attr({
                    name: input_name,
                    id: input_id
                }).appendTo(province_container);

                $('<option />').attr('value', 'N/A').text('Please select province/state').appendTo(select);

                PROVINCE_LOADED[country_code].forEach(function (province) {
                    var option = $('<option />').attr('value', province.title).text(province.title).appendTo(select);

                    if (province.title === SELECTED_DATA[PREV_SELECTED_COUNTRY]) {
                        option.attr('selected', 'selected');
                    }
                });
            }

            __verifyInputSettup('province');
        };

        frm.find('[data-frm="country"] select').change(function () {
            if (PREV_SELECTED_COUNTRY) {
                var province_input = frm.find('[data-frm="province"]').find('select,input');

                if (province_input[0]) {
                    SELECTED_DATA[PREV_SELECTED_COUNTRY] = province_input.val();
                }
            }

            PREV_SELECTED_COUNTRY = $(this).val();

            __provinceRenderer();
        });

        if (frm.attr('data-require') === '1') {
            var input_name_prefix = frm.attr('data-input-name-prefix');

            var require_fields = ['address1', 'city', 'province', 'country', 'zip'];

            if (!skip_contact_frm) {
                if (frm.find('[name="' + input_name_prefix + '[full_name]"]')[0]) {
                    require_fields.push('full_name');
                } else {
                    require_fields.push('first_name');
                    require_fields.push('last_name');
                }

                require_fields.push('phone');
            }

            $.each(require_fields, function (k, key) {
                __verifyInputSettup(key);
            });

            frm.closest('form').submit(function (e) {
                var failed = false;

                $.each(require_fields, function (k, key) {
                    frm.find('[name="' + input_name_prefix + '[' + key + ']"]').trigger('verify');
                });

                if (frm.find('.minified-input.error')[0]) {
                    e.preventDefault();
                }
            });
        }
    };
})(jQuery);