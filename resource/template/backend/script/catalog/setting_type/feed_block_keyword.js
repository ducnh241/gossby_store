(function ($) {
    'use strict';

    window.catalogInitSettingFeedBlockKeyword = function () {
        var container = $(this);

        var frm_name = container.attr('data-name');

        var json = fetchJSONTag(container, 'feed-block-keyword');

        var input_keyword = $('<input/>')
            .attr('type', 'text')
            .attr('id', 'input-feed-block-keyword')
            .attr('value', '')
            .addClass('styled-input')
            .appendTo(container);

        var block_keywords = $('<div>').addClass('tags')
            .attr('id', 'block-keywords')
            .appendTo(container);

        input_keyword.on('keyup keypress', function (e) {
            var name_tag = input_keyword.val().trim();
            var keyCode = e.keyCode || e.which;

            if (keyCode === 13 && name_tag) {
                __addTag(name_tag);
                input_keyword.val('');
                e.preventDefault();
            }
        });

        $('form').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        function __addTag(value) {
            var tag = $('<li/>').addClass('tag').text(value).appendTo(block_keywords);
            $('<input/>').attr('type', 'hidden')
                .attr('name', frm_name + '[]')
                .attr('value', value).appendTo(tag);
            $('<div/>').appendTo(tag).text('x').addClass('btn-remove-tag').click(function () {
                tag.remove();
                if (!block_keywords.children().length && !block_keywords.find('input[value=""]').length) {
                    $('<input/>').attr('type', 'hidden')
                        .attr('name', frm_name)
                        .attr('value', '')
                        .appendTo(block_keywords);
                }
            });
        }

        if (json.data) {
            $.each(json.data, function (key, value) {
                __addTag(value);
            });
        }
    };
})(jQuery);
