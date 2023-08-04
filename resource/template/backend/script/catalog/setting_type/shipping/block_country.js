(function ($) {
    'use strict';

    window.countryPostFrm__addTag = function (tag, input) {
        input.val('');

        tag = tag.trim();

        var container = input.closest('.frm-grid').find('.product-tags');

        var tags = {};

        container.find('input[type="hidden"]').each(function () {
            tags[this.value.toLowerCase()] = $(this).closest('.product-tag');
        });

        if (typeof tags[tag.toLowerCase()] !== 'undefined') {
            container.prepend(tags[tag.toLowerCase()]);
        } else {
            container.append(_countryPostFrm_renderTag(tag,input.attr('type-name')));
        }
        $('#input-tags').focus();
    };

    window.countryPostFrm__initTags = function () {
        $(this).find('input[type="text"]').keydown(function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        });

        var type = $(this).attr('type')

        var tags = JSON.parse(this.getAttribute('data-tags'));

        if (tags === null) {
            return;
        }

        var container = $(this).find('.product-tags');

        $.each(tags, function (k, tag) {
            container.append(_countryPostFrm_renderTag(tag,type));
        });
    };

    function _countryPostFrm_renderTag(tag,type) {
        var config;

        if (type == 'block_ip') {
            config = 'config[list/block_ip_countries][]';
        }else if (type == 'block_shipping') {
            config = 'config[shipping/block_countries][]';
        }else if (type == 'list_premium') {
            config = 'config[catalog/campaign/fleeceBlanket_50x60/list_country_by_premium][]';
        }else if (type == 'block_auto_convert_price') {
            config = 'config[list/block_countries_auto_convert_price][]';
        }

        return $('<div />').addClass('product-tag').attr('title', tag).text(tag).append($('<input />').attr({type: 'hidden', name: '' + config, value: tag})).append($('<ins />').click(function () {
            $(this).closest('.product-tag').remove();
        }));
    }
})(jQuery);
