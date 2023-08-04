(function ($) {
    'use strict';
    window.SEARCH_KEYWORD_MIN_LENGTH = 2;

    window.searchCleanKeywords = function (keywords, deep_clean_mode) {
        keywords = keywords.split(/[ \n\r]+/);

        var buff = [];

        for (var k = 0; k < keywords.length; k++) {
            var keyword = keywords[k].trim();

            var mark = '';

            if (deep_clean_mode) {
                mark = XRegExp.replace(keyword, new XRegExp('^(?=^[^\\p{L}]+)^[^-+]*([-+])[^\\p{L}]*\\p{L}.+', 'g'), '$1');
                keyword = XRegExp.replace(keyword, new XRegExp('[^\\p{L}\\d]', 'g'), '');
            }

            // fix lỗi mất ký tự thứ 2
            // if (keyword.length < SEARCH_KEYWORD_MIN_LENGTH) {
            //     continue;
            // }

            if (mark != '+' && mark != '-') {
                mark = '';
            }

            buff.push(mark + keyword);
        }

        return buff.join(' ');
    };
})(jQuery);