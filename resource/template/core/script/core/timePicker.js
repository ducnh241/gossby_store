(function ($) {
    'use strict';

    function OSC_TimePicker(input, options) {
        this._initialize = function () {
            var self = this;

            var value = this._input[this._input[0].nodeName.toLowerCase() === 'input' ? 'val' : 'text']();

            this._correctValue(value);

            this._input.click(function () {
                self._renderPicker();
            });
        };

        this._renderPicker = function () {
            var self = this;

            this._scene = $('<div />').attr('flag', 'hour').addClass('osc-time-picker');

            this._digital_clock = $('<div />').addClass('digital-clock').appendTo(this._scene);

            this._digital_clock_hour = $('<span />').addClass('hour').click(function () {
                self._scene.attr('flag', 'hour');
            }).appendTo(this._digital_clock);
            $('<span />').addClass('separate').html(':').appendTo(this._digital_clock);
            this._digital_clock_minute = $('<span />').addClass('minute').click(function () {
                self._scene.attr('flag', 'minute');
            }).appendTo(this._digital_clock);

            this._analog_clock = $('<div />').addClass('analog-clock').appendTo(this._scene);

            $('<ul />').addClass('indicator-lights').append($('<li />').addClass('AM-light').html('AM')).append($('<li />').addClass('PM-light').html('PM')).appendTo(this._analog_clock);

            this._clock = $('<div />').addClass('clock-face').appendTo(this._analog_clock);

            this._flag_container = $('<ul />').addClass('flags').appendTo(this._clock);

            for (var i = 0; i < 12; i++) {
                $('<li />').appendTo(this._flag_container);
            }

            var hands = $('<ul />').appendTo(this._clock);

            this._hour_hand = $('<li />').addClass('hour-hand').mousedown(function () {
                self._scene.attr('flag', 'hour');
            }).appendTo(hands);
            this._minute_hand = $('<li />').append($('<div />')).addClass('minute-hand').mousedown(function () {
                self._scene.attr('flag', 'minute');
            }).appendTo(hands);

            this._setEvents();

            $.wrapContent(this._scene, {key: 'OSCTimePicker', close_callback: function () {

                }});

            this._scene.moveToCenter();

            this._digital_clock.osc_dragger({
                target: this._scene
            });

            this._update();
        };

        this._setEvents = function () {
            var self = this;

            this._clock.mousedown(function (e) {
                if (e.which !== 1) {
                    return;
                }

                self._clock.addClass('active');

                $(document).bind('mouseup.oscTimePicker', function (e) {
                    $(document).unbind('.oscTimePicker');
                    self._calculateValueByDegress(e);
                    self._clock.removeClass('active');
                });
                $(document).bind('mousemove.oscTimePicker', function (e) {
                    self._calculateValueByDegress(e);
                });
                self._calculateValueByDegress(e);
            });

            this._clock.bind('mousewheel', function (e) {
                e.preventDefault();

                var flag = self._scene.attr('flag');
                var value = parseInt(self['_' + flag]);

                if (e.originalEvent.wheelDelta > 0) {
                    value++;
                } else {
                    value--;
                }

                if (flag === 'hour') {
                    if (value > 23) {
                        value = 0;
                    } else if (value < 0) {
                        value = 23;
                    }
                } else {
                    if (value > 59) {
                        self._hour++;
                        value = value % 60;
                    } else if (value < 0) {
                        self._hour--;
                        value = 60 - (Math.abs(value) % 60);
                    }

                    if (self._hour > 23) {
                        self._hour = self._hour % 24;
                    } else if (self._hour < 0) {
                        self._hour = 24 - (Math.abs(self._hour) % 24);
                    }
                }

                self['_' + flag] = value;

                self._update(e);
            });
        };

        this._calculateValueByDegress = function (e) {
            var offset = this._clock.offset();
            var center = {
                y: offset.top + this._clock.height() / 2,
                x: offset.left + this._clock.width() / 2
            };

            var a = center.y - e.pageY;
            var b = center.x - e.pageX;

            var deg = Math.atan2(a, b) * (180 / Math.PI);

            deg = parseInt(deg);

            var flag = this._scene.attr('flag');

            var round_base = flag === 'minute' ? 6 : 30;

            deg += ((deg % round_base) > round_base / 2) ? (round_base - (deg % round_base)) : (-(deg % round_base));

            deg -= 90;

            if (deg < 0) {
                deg = 360 + deg;
            }

            var value = deg / round_base;

            if (flag === 'hour') {
                if (value == 0) {
                    if (this._hour == 11) {
                        this._hour_additional = 12;
                    } else if (this._hour == 23) {
                        this._hour_additional = 0;
                    }
                } else if (value == 11) {
                    if (this._hour == 0) {
                        this._hour_additional = 12;
                    } else if (this._hour == 12) {
                        this._hour_additional = 0;
                    }
                }

                value += this._hour_additional;
            } else {
                this._minute_hand[(value % 5) ? 'removeClass' : 'addClass']('reach-flag');
            }

            this['_' + flag] = value;

            this._update();
        };

        this._update = function () {
            if (this._hour === false) {
                this._hour = 0;
            }

            if (this._minute === false) {
                this._minute = 0;
            }

            this._scene[this._hour < 12 ? 'removeClass' : 'addClass']('PM');

            this['_hour_hand'].css('transform', 'rotateZ(' + (this._hour * 30) + 'deg)');
            this['_minute_hand'].css('transform', 'rotateZ(' + (this._minute * 6) + 'deg)');

            var digital_clock_values = [this._hour, this._minute];

            for (var i = 0; i < digital_clock_values.length; i++) {
                digital_clock_values[i] += '';

                if (digital_clock_values[i].length !== 2) {
                    digital_clock_values[i] = '0' + digital_clock_values[i];
                }
            }

            this._digital_clock_hour.html(digital_clock_values[0]);
            this._digital_clock_minute.html(digital_clock_values[1]);

            var value = digital_clock_values[0] + ':' + digital_clock_values[1];
            
            this.setValue(value);
        };

        this.getValue = function () {
            if (typeof this.getter === 'function') {
                return this.getter.apply(this);
            }

            if (this._hour === false || this._minute === false) {
                return '';
            }

            var digital_clock_values = [this._hour, this._minute];

            for (var i = 0; i < digital_clock_values.length; i++) {
                digital_clock_values[i] += '';

                if (digital_clock_values[i].length !== 2) {
                    digital_clock_values[i] = '0' + digital_clock_values[i];
                }
            }

            return digital_clock_values[0] + ':' + digital_clock_values[1];
        };
        
        this._correctValue = function(value) {            
            var matches = value.match(/^\s*([0-9]{1,2})\s*\:\s*([0-9]{1,2})(\s+(PM|AM))?\s*$/i);

            if (matches) {
                matches[1] = parseInt(matches[1]);
                matches[2] = parseInt(matches[2]);

                if (isNaN(matches[1])) {
                    matches[1] = 0;
                }

                if (isNaN(matches[2])) {
                    matches[2] = 0;
                }

                if (matches[4]) {
                    if (matches[4].toLowerCase() === 'pm') {
                        matches[1] += matches[1] === 12 ? 0 : 12;
                    } else if (matches[1] === 12) {
                        matches[1] = 0;
                    }
                }

                if (matches[1] > 23 || matches[1] < 0) {
                    matches[1] = 0;
                }

                if (matches[2] > 59 || matches[2] < 0) {
                    matches[2] = 0;
                }

                this._hour = matches[1];
                this._minute = matches[2];
            } else {
                this._hour = false;
                this._minute = false;

                value = '';
            }
        };

        this.setValue = function (value) {
            this._correctValue(value);
            
            if(this._hour === false || this._minute === false) {
                value = '';
            } else {
                value = this._hour.toString().str_pad(2,0) + ':' + this._minute.toString().str_pad(2,0);
            }
            
            if (typeof this.setter === 'function') {
                this.setter.apply(this, [value]);
            } else {
                this._input[this._input[0].nodeName.toLowerCase() === 'input' ? 'val' : 'html'](value);
            }
        };

        this.setter = null;
        this.getter = null;
        this._input = $(input);
        this._scene = null;
        this._digital_clock = null;
        this._digital_clock_hour = null;
        this._digital_clock_minute = null;
        this._analog_clock = null;
        this._flag_container = null;
        this._hour_hand = null;
        this._minute_hand = null;
        this._hour = 0;
        this._hour_additional = 0;
        this._minute = 0;

        if (typeof options !== 'object') {
            options = {};
        }

        $.extend(this, options);

        this._initialize();
    }

    $.fn.OSC_UI_TimePicker = function () {
        var func = null;

        if (arguments.length > 0 && typeof arguments[0] == 'string') {
            func = arguments[0];
        }

        if (func) {
            var opts = [];

            for (var x = 1; x < arguments.length; x++) {
                opts.push(arguments[x]);
            }
        } else {
            opts = arguments[0];
        }

        if (func && func.toLowerCase() == 'getvalue') {
            var instance = $(this).data('osc-ui-timePicker');
            return instance ? instance.getValue() : '';
        }

        return this.each(function () {
            var instance = $(this).data('osc-ui-timePicker');

            if (func) {
                if (instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if (!instance) {
                    $(this).data('osc-ui-timePicker', new OSC_TimePicker(this, opts));
                }
            }
        });
    };

    window.initTimePicker = function () {
        $(this).OSC_UI_TimePicker();
    };
})(jQuery);
