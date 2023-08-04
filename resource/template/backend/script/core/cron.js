(function ($) {
    'use strict';

    window.initCoreCronBulkActionBtn = function () {
        $(this).click(function () {
            if (this.getAttribute('disabled') === 'disabled') {
                return;
            }

            var ids = [];

            $('input[name="queue_id"]:checked').each(function () {
                ids.push(this.value);
            });

            if (ids.length < 1) {
                return;
            }

            if (this.getAttribute('data-confirm')) {
                if (!window.confirm(this.getAttribute('data-confirm'))) {
                    return;
                }
            }

            this.setAttribute('disabled', 'disabled');

            var btn = this;

            $.ajax({
                url: this.getAttribute('data-link'),
                data: {id: ids},
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

    window.initCoreCronGetInfo = function (link) {
        $.unwrapContent('coreCronInfoFrm');

        var modal = $('<div />').addClass('osc-modal').css('width', 'calc(100% - 100px)');

        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html(this.getAttribute('data-title')).appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('coreCronInfoFrm');
        }).appendTo(header);

        var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

        $('<iframe />').attr('src', link).css({width: '100%', height: 'calc(100vh - 300px)', border: '0'}).appendTo(modal_body);

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

        $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
            $.unwrapContent('coreCronInfoFrm');
        }).appendTo(action_bar);

        $.wrapContent(modal, {key: 'coreCronInfoFrm'});

        modal.moveToCenter().css('top', '50px');
    };
})(jQuery);