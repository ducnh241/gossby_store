(function ($) {
    function OSC_UI_Form_DatePicker(input, options) {
        this._getTodayDateString = function () {
            var d = new Date();
            return (d.getMonth() + 1).toString().str_pad(2, 0) + '/' + d.getDate().toString().str_pad(2, 0) + '/' + d.getFullYear();
        };

        this._getRawDateString = function (month, date, year) {
            month = parseInt(month);
            
            if(month < 1 || month > 12) {
                return '';
            }
            
            date = parseInt(date);
            
            if(date < 1 || date > 31) {
                return '';
            }
            
            year = parseInt(year).toString();
            
            if(year.length !== 4) {
                return '';
            }
            
            return month.toString().str_pad(2, 0) + '/' + date.toString().str_pad(2, 0) + '/' + year;
        };

        this._getFormattedDateString = function (month, date, year) {
            if (arguments.length === 1) {
                var arr = arguments[0].split('/');

                month = arr[0];
                date = arr[1];
                year = arr[2];
            }

            switch (this.date_format) {
                case 'MM/DD/YYYY':
                    return month.toString().str_pad(2, 0) + '/' + date.toString().str_pad(2, 0) + '/' + year.toString();
                case 'DD/MM/YYYY':
                    return date.toString().str_pad(2, 0) + '/' + month.toString().str_pad(2, 0) + '/' + year.toString();
                case 'YYYY/MM/DD':
                    return year.toString() + '/' + month.toString().str_pad(2, 0) + '/' + date.toString().str_pad(2, 0);
                case 'MM.DD.YYYY':
                    return month.toString().str_pad(2, 0) + '.' + date.toString().str_pad(2, 0) + '.' + year.toString();
                case 'DD.MM.YYYY':
                    return date.toString().str_pad(2, 0) + '.' + month.toString().str_pad(2, 0) + '.' + year.toString();
                case 'YYYY.MM.DD':
                    return year.toString() + '.' + month.toString().str_pad(2, 0) + '.' + date.toString().str_pad(2, 0);
                case 'MM-DD-YYYY':
                    return month.toString().str_pad(2, 0) + '-' + date.toString().str_pad(2, 0) + '-' + year.toString();
                case 'DD-MM-YYYY':
                    return date.toString().str_pad(2, 0) + '-' + month.toString().str_pad(2, 0) + '-' + year.toString();
                case 'YYYY-MM-DD':
                    return year.toString() + '-' + month.toString().str_pad(2, 0) + '-' + date.toString().str_pad(2, 0);
            }
        };

        this._convertFormattedDateStringToRawDateString = function (formatted_date_string) {
            var date_arr = formatted_date_string.split('/');

            if (date_arr.length !== 3) {
                date_arr = formatted_date_string.split('-');

                if (date_arr.length !== 3) {
                    date_arr = formatted_date_string.split('.');
                }
            }

            if (date_arr.length !== 3) {
                return '';
            }

            switch (this.date_format) {
                case 'MM/DD/YYYY':
                case 'MM-DD-YYYY':
                case 'MM.DD.YYYY':
                    return this._getRawDateString(date_arr[0], date_arr[1], date_arr[2]);
                case 'DD/MM/YYYY':
                case 'DD-MM-YYYY':
                case 'DD.MM.YYYY':
                    return this._getRawDateString(date_arr[1], date_arr[0], date_arr[2]);
                case 'YYYY/MM/DD':
                case 'YYYY-MM-DD':
                case 'YYYY.MM.DD':
                    return this._getRawDateString(date_arr[1], date_arr[2], date_arr[0]);
            }
        };

        this._isSelectable = function (date_string) {
            if (!this.max_date && !this.min_date) {
                return true;
            }

            var date = new Date(date_string);

            if (this.max_date && date.setHours(0,0,0,0) > this.max_date) {
                return false;
            }

            if (this.min_date && date < this.min_date.setHours(0,0,0,0)) {
                return false;
            }

            return true;
        };

        this.getValue = function () {
            if(typeof this.getter === 'function') {
                return this.getter.apply(this);
            }
            
            return this._input[this._input[0].nodeName.toLowerCase() === 'input' ? 'val' : 'text']();
        };

        this.setValue = function (date_string) {
//            if (!date_string) {
//                date_string = this._getTodayDateString();
//            }

            this.date_string = date_string;

            if(typeof this.setter === 'function') {
                this.setter.apply(this, [date_string ? this._getFormattedDateString(date_string) : '']);
            } else {
                this._input[this._input[0].nodeName.toLowerCase() === 'input' ? 'val' : 'text'](date_string ? this._getFormattedDateString(date_string) : '');
                this._input.trigger('change');
            }

            $.unwrapContent('OSCDatePicker');

            return true;
        };

        this._initialize = function () {
            if (this.min_date) {
                this.min_date = new Date(this.min_date);
            }

            if (this.max_date) {
                this.max_date = new Date(this.max_date);
            }
            
            this.date_string = this._convertFormattedDateStringToRawDateString(this.getValue());

            var self = this;

            this._input.click(function () {
                self._renderPicker();
            });
        };

        this._renderPicker = function () {
            this._scene = $('<div />').addClass('osc-date-picker');

            this.current_date_string = this.selected_date_string;

            this._renderDateForm(this.date_string);

            $.wrapContent(this._scene, {key: 'OSCDatePicker', close_callback: function () {

                }});

            this._scene.moveToCenter();

            this._scene.osc_dragger({target : this._scene});
        };

        this._renderDateForm = function (date_string) {
            var self = this;

            var month_days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

            var today_date_string = this._getTodayDateString();

            var date_obj = new Date((date_string ? date_string : today_date_string).replace(/^([0-9]+)\/+([0-9]+)\/+([0-9]+)$/, '$1/01/$3'));
            var day_of_week = date_obj.getDay();
            var month = date_obj.getMonth();
            var year = date_obj.getFullYear();

            if ((year % 4 === 0 && year % 100 !== 0) || year % 400 === 0) {
                month_days[1]++;
            }

            var prev_month = month - 1;
            var prev_year = year;

            if (prev_month < 0) {
                prev_month = 11;
                prev_year--;
            }

            var next_month = month + 1;
            var next_year = year;

            if (next_month > 11) {
                next_month = 0;
                next_year++;
            }

            var dates = [];

            for (var i = (day_of_week === 0 ? 6 : day_of_week - 1); i >= 0; i--) {
                dates.push([prev_month, month_days[prev_month] - i, prev_year]);
            }

            for (var i = 1; i <= month_days[month]; i++) {
                dates.push([month, i, year]);
            }

            var k = 0;

            for (var i = ((day_of_week + month_days[month]) % 7); i <= 6; i++) {
                dates.push([next_month, ++k, next_year]);
            }

            this._scene.html('');

            var header = $('<div />').addClass('form-header').appendTo(this._scene);

            $('<ul />').addClass('controls')
                    .append($('<li />').addClass('prev-btn').click(function () {
                        self._renderDateForm((prev_month + 1) + '/1/' + prev_year);
                    }))
                    .append($('<li />').addClass('next-btn').click(function () {
                        self._renderDateForm((next_month + 1) + '/1/' + next_year);
                    }))
                    .appendTo(header);

            $('<span />').addClass('clickable').click(function () {
                self._renderMonthYearForm((month + 1) + '/1/' + year);
            }).html(this._month_names[month] + ' ' + year).appendTo(header);

            var date_grid = $('<div />').addClass('date-grid').appendTo(this._scene);

            for (var i = 0; i < 7; i++) {
                $('<div />').addClass('head').html(this._day_names[i]).appendTo(date_grid);
            }

            for (var i = 0; i < dates.length; i++) {
                var date_string = this._getRawDateString(dates[i][0] + 1, dates[i][1], dates[i][2]);

                $('<div />').addClass((dates[i][0] !== month ? ' other-month' : ''))
                        .attr('date', date_string)
                        .attr('flag', (date_string === this.date_string) ? '1' : '0')
                        .attr('selectable', this._isSelectable(date_string) ? '1' : '0')
                        .click(function () {
                            if (this.getAttribute('selectable') === '1') {
                                self.setValue(this.getAttribute('date'));
                            }
                        })
                        .html(dates[i][1])
                        .appendTo(date_grid);
            }

            $('<div />').addClass('today')
                    .html('<span>Today:</span> <span>' + this._getFormattedDateString(today_date_string) + '</span>')
                    .click(function () {
                        self.setValue(today_date_string);
                    })
                    .appendTo(this._scene);
        };

        this._renderMonthYearForm = function (date_string) {
            var self = this;

            var date_obj = new Date(date_string);

            var flag_year = date_obj.getFullYear();

            this._scene.html('');

            $('<div />').addClass('form-header').append($('<span />').addClass('clickable').click(function () {
                self._renderDateForm(date_string);
            }).html(this._month_names[date_obj.getMonth()] + ' ' + date_obj.getFullYear())).appendTo(this._scene);

            var layout = $('<div />').addClass('month-year-layout').appendTo(this._scene);

            var column = $('<div />').appendTo(layout);

            $('<div />').addClass('tile').html('Months').appendTo(column);

            var month_grid = $('<div />').addClass('month-year-grid').appendTo(column);

            for (var i = 0; i < 12; i++) {
                $('<div />').addClass(i === date_obj.getMonth() ? 'selected current' : '').attr('rel', i + 1).click(function () {
                    month_grid.find('.selected').removeClass('selected');
                    $(this).addClass('selected');
                }).html(this._month_short_names[i]).appendTo(month_grid);
            }

            column = $('<div />').appendTo(layout);

            $('<div />').addClass('tile').html('Years').appendTo(column);

            var year_grid = $('<div />').addClass('month-year-grid').appendTo(column);

            var year_grid_renderer = function () {
                year_grid.html('');

                for (var i = (flag_year - 5); i < (flag_year + 5); i++) {
                    $('<div />').addClass(i === date_obj.getFullYear() ? 'selected' : '').attr('rel', i).click(function () {
                        self._renderDateForm(self._getRawDateString(month_grid.find('.selected').attr('rel'), 1, this.getAttribute('rel')));
                    }).html(i).appendTo(year_grid);
                }
            };

            $('<ul />').addClass('controls')
                    .append($('<li />').addClass('prev-btn').click(function () {
                        flag_year -= 10;
                        year_grid_renderer();
                    }))
                    .append($('<li />').addClass('next-btn').click(function () {
                        flag_year += 10;
                        year_grid_renderer();
                    }))
                    .appendTo(column);

            year_grid_renderer();
        };

        this.setter = null;
        this.getter = null;
        this.date_format = 'MM/DD/YYYY';
        this.min_date = null;
        this.max_date = null;
        this.date_string = null;

        this._input = $(input);
        this._scene = null;
        this._month_names = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        this._month_short_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        this._day_names = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

        if (typeof options !== 'object') {
            options = {};
        }

        $.extend(this, options);

        this._initialize();
    }

    $.fn.OSC_UI_DatePicker = function () {
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
            var instance = $(this).data('osc-ui-frm-datePicker');
            return instance ? instance.getValue() : '';
        }

        return this.each(function () {
            var instance = $(this).data('osc-ui-frm-datePicker');

            if (func) {
                if (instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if (!instance) {
                    $(this).data('osc-ui-frm-datePicker', new OSC_UI_Form_DatePicker(this, opts));
                }
            }
        });
    };

    window.initDatePicker = function () {
        var options = {};

        try {
            options = JSON.parse(this.getAttribute('data-datepicker-config'));
        } catch (e) {
        }

        $(this).OSC_UI_DatePicker(options);
    };
})(jQuery);