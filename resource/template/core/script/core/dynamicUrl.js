(function ($) {
    'use strict';

    function OSC_Dynamic_Url() {
        this.pushState = function (state, replace_flag) {
            if (state instanceof jQuery) {
                state = state[0];
            }

            if (state instanceof HTMLElement) {
                var url = state.getAttribute('data-url');

                if (!url) {
                    url = state.getAttribute('href');

                    if (!url) {
                        return true;
                    }
                }

                var title = null;

                $.each(['data-title', 'title', 'alt'], function (k, attr_key) {
                    title = state.getAttribute(attr_key);

                    if (title) {
                        return false;
                    }
                });

                if (!title && state.nodeName === 'a') {
                    title = state.textContent;
                }

                state = {url: url, title: title};
            }

            if (typeof state !== 'object' || typeof state.url !== 'string') {
                return;
            }

            if (typeof state.title !== 'string') {
                state.title = $('head title').text();
            }

            state.url = state.url.replace($.base_url, '');

            this._titles[$.md5(state.url.replace(/^\/+/, '').replace(/\/+$/, ''))] = title;

            try {
                if (replace_flag) {
                    window.history.replaceState({request: state.url}, state.title, state.url);
                } else {
                    window.history.pushState({request: state.url}, state.title, state.url);
                }
            } catch (e) {
                window.location.hash = '!' + state.url;
                this._HASH_CHANGE_LOCK = true;
            }
        };

        this._processPopstate = function (e, request) {
            var request_to_validate = request.replace(/^\/+/, '').replace(/\/+$/, '');

            var title = this._titles[$.md5(request_to_validate)];

            if (typeof title !== 'undefined') {
                for (var i = 0; i < this._registry.length; i++) {
                    var checker = this._registry[i][0];

                    if (checker instanceof RegExp) {
                        if (checker.test(request_to_validate)) {
                            document.title = title;
                            this._registry[i][1](e, request, title);
                            return;
                        }
                    } else if ((typeof checker).toLowerCase() === 'string') {
                        if (request_to_validate === checker) {
                            document.title = title;
                            this._registry[i][1](e, request, title);
                            return;
                        }
                    } else {
                        if (checker(request_to_validate)) {
                            document.title = title;
                            this._registry[i][1](e, request, title);
                            return;
                        }
                    }
                }
            }

            window.location.reload();
        };

        this.addProcessor = function () {
            if (arguments.length < 2) {
                return this;
            }

            var func = arguments[arguments.length - 1];

            if ((typeof func).toLowerCase() !== 'function') {
                return this;
            }

            for (var i = 0; i < arguments.length - 1; i++) {
                var checker = arguments[i];

                if (checker instanceof RegExp) {
                    this._registry.push([checker, func]);
                } else if ((typeof checker).toLowerCase() === 'string') {
                    checker = checker.replace($.base_url, '');
                    checker = checker.replace(/^\/+/, '');
                    checker = checker.replace(/\/+$/, '');

                    this._registry.push([checker, func]);
                } else if ((typeof checker).toLowerCase() === 'function') {
                    this._registry.push([checker, func]);
                }
            }

            return this;
        };

        this.setNoStateCallback = function (callback) {
            this.no_state_callback = callback;
            return this;
        };

        this._HASH_CHANGE_LOCK = false;
        this._registry = [];
        this.no_state_callback = null;
        this._titles = {};

        var $this = this;

        $(window).bind('popstate', function (e) {
            if (e.originalEvent.state) {
                $this._processPopstate(e, e.originalEvent.state.request);
            } else if (typeof $this.no_state_callback === 'function') {
                $this.no_state_callback(e);
            }
        }).bind('hashchange', function (e) {
            if ($this._HASH_CHANGE_LOCK) {
                $this._HASH_CHANGE_LOCK = false;
                return;
            }

            if (window.location.hash.substr(0, 2) === '#!') {
                $this._processPopstate(e, window.location.hash.substr(2));
            }
        });
    }

    $.dynamicUrl = new OSC_Dynamic_Url();

    window.initPushState = function () {
        $(this).click(function () {
            $.dynamicUrl.pushState(this);
        });
    };
})(jQuery);