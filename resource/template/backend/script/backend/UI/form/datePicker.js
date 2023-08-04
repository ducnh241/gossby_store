(function($){
    function _OSC_UI_Form_DatePicker() {
        this.scrivi_data_odierna = function() {
            var nd = new Date();
            var data = "";

            data += ( nd.getMonth() + 1 ) + '/';
            data += nd.getDate() < 10 ? '0' + nd.getDate() + ' ' : nd.getDate() + '/';
            data += nd.getFullYear();

            return data;
        }
        
        this.formatDate = function(the_date_arr, code) {
            var _return = '';

            switch(code) {
                case 1: // In: MM/DD/YYYY Out: MM/DD/YYYY
                    _return = the_date_arr[0].concat("/").concat(the_date_arr[1]).concat("/").concat(the_date_arr[2]);
                    break;
                case 2: // In: MM/DD/YYYY Out: DD/MM/YYYY
                    _return = the_date_arr[1].concat("/").concat(the_date_arr[0]).concat("/").concat(the_date_arr[2]);
                    break;
                case 3: // In: MM/DD/YYYY Out: YYYY/MM/DD
                    _return = the_date_arr[2].concat("/").concat(the_date_arr[0]).concat("/").concat(the_date_arr[1]);
                    break;
                case 4: // In MM.DD.YYYY Out: MM.DD.YYYY
                    _return = the_date_arr[0].concat(".").concat(the_date_arr[1]).concat(".").concat(the_date_arr[2]);
                    break;
                case 5: // In MM.DD.YYYY Out: DD.MM.YYYY
                    _return = the_date_arr[1].concat(".").concat(the_date_arr[0]).concat(".").concat(the_date_arr[2]);
                    break;
                case 6: // In MM.DD.YYYY Out: YYYY.MM.DD
                    _return = the_date_arr[2].concat(".").concat(the_date_arr[0]).concat(".").concat(the_date_arr[1]);
                    break;
                case 7: // In MM-DD-YYYY Out: MM-DD-YYYY
                    _return = the_date_arr[0].concat("-").concat(the_date_arr[1]).concat("-").concat(the_date_arr[2]);
                    break;
                case 8: // In MM-DD-YYYY Out: DD-MM-YYYY
                    _return = the_date_arr[1].concat("-").concat(the_date_arr[0]).concat("-").concat(the_date_arr[2]);
                    break;
                case 9: // In MM-DD-YYYY Out: YYYY-MM-DD
                    _return = the_date_arr[2].concat("-").concat(the_date_arr[0]).concat("-").concat(the_date_arr[1]);
                    break;
            }

            return _return;
        }
        
        this.MENU = $('<div />').prop({className : 'osc-ui-frm-date-picker dis-sel'}).appendTo(document.body);
        
        $('<div />').appendTo(this.MENU);
    }
    
    function OSC_UI_Form_DatePicker(target, options) {
        this.render = function(the_date) {
            $($.osc_UIFormDatePicker.MENU.find('> div')).html(this.renderer.renderDate(the_date));
        }
        
        this.displayMonthYear = function(the_date) {
            $($.osc_UIFormDatePicker.MENU.find('> div')).html(this.renderer.displayMonthYear(the_date));
        }
        
        this.getDateObjByVal = function() {
            var today_date;

            if(this.value != '') {
                var the_date;
                var the_date_arr = this.value.split("/");

                if(the_date_arr.length != 3) {
                    the_date_arr = this.value.split(".");

                    if(the_date_arr.length != 3) {
                        the_date_arr = this.value.split("-");
                    }
                }

                if(the_date_arr.length == 3) {
                    the_date = $.osc_UIFormDatePicker.formatDate(the_date_arr, this.format_code);

                    today_date = new Date(the_date);

                    if(today_date == NaN) {
                        today_date = new Date();
                    }
                } else {
                    today_date = new Date();
                }
            } else {
                today_date = null;
            }

            return today_date;
        }
        
        this.isLinkable = function(the_date) {
            if(this.max_date == null && this.min_date == null) {
                return true;
            }
            
            var the_cur_date = new Date(the_date);

            if(this.max_date != null && this.min_date != null) {
                if(the_cur_date <= this.max_date && the_cur_date >= this.min_date) {
                    return true;
                }
            } else if(this.max_date != null) {
                if(the_cur_date <= this.max_date) {
                    return true;
                }
            } else if(the_cur_date >= this.min_date) {
                return true;
            }

            return false;
        }
        
        this.determineDate = function(in_date, skip_format) {
            var the_date_arr = in_date.split('/');

            if(the_date_arr.length != 3) {
                the_date_arr = in_date.split('.');

                if(the_date_arr.length != 3) {
                    the_date_arr = in_date.split('-');
                }
            }

            if(parseInt(the_date_arr[0]) < 10 && the_date_arr[0].length == 1) {
                the_date_arr[0] = '0' + the_date_arr[0];
            }

            if(parseInt(the_date_arr[1]) < 10 && the_date_arr[1].length == 1) {
                the_date_arr[1] = '0' + the_date_arr[1];
            }

            if(parseInt(the_date_arr[2]) < 10 && the_date_arr[2].length == 1) {
                the_date_arr[2] = '0' + the_date_arr[2];
            }

            return skip_format ? the_date_arr : $.osc_UIFormDatePicker.formatDate(the_date_arr, this.format_code);
        }
        
        this.selectDate = function(the_date) {            
            this.today_date_string = the_date;
            
            the_date = this.determineDate(the_date);
            
            this.renderer.setDate(the_date);
            
            this.value = the_date;
			
            if(this.input) {
                this.input.value = the_date;
            }
            
            $(this.container).osc_toggleMenu('hide');
        }
        
        this.changeMonth = function(month) {        
            month = parseInt(month);
            
            if(isNaN(month) || month < 0 || month > 11) {
                return;
            }
            
            if(this.min_date != null || this.max_date != null) {
                if(this.min_date) {
                    if(this.sel_year < this.min_date.getFullYear()) {
                        return false;
                    } else if(this.sel_year == this.min_date.getFullYear()) {
                        if(month < this.min_date.getMonth()) {
                            return false;
                        }
                    }
                }

                if(this.max_date) {
                    if(this.sel_year > this.max_date.getFullYear()) {
                        return false;
                    } else if(this.sel_year == this.max_date.getFullYear()) {
                        if(month > this.max_date.getMonth()) {
                            return false;
                        }
                    }
                }
            }

            this.sel_month = month;

            this.displayMonthYear((this.sel_month + 1) + '/1/' + this.sel_year);
        }
        
        this.changeYear = function(year) {            
            if(year == this.sel_year) {
                return false;
            }

            if(this.min_date != null || this.max_date != null) {
                if(this.min_date) {
                    if(year < this.min_date.getFullYear()) {
                        return false;
                    }
                }

                if(this.max_date) {
                    if(year > this.max_date.getFullYear()) {
                        return false;
                    }
                }
            }
            
            var curYearObj = $('#date-picker-year-' + this.sel_year, $.osc_UIFormDatePicker.MENU);

            if(curYearObj[0]) {
                curYearObj.removeClass('current');
            }

            $('#date-picker-year-' + year, $.osc_UIFormDatePicker.MENU).addClass('current');

            this.sel_year = year;

            this.changeMonth(this.sel_month);
        }
        
        this.changeMonthYear = function(is_cancel) {            
            if(! is_cancel) {
                this.today_date_string = (this.sel_month + 1) + "/1/" + this.sel_year;
            }

            this.render();
        }
        
        this.getValue = function() {   
            return this.value;
        }
        
        this.setValue = function(the_date, code) {		
            if(code) {
                this.format_code = code;
            }

            this.renderer.setDate(the_date);
		
            if(this.input) {
                this.input.value = inst.value;
            }
		
            if(the_date) {
                var the_date_arr = this.determineDate(the_date, true);
			
                switch(this.format_code) {
                    case 1: // In: MM/DD/YYYY
                    case 4: // In: MM.DD.YYYY
                    case 7: // In: MM-DD-YYYY
                        the_date = the_date_arr[0].concat("/").concat(the_date_arr[1]).concat("/").concat(the_date_arr[2]);
                        break;
                    case 2: // In: DD/MM/YYYY
                    case 5: // In: DD.MM.YYYY
                    case 8: // In: DD-MM-YYYY
                        the_date = the_date_arr[1].concat("/").concat(the_date_arr[0]).concat("/").concat(the_date_arr[2]);
                        break;
                    case 3: // In: YYYY/MM/DD
                    case 6: // In: YYYY.MM.DD
                    case 9: // In: YYYY-MM-DD
                        the_date = the_date_arr[1].concat("/").concat(the_date_arr[2]).concat("/").concat(the_date_arr[0]);
                        break;
                }
            } else {
                the_date = this.scrivi_data_odierna();
            }
		
            this.today_date_string = the_date;
		
            return true;
        }
        
        this._renderForm = function() {
            this.renderer.render();
        }
        
        if(typeof options != 'object') {
            options = {};
        }
        
        if(target.tagName == 'INPUT') {
            target.type = 'text';
            
            options.container = $('<div />').data('osc-ui-frm-datePicker', this);
            $(target).after(options.container);
            
            options.input = $(target);
            
            var attr_keys = ['name','id','value','disabled'];
            
            for(var x = 0; x < attr_keys.length; x ++) {
                if(options.input.attr(attr_keys[x])) {
                    options[attr_keys[x]] = options.input.attr(attr_keys[x]);
                }
            }
        } else {
            options.container = $(target);
            options.input = $('<input />').prop('type', 'text').data('osc-ui-frm-datePicker', this);
        }
            		
        this.container = null;
        this.id = null;
        this.name = null;
        this.disabled = false;
        this.keyboard_disabled = false;
        this.sel_month = '';
        this.sel_year = '';
        this.format_code = 2;
        this.fdweek = 1;
        this.sun_num = 6;
        this.sat_num = 5;
        this.input = false;
        this.wrapper = null;
        this.scene = null;
        this.value = '';
        this.min_date = null;
        this.max_date = null;
        this.today_date_string = $.osc_UIFormDatePicker.scrivi_data_odierna();
        this.renderer = null;
        this.holidays = {};
            
        $.extend(this, options);
        
        if(this.min_date) {
            this.min_date = new Date(this.min_date);
        }
        
        if(this.max_date) {
            this.max_date = new Date(this.max_date);
        }
        
        if(typeof this.renderer != 'object' || this.renderer === null) {
            this.renderer = new OSC_UI_Form_DatePicker_Renderer();
        }
        
        this.renderer.setInstance(this);
        
        this._renderForm();
        
        var self = this;
        
        $(this.container).osc_toggleMenu({
            menu : $.osc_UIFormDatePicker.MENU,
            delay_time : 100,
            divergent_x : 2,
            divergent_y : 4,
            hold_menu_click : true,
            open_hook : function() {
                self.render();
            },
            close_hook : function() {
                try {
                    self.renderer.cleanDate();
                    $.osc_UIFormDatePicker.MENU.lastChild.innerHTML = '';
                } catch(e) {}
            }
        });
    }
    
    function OSC_UI_Form_DatePicker_Renderer() {
        this.setInstance = function(inst) {
            this.inst = inst;
            return this;
        }
        
        this.setDate = function(the_date) {
            this.inst.container.find('input').val(the_date);
        }
        
        this.render = function() {            
            $('<input />').val(this.inst.value).appendTo(this.inst.container).osc_ui_form_input({width : this.inst.container.width()});
        }
        
        this.renderDate = function(the_date) {
            if(the_date != null) {
                this.inst.today_date_string = the_date;
            }
            
            var self = this;

            var date_today = new Date();
            var today_date = new Date(this.inst.today_date_string);
            var cur_date   = this.inst.getDateObjByVal();
            var month_days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

            var this_day  = today_date.getDay();
            var this_month = today_date.getMonth();
            var this_date = today_date.getDate();
            var this_year = today_date.getFullYear();

            if((this_year%4 == 0 && this_year%100 != 0) || this_year%400 == 0) {
                month_days[1] ++;
            }

            var start_spaces       = this_date;
            var prev_month         = this_month;
            var prev_day           = this_date;
            var prev_year          = this_year;
            var this_previous_year = this_year - 1;
            var this_next_year     = this_year + 1;

            if(prev_month < 1) {
                prev_month = 12;
                prev_year  = prev_year - 1;
            }

            if(this_date > month_days[prev_month-1]) {
                prev_day = month_days[prev_month-1];
            }

            var next_month = this_month + 2;
            var next_day   = this_date;
            var next_year  = this_year;

            if(next_month > 12) {
                next_month = 1;
                next_year  = next_year + 1;
            }

            if(this_date > month_days[next_month-1]) {
                next_day = month_days[next_month-1];
            }

            while(start_spaces > 7) {
                start_spaces -= 7;
            }

            start_spaces = this_day - start_spaces + 1;
            start_spaces = start_spaces - this.inst.fdweek;

            if(start_spaces < 0) {
                start_spaces += 7;
            }
            
            var container = $('<div />').addClass('month-days');
            var row = $('<div />').addClass('top-bar').appendTo(container);
            var cell = '';
            
            $('<span />').addClass('prev-btn').click(function(){ self.inst.render(prev_month + '/' + prev_day + '/' + prev_year); }).appendTo(row);
            $('<span />').addClass('next-btn').click(function(){ self.inst.render(next_month + '/' + next_day + '/' + next_year); }).appendTo(row);
            $('<span />').addClass('title').click(function(){ self.inst.displayMonthYear((this_month + 1) + '/1/' + this_year); }).html(this.month_names[this_month] + ' ' + this_year).appendTo(row);
           
            row = $('<div />').addClass('row head').appendTo(container);

            for(var i = 0; i < 7; i ++) {
                $('<div />').addClass('cell').html(this.day_names[i]).appendTo(row);
            }

            for(var s = 0; s < start_spaces; s ++) {
                var the_date, month, year;

                if(this_month == 0) {
                    the_date = month_days[11]-(start_spaces-(s+1));
                    month    = 12;
                    year     = this_year - 1;
                } else {
                    the_date = month_days[this_month-1]-(start_spaces-(s+1));
                    month    = this_month;
                    year     = this_year;
                }

                var the_cur_date = new Date(month + '/' + the_date + '/' + year);

                if(s == 0) {
                    row = $('<div />').addClass('row').appendTo(container);
                }
                
                cell = $('<div />').addClass('cell date').html(the_date).appendTo(row);

                if(cur_date != null && cur_date.getDate() == the_date && (cur_date.getMonth() + 1) == month && cur_date.getFullYear() == year) {
                    cell.addClass('current').attr('rel', month + '/' + the_date + '/' + year).click(function(){ self.inst.selectDate($(this).attr('rel')); });
                } else {
                    cell.addClass('other-month');

                    if(this.inst.holidays[month + '/' + the_date] != null) {
                        cell.addClass('holiday');
                        cell.prop('title', this.inst.holidays[month + '/' + the_date ]);
                    }

                    if(this.inst.isLinkable(month + '/' + the_date + '/' + year)) {
                        cell.addClass('linkable');
                        cell.attr('rel', month + '/' + the_date + '/' + year).click(function(){ self.inst.selectDate($(this).attr('rel')); });
                    }
                }
            }

            var count = 1;
            var the_year = '';
            var the_month = '';

            this.inst.sel_month = this_month;
            this.inst.sel_year  = this_year;

            while(count <= month_days[this_month]) {
                for(var b = start_spaces; b < 7; b ++) {
                    if(b == 0) {
                        row = $('<div />').addClass('row').appendTo(container);
                    }

                    the_year = this_year;
                    
                    cell = $('<div />').addClass('cell date').appendTo(row);

                    if(count > month_days[this_month]) {
                        the_date  = count - month_days[this_month];
                        the_month = this_month + 2;

                        cell.addClass('other-month');

                        if(the_month > 12) {
                            the_month = 1;
                            the_year ++;
                        }
                    } else {
                        the_date  = count;
                        the_month = this_month + 1;
                    }
                    
                    cell.html(the_date);

                    if(cur_date != null && cur_date.getDate() == the_date && (cur_date.getMonth()+1) == the_month && cur_date.getFullYear() == the_year) {
                        cell.addClass('current');
                        cell.attr('rel', the_month + '/' + the_date + '/' + the_year).click(function(){ self.inst.selectDate($(this).attr('rel')); });
                    } else {
                        if(this.inst.holidays[the_month + '/' + the_date] != null) {
                            cell.addClass('holiday');
                            cell.prop('title', this.inst.holidays[the_month + '/' + the_date ]);
                        }

                        if(this.inst.isLinkable(the_month + '/' + the_date + '/' + the_year)) {
                            cell.addClass('linkable');
                            cell.attr('rel', the_month + '/' + the_date + '/' + the_year).click(function(){ self.inst.selectDate($(this).attr('rel')); });
                        }
                    }

                    count ++;
                }
                
                start_spaces   = 0;
            }

            var short_date = (date_today.getMonth() + 1) + '/' + date_today.getDate() + '/' + date_today.getFullYear();
                        
            $('<div />').addClass('bottom-bar')
                    .html('<span>Today:</span> <span>' + this.inst.determineDate(short_date) + '</span>')
                    .click(function(){ self.inst.selectDate(short_date); })
                    .appendTo(container);
            
            return container;
        }
        
        this.displayMonthYear = function(the_date) {
            var cur_date = new Date(the_date);
            
            var self = this;
            
            var container = $('<div />').addClass('month-year');
            
            var main_row = $('<div />').addClass('top-bar').click(function() { self.inst.displayMonthYear((self.inst.sel_month + 1 ) + '/1/' + self.inst.sel_year); }).appendTo(container);
            $('<span />').addClass('title').click(function(){ self.inst.changeMonthYear(false); }).html(this.month_names[this.inst.sel_month] + ' ' + this.inst.sel_year).appendTo(main_row);
                      
            main_row = $('<div />').addClass('row head').appendTo(container);
            
            $('<div />').addClass('cell').html('Months').appendTo(main_row);
            $('<div />').addClass('cell').html('Years').appendTo(main_row);
            
            main_row = $('<div />').addClass('row').appendTo(container);
            
            var month_container = $('<div />').addClass('months').appendTo($('<div />').addClass('cell').appendTo(main_row));
            
            var row, cell;
            
            for(var i = 0; i < 12; i ++) {
                if(i % 2 == 0) {
                    row = $('<div />').addClass('row').appendTo(month_container);
                }
                
                cell = $('<div />').addClass('item').attr('rel', i).click(function(){ self.inst.changeMonth($(this).attr('rel')); }).html(this.month_short_names[i]).appendTo(row);
                                
                if(i == this.inst.sel_month && cur_date.getFullYear() == this.inst.sel_year) {
                    cell.addClass('current');
                }
            }

            var year_container = $('<div />').addClass('years').appendTo($('<div />').addClass('cell').appendTo(main_row));

            for(var i = (cur_date.getFullYear() - 5); i < (cur_date.getFullYear() + 5); i ++) {
                if((i-(cur_date.getFullYear() - 5)) % 2 == 0) {
                    row = $('<div />').addClass('row').appendTo(year_container);
                }
                
                cell = $('<div />').addClass('item').attr('rel', i).click(function(){ self.inst.changeYear($(this).attr('rel')); }).html(i).appendTo(row);
                                
                if(i == this.inst.sel_year) {
                    cell.addClass('current');
                }
            }
            
            row = $('<div />').addClass('control').appendTo(year_container);
            
            $('<span />').addClass('prev-btn').click(function() { self.inst.displayMonthYear('1/1/' + (cur_date.getFullYear() - 10)); }).appendTo(row);
            $('<span />').addClass('next-btn').click(function() { self.inst.displayMonthYear('1/1/' + (cur_date.getFullYear() + 10)); }).appendTo(row);

            main_row = $('<div />').addClass('bottom-bar action').appendTo(container);
            
            $('<span />').addClass('btn red').click(function() { self.inst.changeMonthYear(true); }).html('Cancel').appendTo(main_row);
            $('<span />').html('    ').appendTo(main_row);
            $('<span />').addClass('btn').click(function() { self.inst.changeMonthYear(false); }).html('Apply').appendTo(main_row);

            return container;
        }
        
        this.inst = null;
        this.month_names = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        this.month_short_names = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        this.day_names = ['Su','Mo','Tu','We','Th','Fr','Sa'];
        this.wrapper = null;
        this.scene = null;
    }
    
    $.osc_UIFormDatePicker = new _OSC_UI_Form_DatePicker();
    
    $.fn.osc_UIFormDatePicker = function() {      
        var func = null;
        
        if(arguments.length > 0 && typeof arguments[0] == 'string') {
            func = arguments[0];
        }
        
        if(func) {
            var opts = [];
        
            for(var x = 1; x < arguments.length; x ++) {
                opts.push(arguments[x]);
            }
        } else {
            opts = arguments[0];
        }
        
        if(func && func.toLowerCase() == 'getvalue') {
            var instance = $(this).data('osc-ui-frm-datePicker');            
            return instance ? instance.getValue() : '';
        }
               
        return this.each(function() {
            var instance = $(this).data('osc-ui-frm-datePicker');
                
            if(func) {
                if(instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if(! instance) {
                    $(this).data('osc-ui-frm-datePicker', new OSC_UI_Form_DatePicker(this, opts));
                }
            }
        });
    }
    
    $(window).ready(function() {
        $('.mrk-ui-frm-datePicker').each(function() {
            var obj = $(this);
            
            var opt_buff = obj.attr('rel') ? obj.attr('rel').split(';') : [];

            var opts = {};
            
            for(var x = 0; x < opt_buff.length; x ++) {
                var opt = opt_buff[x].split(':');
                
                if(opt.length != 2) {
                    continue;
                }
                
                opt[0] = opt[0].toString().trim();
                opt[1] = opt[1].toString().trim();
                
                if(opt[0] && opt[1]) {
                    opts[opt[0]] = opt[1];
                }
            }
           
            obj.osc_UIFormDatePicker(opts);            
        });
    });
})(jQuery);