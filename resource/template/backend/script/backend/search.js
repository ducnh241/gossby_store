(function ($) {
    $.BACKEND_SEARCH_URI = '';

    var frm = $('#backend-search-frm');
    var input = frm.find('input[type="text"]');
    var container = frm.find('#backend-search-result');
    var result_container = $('<div />');

    var _FILTER_RECALL_FLAG = false;
    var _FILTER_LOCK_FLAG = false;
    var _FILTER_LAST_KEYWORDS = '';
    var _FILTER_TIMER = null;

    function filter() {
        if (_FILTER_LOCK_FLAG) {
            _FILTER_RECALL_FLAG = true;
            return;
        }

        _FILTER_LOCK_FLAG = true;

        _FILTER_RECALL_FLAG = false;

        var keywords = input.val().replace(/^\s+|\s+$/g, '');

        keywords = searchCleanKeywords(keywords);

        if (keywords != _FILTER_LAST_KEYWORDS) {
            _FILTER_LAST_KEYWORDS = keywords;

            if (keywords != '') {
                __loadFilterResult(keywords, 1);
                return;
            } else {
                result_container.remove();
            }
        }

        _FILTER_LOCK_FLAG = false;

        if (_FILTER_RECALL_FLAG) {
            filter();
        }
    }

    function __loadFilterResult(keywords, page) {
        input.parent().addClass('loading');

        $.ajax({
            type: 'POST',
            url: $.BACKEND_SEARCH_URI,
            data: {keywords: keywords, page: page},
            success: function (response) {
                if (response.result !== 'OK') {
                    if (response.message == -1) {
                        result_container.remove();
                    } else if (response.message != -2) {
                        //alert(response.data.message);
                    }
                } else {
                    _renderFilterResult(response.data);
                }

                input.parent().removeClass('loading');

                _FILTER_LOCK_FLAG = false;

                if (_FILTER_RECALL_FLAG) {
                    filter();
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                input.parent().removeClass('loading');

                _FILTER_LOCK_FLAG = false;

                if (_FILTER_RECALL_FLAG) {
                    filter();
                }

                alert('ERROR [#' + xhr.status + ']: ' + thrownError);
            }
        });
    }

    function _renderFilterResult(data) {
        result_container.html('').appendTo(container);

        var list = $('<ul />').addClass('result').appendTo(result_container);

        for (var i = 0; i < data.docs.length; i++) {
            $('<li />').html(data.docs[i]).appendTo(list);
        }

        _renderPager(data);
    }

    function _renderPager(data) {
        var pager = $('<div />').addClass('pager').appendTo(result_container);

        var total_page = Math.ceil(data.total / data.page_size);

        var CUR_PAGE = Math.ceil(data.offset / data.page_size) + 1;

        if (total_page > 1) {
            var list = $('<ul />').addClass('pagination').data('keywords', data.keywords[0].value).appendTo($('<div />').appendTo(pager));

            var section = 2;

            if (total_page > 1) {
                if (section * 2 + 1 >= total_page) {
                    var start = 1;
                    var end = total_page;
                } else {
                    var start = CUR_PAGE - section;

                    if (start < 1) {
                        start = 1;
                    }

                    var end = start + section * 2;

                    if (end > total_page) {
                        end = total_page;
                        start = end - section * 2;
                    }
                }

                if (start > 1) {
                    if (start > section * 2 + 1) {
                        $('<li />').append($('<a />').attr('href', '#').data('page', 1).append($('<i />').addClass('fa fa-angle-double-left'))).appendTo(list);
                    }

                    $('<li />').append($('<a />').attr('href', '#').data('page', start - section - 1).append($('<i />').addClass('fa fa-angle-left'))).appendTo(list);
                }

                for (var p = start; p <= end; p++) {
                    var page_item = $('<li />').append($('<a />').attr('href', '#').data('page', p).html(p)).appendTo(list);

                    if (p == CUR_PAGE) {
                        page_item.addClass('current');
                    }
                }

                if (end < total_page) {
                    $('<li />').append($('<a />').attr('href', '#').data('page', end + section + 1 >= total_page ? total_page : end + section + 1).append($('<i />').addClass('fa fa-angle-right'))).appendTo(list);

                    if (end < total_page - (section * 2 + 1)) {
                        $('<li />').append($('<a />').attr('href', '#').data('page', total_page).append($('<i />').addClass('fa fa-angle-double-right'))).appendTo(list);
                    }

                }
            }

            list.find('a').click(function () {
                __loadFilterResult(list.data('keywords'), $(this).data('page'));
                return false;
            });
        }

        $('<div />').html("Result -  " + data.total).appendTo(pager);
    }

    input.keyup(function (e) {
        clearTimeout(_FILTER_TIMER);

        if (e.keyCode == 32) {
            filter();
        } else {
            _FILTER_TIMER = setTimeout(function () {
                filter();
            }, 250);
        }
    });
    input.blur(function () {
        clearTimeout(_FILTER_TIMER);
    });
    frm.submit(function () {
        filter();
        return false;
    });

    frm.find('.mrk-history').click(function () {

    });
})(jQuery);