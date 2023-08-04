(function ($) {
    'use strict';

    $(document).ready(function () {
        function initLiveChat() {
            window.__lc = window.__lc || {};
            window.__lc.license = 12582639;
            ;(function (n, t, c) {
                function i(n) {
                    return e._h ? e._h.apply(null, n) : e._q.push(n)
                }

                var e = {
                    _q: [], _h: null, _v: "2.0", on: function () {
                        i(["on", c.call(arguments)])
                    }, once: function () {
                        i(["once", c.call(arguments)])
                    }, off: function () {
                        i(["off", c.call(arguments)])
                    }, get: function () {
                        if (!e._h) throw new Error("[LiveChatWidget] You can't use getters before load.");
                        return i(["get", c.call(arguments)])
                    }, call: function () {
                        i(["call", c.call(arguments)])
                    }, init: function () {
                        var n = t.createElement("script");
                        n.async = !0, n.type = "text/javascript", n.src = "https://cdn.livechatinc.com/tracking.js", t.head.appendChild(n)
                    }
                };
                !n.__lc.asyncInit && e.init(), n.LiveChatWidget = n.LiveChatWidget || e
            }(window, document, [].slice))
        }

        function initialize(i) {
            var e;
            (initLiveChat(), e = i.createElement("noscript"), e.insertAdjacentHTML("beforeend",'<a href="https://www.livechatinc.com/chat-with/12582639/" rel="nofollow">Chat with us</a>, powered by <a href="https://www.livechatinc.com/?welcome" rel="noopener nofollow" target="_blank">LiveChat</a>'), i.body.appendChild(e));
        }

        function initiateCall() {
            initialize(document)
        }

        window.addEventListener ? window.addEventListener("load", initiateCall, !1) : window.attachEvent("load", initiateCall, !1);
    });
})(jQuery);

window.initLiveChatAttachment = function () {
    var body = $(this);
    var file_input = $('<input />').attr({type: 'hidden',name: 'attachment'}).appendTo(body);
    var uploader = $('<div />').addClass('btn btn-primary btn-small').appendTo(body);
    var preview = $('<div />').appendTo(body);
    uploader.osc_uploader({
        max_files: 1,
        process_url: body.attr('data-upload-url'),
        btn_content: 'Attach an image',
        dragdrop_content: 'Drop here to upload',
        extensions: ['png', 'jpg', 'gif', 'jpeg'],
        xhrFields: {withCredentials: true},
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-OSC-Cross-Request': 'OK'
        }
    }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
        uploader.hide();
        file_input.val('');
    }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
        try {
            response = JSON.parse(response);
        } catch (e) {
            return;
        }

        if (response.result === 'OK') {
            file_input.val(response.data);
        }
    }).bind('uploader_upload_error uploader_cancel', function (e, file_id, error_code, error_message) {
        uploader.show();
        file_input.val('');
    });

    initFileUploadHandler(uploader, preview);
};


