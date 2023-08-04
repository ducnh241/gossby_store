(function ($) {
    window.reportTrackRecord = function () {
        var events = this.getAttribute('data-events');

        $(document).ready(function () {
            $.ajax({
                type: 'post',
                data: {req: window.location.href, ref: document.referrer, events: events},
                url: $.base_url + '/report/frontend/record'
            });
        });
    };
})(jQuery);