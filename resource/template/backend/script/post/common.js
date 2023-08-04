(function ($) {
    'use strict';
    window.initPostBulkDeleteBtn = function () {
        $(this).click(function () {
            if (this.getAttribute('disabled') === 'disabled') {
                return;
            }

            let ids = [];

            $('input[name="post_id"]:checked').each(function () {
                ids.push(this.value);
            });

            if (ids.length < 1) {
                alert('Please select least a post to delete');
                return;
            }

            if (this.getAttribute('data-confirm')) {
                if (!window.confirm(this.getAttribute('data-confirm'))) {
                    return;
                }
            }

            this.setAttribute('disabled', 'disabled');

            let btn = this;

            $.ajax({
                url: this.getAttribute('data-link'),
                data: {ids: ids},
                success: function (response) {
                    btn.removeAttribute('disabled');

                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    alert(response.data.message);

                    window.location.reload();
                }
            });
        });
    };

    window.initPostDetailTracking = function () {
        $(this).click(function () {
            var item = $(this);

            $.unwrapContent('postDetailShowTracking');

            var modal = $('<div />').addClass('osc-modal').width('600px');

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html(this.getAttribute('data-title')).appendTo($('<div />').addClass('main-group').appendTo(header));

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            $.ajax({
                url: item.attr('data-url'),
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }
                    $('<div />').html(response.data).css({width: '100%', border: '0'}).appendTo(modal_body);

                }
            });

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('postDetailShowTracking');
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'postDetailShowTracking'});

            modal.moveToCenter().css('top', '50px');
        });
    };
})(jQuery);
