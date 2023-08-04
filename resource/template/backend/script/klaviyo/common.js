(function ($) {
    'use strict';
    window.initKlaviyoBulkRequeueBtn = function () {
        $(this).click(function () {
            if (this.getAttribute('disabled') === 'disabled') {
                return;
            }

            var ids = [];

            $('input[name="record_id"]:checked').each(function () {
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
    window.initKlaviyoBulkDeleteBtn = function () {
        $(this).click(function () {
            if (this.getAttribute('disabled') === 'disabled') {
                return;
            }

            var ids = [];

            $('input[name="record_id"]:checked').each(function () {
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
})(jQuery);