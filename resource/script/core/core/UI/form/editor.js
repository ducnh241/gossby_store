/*
http://jsfiddle.net/timdown/jwvha/527/
http://jsfiddle.net/09509v9z/
 */

(function($){        
    function OSC_Editor() {
        
        this.registerPlugin = function(plugin_key, plugin_object) {
            this._plugins[plugin_key] = plugin_object;
        };
        
        this._getParentElement = function(element, parentTag, func, breakElement, breakTag) {
            var rx  = parentTag ? new RegExp('^(' + parentTag.toUpperCase().replace(/,/g, '|') + ')$') : 0;
            var brx = breakTag  ? new RegExp('^(' + breakTag.toUpperCase().replace(/,/g, '|') + ')$')  : 0;

            return this._getParentNode(element, function(element) {
                return ((element.nodeType == 1 && ! rx) || (rx && rx.test(element.nodeName))) && (! func || func(element));
            }, breakElement, brx);
        };
        
        this._getParentNode = function(element, func, breakElement, brx) {
            while(element) {
                if(element == breakElement) {
                    return null;
                } else if(brx && brx.test(element.nodeName)) {
                    return null;
                }

                if(func(element)) {
                    return element;
                }

                element = element.parentNode;
            }

            return null;
        };
        
        this._formatFontsize = function(csssize) {            
            var unit = csssize.replace(/[0-9\.]/g, '').toLowerCase();
            var val  = parseInt(csssize.replace(/[^0-9\.]/g, ''));
            
            if(unit == 'pt') {
                val = parseInt(val * 1.333333);
            } else if(unit == 'em') {
                val = parseInt(val * 16);
            } else if(unit != 'px') {
                return val;
            }
            
            if(val <= 10) {
                return 1;
            } else if(val <= 13) {
                return 2;
            } else if(val <= 16) {
                return 3;
            } else if(val <= 19) {
                return 4;
            } else if(val <= 24) {
                return 5;
            } else if(val <= 32) {
                return 6;
            } else {
                return 7;
            }
        }
        
        this._rgbhexToColor = function(r, g, b) {
            return '#' + ($.str_pad(r, 2, 0) + $.str_pad(g, 2, 0) + $.str_pad(b, 2, 0));
        }
        
        this._IMG_ATTACH_URL = null;
        this._NULL_IMG_SRC = null;
        this._plugins = {};
    }
    
    function OSC_Editor_Item(container, options) {
        this.reInit = function() {
            this.setup(this.getContent());
        },
        
        this.setup = function(value) {	
            this._setupEditArea();
            this._setEditorEvents();
            this._initControls();

            if(value) {
                this.setContent(value);
                this.moveCaretToEnd();
            }
            
            for(var plugin_key in this.plugins) {
                this.plugins[plugin_key].setup();
            }
        }
        
        this._setupEditArea = function() {
            var editbox = $(this.container).find('iframe.mrk-editor-box')[0];
            
            if(editbox) {
                this.editbox = editbox;
            } else {
                this.editbox = document.createElement('iframe');

                this.textarea.parentNode.insertBefore(this.editbox, this.textarea.parentNode.lastChild);

                this.editbox.tabIndex = -1;
                this.editbox.allowtransparency = 'true';
                this.editbox.className = 'mrk-editor-box';
            }

            $(this.textarea).css('height', this.height + 'px');

            this.editwin = this.editbox.contentWindow;
            this.editdoc = this.editwin.document;

            this.editdoc.designMode = 'on';

            this.editdoc = this.editwin.document;

            this.editdoc.open('text/html', 'replace');
            this.editdoc.write('<!DOCTYPE html><html><head></head><body></body></html>');
            this.editdoc.close();

            $(this.editbox).data('osc-editor', this);
            
            $('<link />').attr('rel', 'stylesheet')
                .attr('type', 'text/css')
                .attr('href', OSC_TPL_CSS_BASE_URL + '/core/UI/form/editor/format.css')
                .appendTo(this.editdoc.getElementsByTagName('head')[0]);

            this.editdoc.body.contentEditable = true;

            $(this.textarea).hide();
            $(this.editbox).show();

            if($.browser.msie) {
                this.editdoc.body.style.border = '0px';
            } else {
                //this.editdoc.body.style.padding = '10px';
                this.editbox.style.border = '0px';
            }

            $(this.editdoc.body).addClass('editor-content-wrap').addClass('edit-mode');

            $(this.editbox).css('width', '100%');
            $(this.editbox).css('display', 'block');

            this.setEditorHeight($(this.textarea).height());
        };
        
        this.getCaretOffset = function(outer_editor) {
            this.checkFocus();
            
            var open_tag = '<span class="mrk-caret">';
            var close_tag = '</span>';
            
            var text = open_tag + this._makeMarker() + close_tag;
                
            this.insertText(text, $.mb_strlen(open_tag), $.mb_strlen(close_tag));
            
            var focus_elm = $(this._getFocusElement());
            
            var offset = focus_elm.offset();
            
            focus_elm.remove();
            
            this.insertText('', 0, 0, true);  
            
            if(outer_editor) {
                offset.left -= $(this.editwin).scrollLeft();
                offset.top -= $(this.editwin).scrollTop();
                
                var editor_offset = $(this.editbox).offset();
                
                offset.left += editor_offset.left;
                offset.top += editor_offset.top;
            }
            
            return offset;
        };
        
        this.autoResize = function() {
            var resize_height = $.browser.msie ? this.editdoc.body.scrollHeight : ($.browser.webkit && this.editdoc.body.clientHeight === 0 ? 0 : this.editdoc.body.offsetHeight);
         
            if (resize_height < this.min_height) {
                resize_height = this.min_height;
            }

            if (this.max_height && resize_height > this.max_height) {
                resize_height = this.max_height;
                
                this.editdoc.body.style.overflowY = "auto";
                this.editdoc.documentElement.style.overflowY = "auto";
            } else {
                this.editdoc.body.style.overflowY = "hidden";
                this.editdoc.documentElement.style.overflowY = "hidden";
                
                this.editdoc.body.scrollTop = 0;
            }

            if (resize_height !== this.height) {
                var delta_size = resize_height - this.height;
                
                this.setEditorHeight(resize_height);

                if ($.browser.webkit && delta_size < 0) {
                    this.autoResize();
                    return;
                }
            }
        };
        
        this.setEditorHeight = function(height, skip_backup) {            
            height = parseInt(height);

            if(isNaN(height) || height < 20) {
                height = 20;
            }

            $(this.editbox).css('height', height + 'px');
            $(this.textarea).css('height', height + 'px');

            if(! skip_backup) {
                this.height = height;
            }
            
            return true;
        };
        
        this._setEditorEvents = function() {            
            $(this.editdoc).unbind('mouseup', this.event_hooks.docMouseUp).bind('mouseup', this.event_hooks.docMouseUp);
            $(this.editdoc).unbind('keyup', this.event_hooks.docKeyUp).bind('keyup', this.event_hooks.docKeyUp);
            
            $(this.editwin).unbind('focus', this.event_hooks.winFocus).bind('focus', this.event_hooks.winFocus);
            $(this.editwin).unbind('blur', this.event_hooks.winBlur).bind('blur', this.event_hooks.winBlur);
            
            $(this.editdoc).unbind('keydown', this.event_hooks.docKeyDown).bind('keydown', this.event_hooks.docKeyDown);
            $(this.editdoc.body).unbind('paste', this.event_hooks.paste).bind('paste', this.event_hooks.paste);
        };
	
        this._initControls = function() {   
            var self = this;
            
            this.control_bar.find('.mrk-editor-cmd').each(function() {
                var command = $(this); 
                
                if(command.attr('data-cmd')) {
                    self._initCommandButton(command[0]);
                } else if(command.hasClass('mrk-editor-popup')) {
                    self._initPopupButton(command[0]);
                }
            });
        };
        
        this._initCommandButton = function(element) {
            var obj = $(element);
            
            var cmd = obj.attr('data-cmd');
            
            obj.data('osc-editor', this).attr('cmd', cmd).attr('state', 0).attr('mode', 'normal');
            
            this.e_buttons[cmd] = obj;
            
            obj.unbind('click', this.event_handler.commandButtonMouseEventHook).bind('click', this.event_handler.commandButtonMouseEventHook);
            obj.unbind('mousedown', this.event_handler.commandButtonMouseEventHook).bind('mousedown', this.event_handler.commandButtonMouseEventHook);
            obj.unbind('mouseover', this.event_handler.commandButtonMouseEventHook).bind('mouseover', this.event_handler.commandButtonMouseEventHook);
            obj.unbind('mouseout', this.event_handler.commandButtonMouseEventHook).bind('mouseout', this.event_handler.commandButtonMouseEventHook);
        };
	
        this._initPopupButton = function(element) {
            var self = this;
            
            var obj = $(element);
            
            var cmd = obj.attr('editor-popup');
            
            obj.attr('popup-cmd', cmd);
        };
        
        this._markElement = function() {
            var elmTags = ['table','tr','td'];

            for(var x = 0; x < elmTags.length; x ++) {
                var elements = this.editdoc.getElementsByTagName(elmTags[x]);

                for(var y = 0; y < elements.length; y ++) {
                    elements[y].className = 'editor-tbl-wrapper';
                }
            }
        };
        
        this.toggleControlBar = function() {
            this.renderer.toggleControlBar();
        }
        
        this.focus = function() {
            this.checkFocus();
            
            var self = this;
            
            setTimeout(function(){ self.autoResize(); }, 100);
        }
        
        this.checkFocus = function() {            
            $.osc_editor.CUR_INST = this;

            if(this.html){
                return false;
            }

            if(! this.has_focus) {
                this.editwin.focus();
                this._restoreSelection();
            }
        }
        
        this._getFocusElement = function() {
            var range = this._getRange();
            var focus_element = null;

            if($.browser.msie) {
                focus_element = range.item ? range.item(0) : range.parentElement();
            } else {                
                focus_element = range.commonAncestorContainer;

                if(! range.collapsed) {
                    if(range.startContainer == range.endContainer) {
                        if(range.startOffset - range.endOffset < 2) {
                            if(range.startContainer.hasChildNodes()) {
                                focus_element = range.startContainer.childNodes[range.startOffset];
                            }
                        }
                    }
                }

                focus_element = $.osc_editor._getParentElement(focus_element);
            }

            return focus_element;
        }
        
        this._saveSelection = function() {
            if(this.editwin.getSelection) {
                this.cur_sel = this.editwin.getSelection();
            } else if(this.editdoc.selection) {
                this.cur_sel = this.editdoc.selection.createRange().getBookmark();
            } else {
                this.cur_sel = null;
            }
        }
        
        this._restoreSelection = function() {		
            if(this.cur_sel == null) {
                if(! this.getSel()) {                
                    this.insertText('', 0, 0, true);
                }
                
                return false;
            }
            
            var range;

            if(this.editwin.getSelection) {
                if(typeof this.cur_sel.getRangeAt != 'undefined') {
                    range = this.cur_sel.getRangeAt(0);
                } else if(typeof this.cur_sel.baseNode != 'undefined') {
                    range = this.editdoc.createRange();
                    range.setStart(this.cur_sel.baseNode, this.cur_sel.baseOffset);
                    range.setEnd(this.cur_sel.extentNode,this.cur_sel.extentOffset);

                    if(range.collapsed) {
                        range.setStart(this.cur_sel.extentNode, this.cur_sel.extentOffset);
                        range.setEnd(this.cur_sel.baseNode, this.cur_sel.baseOffset);
                    }
                }

                var rangecopy = range.cloneRange();

                this.cur_sel.removeAllRanges();
                this.cur_sel.addRange(rangecopy);
            } else if(this.editdoc.selection && this.editdoc.body.createTextRange) {
                var range = this.editdoc.body.createTextRange();
                range.moveToBookmark(this.cur_sel);
                range.select();
            }

            this.cur_sel = null;
        }
        
        this._getRange = function() {
            if($.browser.msie) {
                var range = this.editdoc.selection.createRange();
            } else {                
                var sel = this.editwin.getSelection();
                var range = sel && sel.rangeCount > 0 ? sel.getRangeAt(0) : this.editdoc.createRange();
            }

            return range;
        }    
        
        this.getSel = function() {
            if($.browser.msie) {
                var range = this.editdoc.selection.createRange();

                if(range.htmlText && range.text) {
                    return range.htmlText;
                } else {
                    var do_not_steal_this_code_html = '';

                    for(var i = 0; i < range.length; i++) {
                        do_not_steal_this_code_html += range.item(i).outerHTML;
                    }

                    return do_not_steal_this_code_html;
                }
            } else {
                var sel = this.editwin.getSelection();

                this.checkFocus();

                var range = sel ? sel.getRangeAt(0) : this.editdoc.createRange();

                return this.read_nodes(range.cloneContents(), false);
            }
        }
        
        this._setFontContext = function() {		
            var fs = false;
            
            var elm = this._getFocusElement();
            
            if(elm !== null) {
                var elm_obj = $(elm);
                
                while(elm_obj[0].nodeName != 'BODY') {
                    if(elm_obj[0].nodeName == 'FONT') {
                        fs = elm_obj.attr('face');
                    }

                    if(! fs && elm_obj.css('font-family') != '')   {
                        fs = elm_obj.css('font-family').split(',')[0];
                    }

                    if(fs) {
                        break;
                    }

                    elm_obj = elm_obj.parent();
                }
            }

            if(! fs) {
                var targetrule = false;
                var myrules = this.editdoc.styleSheets[0].cssRules ? this.editdoc.styleSheets[0].cssRules : this.editdoc.styleSheets[0].rules;

                for(var x = 0; x < myrules.length; x ++) {
                    if(myrules[x].selectorText.toLowerCase() == ".editor-content-wrap") {
                        targetrule = myrules[x];
                        break;
                    }
                }

                if(targetrule) {
                    fs = $.trim(targetrule.style.fontFamily.split(',')[0]);
                }
            }
            
            if(fs) {
                fs = fs.toLowerCase();

                this.renderer._setFontPreview(fs);
            }
        }

        this._setSizeContext = function(ss) {
            var fs = false;
            
            var elm = this._getFocusElement();
            
            if(elm !== null) {
                var elm_obj = $(elm);
                
                while(elm_obj[0].nodeName != 'BODY') {
                    if(elm_obj[0].nodeName == 'FONT') {
                        fs = elm_obj.attr('size');
                    }

                    if(! fs && elm_obj.css('font-size') != '')   {
                        fs = elm_obj.css('font-size');
                    }

                    if(fs) {
                        break;
                    }

                    elm_obj = elm_obj.parent();
                }
            }

            if(! fs) {
                var targetrule = false;
                var myrules = this.editdoc.styleSheets[0].cssRules ? this.editdoc.styleSheets[0].cssRules : this.editdoc.styleSheets[0].rules;

                for(var x = 0; x < myrules.length; x ++) {
                    if(myrules[x].selectorText.toLowerCase() == ".editor-content-wrap") {
                        targetrule = myrules[x];
                        break;
                    }
                }

                if(targetrule) {
                    fs = targetrule.style.fontSize;
                }
            }
		
            if(fs) {
                if(! /^[1-7]$/.test(fs)) {
                    fs = $.osc_editor._formatFontsize(fs);
                }

                this.renderer._setSizePreview(fs);
            }
        }
        
        this._setColorContext = function(cs) {
            if(typeof cs == 'undefined') {
                cs = this.editdoc.queryCommandValue('forecolor');
            }

            this.renderer.setColorPreview(this._rgbToColor(cs));
        }
        
        this._setBackContext = function() {            
            var range = this._getRange();
			
            if($.browser.msie) {                
                var elm = range.item ? range.item(0) : range.parentElement();
            } else {
                var elm = range.commonAncestorContainer;
            }
		
            var hexVal = '#ffffff';
			
            elm = $.osc_editor._getParentElement(elm);
						
            do {
                if(elm.tagName == 'BODY') {
                    break;
                }

                if(elm != null && elm.style.backgroundColor != '') {
                    var hexVal = elm.style.backgroundColor;

                    if(! $.browser.msie) {
                        hexVal = this._rgbToColor(hexVal);
                    }

                    break;
                }

                elm = elm.parentNode;
            } while(elm != null);
				
            this.renderer.setBackgroundPreview(hexVal);
        };
        
        this.registerContextCallback = function(key, callback) {
            this._context_callback[key] = callback;
            return this;
        };
        
        this._setContext = function(e, cmd) {
            if(this.html) {
                return false;
            }
            
            var self = this;
            
            var focus_element = this._getFocusElement();            
            var elm = focus_element;
		
            this.path_scene.innerHTML = '';
		
            while(elm && elm.tagName != 'BODY') {
                var node = $('<span />');

                node.addClass('entry')
                        .html(elm.tagName.toLowerCase())
                        .click(function() {
                            self.checkFocus();
                            self.sel.selectNode($(this).data('osc-editor-path-elt'), false, false);
                        })
                        .data('osc-editor-path-elt', elm);

                $(this.path_scene.firstChild).before(node);

                this.path_scene.insertBefore(document.createTextNode(' » '), this.path_scene.firstChild);
                
                elm = elm.parentNode;
            }

            if(this.path_scene.childNodes.length > 0) {
                $(this.path_scene.firstChild).remove();
            }

            this._anchorContext(focus_element);
            this._setFontContext();

            for(var i =0; i < this.renderer.context_controls.length; i ++) {
                var obj = this._cmd_elms[this.renderer.context_controls[i]];

                if(obj) {
                    var state = this.editdoc.queryCommandState(this.renderer.context_controls[i]) ? 1 : 0;

                    if(obj.attr('state') != state) {
                        obj.attr('state', state);
                        this._buttonContext(obj[0], obj.attr('cmd') == cmd ? 'mouseover' : 'mouseout');
                    }
                }
            }
            
            focus_element = $(focus_element);
            
            for(var i in this._context_callback) {
                this._context_callback[i](e, this, focus_element);
            }
            
            this.autoResize();
        };
	
        this._anchorContext = function(focus_element) {	
            var obj = this._cmd_elms.link;

            if(! obj) {
                return false;
            }

            focus_element = $.osc_editor._getParentElement(focus_element, 'A');

            obj.attr('state', focus_element != null ? 1 : 0);

            this._buttonContext(obj[0], 'mouseout');
	
            obj = this._cmd_elms.unlink;

            if(focus_element != null) {
                obj.attr('state', 1).attr('activated', 1);
            } else {
                obj.attr('state', 0).attr('activated', 0);
            }

            this._buttonContext(obj[0], 'mouseout');
        };
        
        this._buttonContext = function(element, state, controltype) {
            var obj = $(element);
          
            var cmd = obj.attr('cmd');

            if(typeof controltype == 'undefined') {
                controltype = 'button';
            }

            if(obj.attr('state') == 1) {
                if(state == 'mouseover' || state == 'mousedown' || state == 'mouseup') {
                    this._setControlStyle(element, controltype, 'selected');
                } else if(state == 'mouseout') {
                    this._setControlStyle(element, 'button', 'selected');
                }
            } else {
                if(state == 'mouseout') {
                    this._setControlStyle(element, controltype, 'normal');
                } else if(state == 'mousedown') {
                    this._setControlStyle(element, controltype, 'selected');
                } else if(state == 'mouseup' || state == 'mouseover') {
                    this._setControlStyle(element, controltype, 'hover');
                }
            }
        };
        
        this._setControlStyle = function(element, controltype, mode) {
            var obj = $(element);
      
            if(obj.attr('mode') != mode) {
                obj.attr('mode', mode);

                if(obj.attr('data-cmd')) {
                    if(mode == 'selected') {
                        obj.addClass('affected');
                    } else {
                        obj.removeClass('affected');
                    }
                }
            }
        };
        
        this.disable = function() {
            $(this.editbox).parent().find('.disable').addClass('active');
        };
        
        this.enable = function() {
            $(this.editbox).parent().find('.disable').removeClass('active');
        };
        
        this.setContent = function(text, only_set_textarea) {
            if(typeof text != 'string') {
                text = '';
            } else {
                text = this.trim(text);
                
                if(text) {
                    text += '&nbsp;';
                }
            }
                
            this.textarea.value = text;
            $(this.textarea).trigger('change');
            $(this.container).trigger('change');

            if(! this.html && ! only_set_textarea) {
                this.editdoc.body.innerHTML = text;
                this._markElement();
            }
            
            $('.mrk-null-img', this.editdoc.body).removeClass('mrk-null-img').attr('src', $.osc_editor._NULL_IMG_SRC);
            $('.mrk-embedcode', this.editdoc.body).each(function() {
                var elm = $(this);
                var embed_data = $.base64_decode(elm.attr('embeddata'));
                eval('embed_data = ' + embed_data);
                
                var url = '';
                
                if(embed_data.type == 'iframe') {
                    url = embed_data.src;
                } else {
                    url = embed_data.params.movie;
                }
                                        
                var youtube_regex = [
                    [/^(https?:)?\/\/(www\.)?youtu\.be\/([^\/?&#]+)([\/?&#].*)?/i, 3],
                    [/^(https?:)?\/\/(www\.)?(youtube-nocookie|youtube)\.com\/.*[?&]v=([^\/?&#]+)([\/?&#].*)?/i, 4],
                    [/^(https?:)?\/\/(www\.)?(youtube-nocookie|youtube)\.com(\/.+)?\/(v|embed)\/([^\/?&#]+)([\/?&#].*)?/i, 6]
                ];
                
                var youtube_code = '';
                
                for(var i = 0; i < youtube_regex.length; i ++) {
                    if(youtube_regex[i][0].test(url)) {
                        eval("youtube_code = url.replace(youtube_regex[i][0], '$" + youtube_regex[i][1] + "')");
                    }
                }
                
                if(! youtube_code) {
                    return false;
                }
                
                elm.attr('src', 'http://i2.ytimg.com/vi/' + youtube_code + '/hqdefault.jpg');
            });
            
            this.autoResize();
            
            return this;
        };
        
        this.trimString = function(text) {     
            text = this.trimStringLeft(text);  
            text = this.trimStringRight(text);
            
            return text;
        };
        
        this.trimStringLeft = function(text) {
            return text.replace(/^\s*(?:(?:&nbsp;)\s*)*/g, '');
        };
        
        this.trimStringRight = function(text) {
            return text.replace(/\s*(?:(?:&nbsp;)\s*)*$/g, '');
        };
        
        this.trimLeft = function(elm) {
            var self = this;
                        
            while(true) {
                if(elm.childNodes.length < 1) {
                    break;
                }
                
                var child_elm = elm.childNodes[0];
                
                if(this._trim(child_elm, function(elm){ return self.trimLeft(elm); }, /^\s*(?:(?:&nbsp;)\s*)*/g)) {
                    break;
                }
                                
                elm.removeChild(child_elm);
            }
            
            return elm.childNodes.length > 0;
        };
        
        this.trimRight = function(elm) {            
            var self = this;
                        
            while(true) {
                if(elm.childNodes.length < 1) {
                    break;
                }
                
                var child_elm = elm.childNodes[elm.childNodes.length-1];
                
                if(this._trim(child_elm, function(elm){ return self.trimRight(elm); }, /\s*(?:(?:&nbsp;)\s*)*$/g)) {
                    break;
                }
                                
                elm.removeChild(child_elm);
            }
            
            return elm.childNodes.length > 0;
        };
        
        this._trim = function(elm, trim_callback, text_regex) {
            var tag_name = elm.tagName;

            if(! tag_name) {
                elm.nodeValue = elm.nodeValue.replace(text_regex, '');

                if(! elm.nodeValue) {
                    return false;
                }
            } else {
                tag_name = tag_name.toLowerCase();

                if(tag_name == 'br') {
                    return false;
                }

                if(tag_name != 'img' && tag_name != 'hr') {
                    if(! trim_callback(elm)) {
                        return false;
                    }
                }
            }

            return true;
        };
        
        this.trim = function(content) {
            var wrap = $('<div />').html(content);
            
            this.trimLeft(wrap[0]);
            this.trimRight(wrap[0]);
            
            content = this.trimString(wrap.html());
                                    
            return content;
        };
        
        this.getContent = function() {
            var content = this.html ? this.textarea.value : this.editdoc.body.innerHTML;
            
            content = this.trim(content);
                        
            return content;
        },
                 
        this._processPastedContent = function(e, pasted_content) {
            this.checkFocus();
            
            var wrap = $('<div />').html(pasted_content);
            
            for(var x = 1; x <= 7; x ++) {
                wrap.find('h' + x).each(function() {                    
                    $(this).before($('<div />').html($(this).html())).remove();
                });
            }
            
            wrap.find('*').removeAttr('class');
            
            var html = wrap.html();
            this.insertText(html, $.mb_strlen(html), 0, true);
            this._setContext(e);
        }
        
        this._execCommand = function(e, cmd, arg) {         
            if(this.html && cmd != 'htmlMode' && cmd != 'preview') {
                return false;
            }
			
            this.checkFocus();
          
            if(cmd.substr(0, 4) == 'wrap') {
                var ret = this.wrapTags(cmd.substr(6), cmd.substr( 4, 1 ) == '1' ? true : false);
            } else if (this['_' + cmd + 'Command']) {
                var ret = this['_' + cmd + 'Command'](e);
            } else {
                try {
                    var ret = this._execStandardCommand(cmd, false, (typeof arg == 'undefined' ? true : arg));
                } catch(e) {
                    var ret = false;
                }
            }

            this._setContext(e, cmd);
            
            return ret;
        }
        
        this._execStandardCommand = function(cmd, dialog, argument) {
            this.editdoc.execCommand(cmd, (typeof dialog == 'undefined' ? false : dialog), (typeof argument == 'undefined' ? true : argument));
        }
        
        this._removeformatCommand = function(e) {
            this._execStandardCommand('RemoveFormat');
            
            for(var x = 1; x <= 7; x ++) {
                $('h' + x, this.editdoc).each(function() {                    
                    $(this).before($('<div />').html($(this).html())).remove();
                });
            }
            
            $('*', this.editdoc.body).filter(function(){ var class_name = $(this).attr('class'); return ! class_name || class_name.lastIndexOf('mrk-') < 0; }).removeAttr('class').removeAttr('style');
        }
        
        this._justifyleftCommand = function(e) {
            this._setAlignCommand('left');
        }

        this._justifyrightCommand = function(e) {
            this._setAlignCommand('right');
        }

        this._justifycenterCommand = function(e) {
            this._setAlignCommand('center');
        }

        this._justifyfullCommand = function(e) {
            this._setAlignCommand('justify');
        }
        
        this._setAlignCommand = function(align) {
            var sel = this.getSel();

            if(sel === false || sel == '') {
                var range = this._getRange();

                if($.browser.msie) {                    
                    var element = range.item ? range.item(0) : range.parentElement();
                } else {
                    var element = range.commonAncestorContainer;
                }

                if((element = $.osc_editor._getParentElement(element, 'div,td')) != null) {
                    element.setAttribute("align", align);
                }
            } else {
                var sel = new String(sel);

                var open_tag = '<div align="' + align + '">';
                var close_tag = '</div>';
                var text = open_tag + sel + close_tag;

                this.insertText(text, $.mb_strlen(open_tag), $.mb_strlen(close_tag));
            }
        }
        
        this._ruleCommand = function(e) {
            this.checkFocus();
            var html = '<hr class="editor-horizon-rule" />';
            this.insertText(html, $.mb_strlen(html), 0, true);
        }
        
        this._quoteCommand = function(e) {
            this.checkFocus();

            var sel = this.getSel();

            element = $.osc_editor._getParentElement(this._getFocusElement(), 'DIV');

            if(sel != null && sel != '') {
                var text = sel;
            } else {
                var text = '';
            }
            
            if(element && $(element).hasClass('editor-quote')) {
                $(element).removeClass('editor-quote');
                return true;
            }

            var openTag = '<div class="editor-quote">';
            var closeTag = '</div>';

            text = openTag + text + this._makeMarker() + closeTag;

            this.insertText(text, $.mb_strlen(openTag), $.mb_strlen(closeTag));
        };
        
        this.moveCaretToEnd = function() {
            this.checkFocus();
            var marker = $('<span />').appendTo(this.editdoc.body);
            this.sel.selectNode(marker[0], false);
            marker.remove();
        };
        
        this._makeMarker = function() {
            return '<span id="osc-marker">&#xFEFF;</span>';
        };
        
        this._imglinkCommand = function(e) {            
            this._saveSelection();
            this.renderer.renderImgForm(e);
        };
        
        this._embedcodeCommand = function(e, code) {   
            if(! code) {
                this._saveSelection();
                this.renderer.renderEmbedCodeForm(e);
                return;
            }
            
            var swap = $('<div />').html(code);
            
            var youtube_code = '';
                                        
            var youtube_regex = [
                [/^(https?:)?\/\/(www\.)?youtu\.be\/([^\/?&#]+)([\/?&#].*)?/i, 3],
                [/^(https?:)?\/\/(www\.)?(youtube-nocookie|youtube)\.com\/.*[?&]v=([^\/?&#]+)([\/?&#].*)?/i, 4],
                [/^(https?:)?\/\/(www\.)?(youtube-nocookie|youtube)\.com(\/.+)?\/(v|embed)\/([^\/?&#]+)([\/?&#].*)?/i, 6]
            ];
            
            if(swap.find('iframe')[0]) {
                var url = swap.find('iframe').attr('src');
                
                if(! youtube_regex[2][0].test(url)) {
                    alert("Mã nhúng iframe không được hỗ trợ cho trang này");
                    return false;
                }
                                
                eval("youtube_code = params.movie.replace(youtube_regex[2][0], '$" + youtube_regex[2][1] + "')");
                                
                code = {type : 'iframe', src : url};
            } else if(swap.find('object')[0] || swap.find('embed')[0]) {
                var obj = swap.find('object');
                var embed = null;
                var param_elms = null;
                
                if(obj[0]) {
                    embed = obj.find('embed');
                    param_elms = obj.find('param');
                } else {
                    embed = swap.find('embed');
                }
                
                var params_map = ['wmode', 'movie', 'allowfullscreen', 'allowscriptaccess', 'quality', 'flashvars'];
                var params = {};
                
                for(var i = 0; i < params_map.length; i ++) {
                    var param_val = null;
                    var param_key = params_map[i];
                    
                    if(param_elms && param_elms[0]) {
                        param_elms.each(function() {
                            if($(this).attr('name').toLowerCase() == param_key) {
                                param_val = $(this).attr('value');
                                return false;
                            }
                        });
                    }
                    
                    if(! param_val && embed[0]) {
                        if(param_key == 'movie') {
                            param_key = 'src';
                        }
                        
                        param_val = embed.attr(param_key);
                    }
                    
                    if(param_val) {
                        params[params_map[i]] = param_val;
                    }
                }
                
                if(! params.movie) {
                    alert("Mã nhúng không hợp lệ");
                    return false;
                }
                
                if(youtube_regex[1][0].test(params.movie)) {
                    eval("youtube_code = params.movie.replace(youtube_regex[1][0], '$" + youtube_regex[1][1] + "')");
                }
                    
                youtube_code = params.movie.replace(/^(https?:)?\/\/(www\.)?(youtube-nocookie|youtube)\.com(\/.+)?\/(v|embed)\/([^\/?&#]+)([\/?&#].*)?/i, '$6');
                
                code = {type : 'object', params : params};
            } else {
                var url = code.trim();
                
                code = '';
                                
                for(var i = 0; i < youtube_regex.length; i ++) {
                    if(youtube_regex[i][0].test(url)) {
                        eval("code = url.replace(youtube_regex[i][0], '$" + youtube_regex[i][1] + "')");
                    }
                }
                
                if(! code) {
                    alert("Mã nhúng không hợp lệ");
                    return false;
                }
                
                youtube_code = code;
                
                code = {type : 'iframe', src : '//www.youtube.com/embed/' + code};
            }
            
            this.checkFocus();
            
            var text = '<img src="' + (youtube_code ? 'http://i2.ytimg.com/vi/' + youtube_code + '/hqdefault.jpg' : $.osc_editor._NULL_IMG_SRC) + '" id="osc-editor-mrk-img" class="embed-mark mrk-embedcode" />';

            this.insertText(text, $.mb_strlen(text), 0, true);
            
            var img = $('#osc-editor-mrk-img', this.editdoc.body);
            
            img.removeAttr('id').attr('embeddata', $.base64_encode($.json_encode(code)));
            
            img.click(function() {
                self.sel.selectNode(this, false);
            });
            
            return true;
        };
        
        this._imgbrowseCommand = function(e) {
            
        };
        
        this._renderImgHolder = function(e) {
            this.checkFocus();
            
            var self = this;
            
            if(this.img_limit > 0) {
                if($('img:not([class]),img.mrk-img-holder', this.editdoc.body).length >= this.img_limit) {
                    alert('Bạn đã dùng quá số lượng ảnh cho mỗi bài viết [' + this.img_limit + ' ảnh]!');
                    return;
                }
            }

            var text = '<img src="' + $.osc_editor._NULL_IMG_SRC + '" id="osc-editor-mrk-img" class="loading mrk-loading" />';

            this.insertText(text, $.mb_strlen(text), 0, true);
            
            var img = $('#osc-editor-mrk-img', this.editdoc.body);
            
            img.removeAttr('id');
            
            img.click(function() {
                self.sel.selectNode(this, false);
            });
            
            return img;
        };
        
        this._fontCommand = function(e, font) {
            if(! font) {
                this.renderer.renderFontForm(e);
                return;
            }            
            
            this.checkFocus();
            this._execStandardCommand('FontName', false, font);
        };
        
        this._sizeCommand = function(e, size) {
            if(! size) {
                this.renderer.renderSizeForm(e);
                return;
            }    
            
            this.checkFocus();      
            			/*	
            var sel = this.getSel();
		
            if(sel == false || sel == '') {
                var elm = $.osc_editor._getParentElement(this._getFocusElement(), '', '', '', 'body');
						
                if(elm != null) {
                    elm.style.fontSize = size;
							
                    if(elm.nodeName == 'FONT') {
                        elm.removeAttribute('size');
                    }
							
                    return true;
                }
						
                this.sel.selectNode(this._getFocusElement(), false, true);

                sel = this.getSel();
						
                if(sel == false || sel == '') {
                    return false;
                }
            } else {
                var elm = $.osc_editor._getParentElement(this._getFocusElement(), '', function(elm){
                    return elm.style.fontSize || (elm.nodeName == 'FONT' && elm.size);
                });

                if(elm && (elm.innerHTML == sel || elm.outerHTML == sel)) {
                    elm.style.fontSize = size;
							
                    if(elm.nodeName == 'FONT') {
                        elm.removeAttribute('size');
                    }
							
                    return true;
                }
            }
					
            var elm_id = 'elm-marker.' + (new Date()).getTime();
						
            var openTag  = '<span style="font-size: ' + size + '" id="' + elm_id + '">';
            var closeTag = '</span>';
            var text     = openTag + sel + closeTag;
				
            this.insertText(text, $.mb_strlen(openTag), $.mb_strlen(closeTag));
					
            var elm = $('#' + elm_id, this.editdoc);
					
            this.sel.selectNode(elm[0], false, true);
					
            elm.removeAttr('id');
            
            return true;*/
            this._execStandardCommand('FontSize', false, $.osc_editor._formatFontsize(size));
        };
        
        this._colorswatchesCommand = function(e) {
            this.renderer.renderSwatchesForm(e);
        };   
        
        this._colorCommand = function(e, hex_val) {
            this.checkFocus();
            this._execStandardCommand('forecolor', false, hex_val);
        };
        
        this._backCommand = function(e, hex_val) {
            var sel = this.getSel();

            if(sel === false || sel == '') {
                var range = this._getRange();
				
                if($.browser.msie) {
                    var elm = range.item ? range.item(0) : range.parentElement();            
                } else {
                    var elm = range.commonAncestorContainer;
                }
							
                if((elm = $.osc_editor._getParentElement(elm, 'span')) != null) {
                    elm.style.backgroundColor = '#' + hex_val;
                }
            } else {
                sel = new String(sel);
			
                var openTag  = '<span style="background-color: #' + hex_val + '">';
                var closeTag = '</span>';
                var text     = openTag + sel + closeTag;

                this.insertText(text, $.mb_strlen(openTag), $.mb_strlen(closeTag));
            }
        };
        
        this._linkCommand = function(e, data) {			
            var params = {};

            if(! data) {
                var text_locked = false;

                params = {
                    href   : '',
                    text   : ''
                };

                var element = $.osc_editor._getParentElement(this._getFocusElement(), 'A');

                if(element != null) {
                    params.text = $.strip_tags(element.innerHTML);
                    params.href = element.href ? element.href : '';
                }

                this._saveSelection();

                this.renderer.renderAnchorForm(e, params);

                return false;
            }

            this.checkFocus();

            var sel = this.getSel();

            element = $.osc_editor._getParentElement(this._getFocusElement(), 'A');

            if(element == null && (sel == null || sel == '') && data.href == '') {
                this._unlinkCommand(e);
                return false;
            }

            if(sel != null && sel != '') {
                data.title = sel;
            } else if(data.title == '') {
                data.title = data.href;
            }

            data.target = '_blank';

            var buff = ['a'];

            for(var x in data) {
                if(element != null) {
                    if(data[x] !== false) {
                        element.setAttribute(x, data[x]);
                    } else {
                        element.removeAttribute(x);
                    }

                    continue;
                }

                if(data[x] !== false) {
                    buff.push(x + '="' + data[x] + '"');
                }
            }

            if(element != null) {
                if(data.title) {
                    element.innerHTML = data.title;
                }
                
                return true;
            }

            var openTag = '<' + buff.join(' ') + '>';
            var closeTag = '</a>';

            var text = openTag + data.title + closeTag;

            this.insertText(text, $.mb_strlen(openTag), $.mb_strlen(closeTag));
        };

        this._unlinkCommand = function(e) {
            this.checkFocus();

            var element = this._getFocusElement();

            var sel = this.sel.getSel();

            if(! $.browser.msie && sel.isCollapsed) {
                element = $.osc_editor._getParentElement(element, 'A');

                if(element) {
                    this.sel.selectNode(element, false);
                }
            }

            this._execStandardCommand('unlink');

            $.browser.mozilla && sel.collapseToEnd();
        };
        
        this.insertText = function(text, movestart, moveend, mark_carret, only_insert) {
            if(mark_carret) {
                text += this._makeMarker();
            }
            
            this.checkFocus();
           
            if($.browser.msie) {
                if(typeof this.editdoc.selection != 'undefined' && this.editdoc.selection.type != 'Text' && this.editdoc.selection.type != 'None') {
                    movestart = false;
                    this.editdoc.selection.clear();
                }

                var sel = this.editdoc.selection.createRange();

                sel.pasteHTML(text);

                if(text.indexOf('\n') == -1) {
                    if(typeof movestart != 'undefined') {
                        sel.moveStart('character', - $.mb_strlen(text) + movestart);
                        sel.moveEnd('character', - moveend);
                    } else if(movestart != false) {
                        sel.moveStart('character', - $.mb_strlen(text));
                    }
                }
            } else {
                var fragment = this.editdoc.createDocumentFragment();
                var holder   = this.editdoc.createElement('span');

                holder.innerHTML = text;

                while(holder.firstChild) {
                    fragment.appendChild(holder.firstChild);
                }

                this.insert_node_at_sel(fragment);
            }
            
            if(! only_insert) {
                this.moveCaretToMarker();
            }
            
            this.autoResize();
        };
        
        this.moveCaretToMarker = function(skip_remove) {
            var marker = $('#osc-marker', this.editdoc);
            
            if(marker[0]) {
                this.sel.selectNode(marker[0], false, false);     
                
                if(! skip_remove) {                    
                    this.editdoc.execCommand('Delete', false, null);
                }
            }
            
            return this;
        };
                
        if($.browser.msie){
            this._rgbToColor = function(forecolor) {
                return $.osc_editor._rgbhexToColor((forecolor & 0xFF).toString(16), ((forecolor >> 8) & 0xFF).toString(16), ((forecolor >> 16) & 0xFF).toString(16));
            };
        } else {
            this._rgbToColor = function(forecolor) {
                if(forecolor == '' || forecolor == null) {
                    forecolor = window.getComputedStyle(this.editdoc.body, null).getPropertyValue('color');
                }

                if(forecolor.toLowerCase().indexOf('rgb') == 0) {
                    var matches = forecolor.match(/^rgb\s*\(([0-9]+),\s*([0-9]+),\s*([0-9]+)\)$/);

                    if(matches) {
                        return $.osc_editor._rgbhexToColor((matches[1] & 0xFF).toString(16), (matches[2] & 0xFF).toString(16), (matches[3] & 0xFF).toString(16));
                    } else {
                        return this._rgbToColor(null);
                    }
                } else {
                    return forecolor;
                }
            };
            
            this.add_range = function(node) {
                this.checkFocus();

                var sel = this.editwin.getSelection();
                var range = this.editdoc.createRange();

                range.selectNodeContents(node);

                sel.removeAllRanges();
                sel.addRange(range);
            };
            
            this.read_nodes = function(root, toptag) {
                var html      = "";
                var moz_check = /_moz/i;

                switch(root.nodeType) {
                    case Node.ELEMENT_NODE:
                    case Node.DOCUMENT_FRAGMENT_NODE:
                        var closed;

                        if(toptag) {
                            closed = ! root.hasChildNodes();
                            html   = '<' + root.tagName.toLowerCase();

                            var attr = root.attributes;

                            for(var i = 0; i < attr.length; ++i) {
                                var a = attr.item(i);

                                if(! a.specified || a.name.match(moz_check) || a.value.match(moz_check)) {
                                    continue;
                                }

                                html += " " + a.name.toLowerCase() + '="' + a.value + '"';
                            }

                            html += closed ? " />" : ">";
                        }

                        for(var i = root.firstChild; i; i = i.nextSibling) {
                            html += this.read_nodes(i, true);
                        }

                        if(toptag && ! closed) {
                            html += "</" + root.tagName.toLowerCase() + ">";
                        }

                        break;
                    case Node.TEXT_NODE:
                        html = $.htmlspecialchars(root.data);
                        break;
                }

                return html;
            };
            
            this.insert_node_at_sel = function(text) {
                this.checkFocus();

                var sel = this.editwin.getSelection();
                var range = sel ? sel.getRangeAt(0) : this.editdoc.createRange();

                sel.removeAllRanges();

                range.deleteContents();

                var node = range.startContainer;
                var pos  = range.startOffset;

                switch(node.nodeType) {
                    case Node.ELEMENT_NODE:
                        if(text.nodeType == Node.DOCUMENT_FRAGMENT_NODE) {
                            selNode = text.firstChild;
                        } else {
                            selNode = text;
                        }

                        node.insertBefore(text, node.childNodes[pos]);

                        this.add_range(selNode);

                        break;
                    case Node.TEXT_NODE:
                        if(text.nodeType == Node.TEXT_NODE) {
                            var text_length = pos + text.length;

                            node.insertData(pos, text.data);

                            range = this.editdoc.createRange();

                            range.setEnd(node, text_length);
                            range.setStart(node, text_length);

                            sel.addRange(range);
                        } else {
                            node = node.splitText(pos);

                            var selNode;

                            if(text.nodeType == Node.DOCUMENT_FRAGMENT_NODE) {
                                selNode = text.firstChild;
                            } else {
                                selNode = text;
                            }

                            node.parentNode.insertBefore(text, node);

                            this.add_range(selNode);
                        }

                        break;
                }
            };
        }
               
        if(typeof options != 'object') {
            options = {};
        }
                
        if(container.tagName.toLowerCase() == 'textarea') {
            var textarea = $(container);
            container = $('<div />').insertAfter(textarea);
            textarea.detach();
            container.append(textarea).data('osc-editor', this);
            
            container = container[0];
            
            if(! options.value) {
                options.value = textarea.val();
            }
            
            options.textarea = textarea[0];
        }
        
        options.container = container;
		
        this.resize_mark = null;
        this.resizable = true;
        this.resize_space = 0;
        this.bar_scroll = false;
        this.bar_scroll_additional_height = 0;
        this.min_height = null;
        this.max_height = 750;
        this.hide_control = false;
        this.container = null;
        this.name = null;
        this.renderer = null;
        this.height = 150;
        this.img_limit = -1;
        this.img_max_width = 0;
        this.img_max_height = 0;
        this.full_size = false;
        this.preview = false;
        this.html = false;
        this.range_saved = null;
        this.sel = null;
        this.cur_sel = null;
        this.sel_element = null;
        this.textarea = null;
        this.path_scene = null;
        this.has_focus = false;
        this.event_hooks = {};
        this.control_bar = null;
        this.e_buttons = {};
        this.event_handler = null;
        this.value = '';
        this.plugins = [];
        this.plugin_flags = {};
        this._context_callback = {};
        this._cmd_elms = {};
        this.buttons = [[['left', ['font','separate','size','separate','bold','italic','underline','separate','colorswatches','separate','link','separate','rule','separate','quote','insertunorderedlist','insertorderedlist','separate','imglink','imgbrowse','separate','embedcode','separate']],
                         ['right', ['separate','removeformat']]]];

        $.extend(this, options);
        
        this.height = parseInt(this.height);
        
        if(isNaN(this.height) || this.height < 20) {
            this.height = 20;
        }
        
        this.min_height = this.height;
        
        this.img_limit = parseInt(this.img_limit);
        
        if(isNaN(this.img_limit)) {
            this.img_limit = -1;
        }
        
        this.event_handler = new OSC_Editor_EventHandler();
        this.event_handler.setInstance(this);
        
        this.sel = new OSC_Editor_Selection();
        this.sel.setInstance(this);
        
        var self = this;
        
        this.event_hooks.paste = function(e){
            self.event_handler.paste(e, this);
        };
        
        this.event_hooks.docMouseUp = function(e){
            self.event_handler.docMouseUpHook(e, this);
        };
        
        this.event_hooks.docKeyUp = function(e){
            self.event_handler.docKeyUpHook(e, this);
        };
        
        this.event_hooks.winFocus = function(e){
            self.event_handler.winFocusHook(e, this);
        };
        
        this.event_hooks.winBlur = function(e){
            self.event_handler.winBlurHook(e, this);
        };
        
        this.event_hooks.docKeyDown = function(e){
            return self.event_handler.docKeyDownHook(e, this);
        };
        
        if(typeof this.renderer != 'object' || this.renderer === null) {
            this.renderer = new OSC_Editor_Renderer();
        }
        
        this.renderer.setInstance(this).render(this.hide_control);
        
        var plugin_keys = [];
        
        if(typeof this.plugins == 'string') {
            plugin_keys = this.plugins.split(',');
        } else {
            plugin_keys = this.plugins.slice(0);
        }
        
        this.plugins = {};
        
        for(var i = 0; i < plugin_keys.length; i ++) {
            var plugin_key = plugin_keys[i];
            
            if(typeof $.osc_editor._plugins[plugin_key] != 'undefined') {
                this.plugins[plugin_key] = new $.osc_editor._plugins[plugin_key]();
                this.plugins[plugin_key].setInstance(this);
            }
        }
        
        if(this.resizable) {
            this.resize_mark.mousedown(function(e) {
                self.event_handler.resizeDownHook(e);
            });
        }
                            
        if(this.bar_scroll) {
            var container_obj = $(this.container);

            $(window).scroll(function(e) {
                var scroll_top = $(window).scrollTop() + self.bar_scroll_additional_height;

                if(scroll_top > container_obj.offset().top) {
                    var css_position_val = self.control_bar.css('position').toLowerCase();
                    
                    if(css_position_val != 'fixed' && css_position_val != 'absolute') {
                        self.control_bar.width(self.control_bar.width());
                    }
                        
                    var max_scroll = container_obj.offset().top + container_obj.realHeight() - 150;

                    if(scroll_top < max_scroll) {
                        self.control_bar.css('position', 'fixed');
                        self.control_bar.css('top', self.bar_scroll_additional_height + 'px');
                    } else if(css_position_val != 'absolute') {
                        self.control_bar.css('position', 'absolute');
                        self.control_bar.offset({top : max_scroll});                            
                    }
                } else {
                    self.control_bar.removeAttr('style');
                }
            });
        }
        
        this.textarea.disabled = false;
        
        this.setup(this.value);
        
        $(this.container).addClass('mrk-editor-mark');
        
        if(this.set_focus) {
            this.focus();
        }
    }
            
    function OSC_Editor_Renderer() {      
        this.setInstance = function(inst) {
            this.inst = inst;
            return this;
        };
        
        this.render = function(hide_control) {            
            this.inst.container.innerHTML = '';

            var editor_scene = $('<div />').addClass('editor');
            
            editor_scene.appendTo(this.inst.container);
            
            this.inst.control_bar = $('<div />');
            
            this.inst.control_bar.appendTo(editor_scene);
                                
            if(hide_control) {
                this.inst.control_bar.css('display', 'none');
                this._control_bar_hided = true;
            }
                
            var bar_line = [];
            var bar_line_section = [];
            var command_btn = [];

            for(var l = 0; l < this.inst.buttons.length; l ++) {
                bar_line.push($('<div />').addClass('editor-bar').appendTo(this.inst.control_bar)[0]);

                for(var s = 0; s < this.inst.buttons[l].length; s ++) {
                    bar_line_section.push($('<div />').addClass(this.inst.buttons[l][s][0] == 'right' ? 'fright' : 'fleft').appendTo(bar_line[l])[0]);

                    for(var c = 0; c < this.inst.buttons[l][s][1].length; c ++) {
                        var command = this.inst.buttons[l][s][1][c];

                        if(command == 'separate') {
                            command_btn.push($('<div />').addClass('editor-separator')[0]);
                        } else {
                            if(this.command_map[command][0] == 1) {
                                var command_elm = this.renderCommandButton(command, false, this.command_map[command][1]);
                                this.inst._cmd_elms[command] = $(command_elm);
                                command_btn.push(command_elm);
                            } else {
                                command_btn.push(this.renderSpecialButton(command));
                            }
                        }

                        bar_line_section[bar_line_section.length-1].appendChild(command_btn[command_btn.length-1]);
                    }
                }
            }

            if(! this.inst.textarea) {
                this.inst.textarea = $('<textarea />');
            } else {
                this.inst.textarea = $(this.inst.textarea)
            }
            
            this.inst.textarea.attr('tabIndex', 100).appendTo($('<div />').addClass('textarea-wrap').append($('<div />').addClass('disable')).appendTo(editor_scene));
            
            this.inst.textarea = this.inst.textarea[0];

            if(this.inst.name) {
                this.inst.textarea.name = this.inst.name;
            }
            
            var path_container = $('<div />').addClass('editor-path').html('Path: ').appendTo(editor_scene);

            this.inst.path_scene = $('<span />');
            this.inst.path_scene.appendTo(path_container);
            this.inst.path_scene = this.inst.path_scene[0];
            
            if(this.inst.resizable) {
                this.inst.resize_mark = $('<div />').addClass('resizable').appendTo(editor_scene);
            }
        };
        
        this.renderCommandButton = function(cmd, disabled, toggler) {
            var cmd = $('<div />').attr('data-cmd', cmd)
                    .attr('activated', 1)
                    .addClass('editor-cmd-btn mrk-root-cmd-btn mrk-editor-cmd ' + cmd.toLowerCase());
            
            if(toggler) {
                $('<div />').addClass('toggler').appendTo(cmd);
            }
            
            return cmd[0];
        };
        
        this.renderSpecialButton = function(cmd) {
            return this['renderSpecialButton__' + cmd]();
        };
        
        this.renderSpecialButton__imgbrowse = function() {  
            var self = this;
                        
            var container = $('<div />').addClass('osc-editor-imgbrowse');            
            container.appendTo(document.body);
            
            var loading = $('<div />').addClass('loading');
            
            loading.appendTo(container);
            
            var uploader = $('<div />').addClass('uploader editor-cmd-btn imgbrowse');
            
            uploader.appendTo(container);
            
            var img_upload_renderer = new $.OSC_Uploader_Renderer();
            img_upload_renderer.lang.click_to_upload = 'Chọn file từ máy';
            
            var img_holder = null;
                     
            uploader.osc_uploader({
                    callback  : {
                        upload_fire : function(){
                            container.addClass('uploading');
                            img_holder = self.inst._renderImgHolder(); 
                        },
                        upload_complete : function(response){
                            container.removeClass('uploading');

                            eval("var response="+response);

                            if(response.result != 'OK') {
                                alert(response.data.message);
                                
                                if(img_holder) {
                                    img_holder.remove();
                                }
                            } else if(img_holder) {                
                                img_holder.removeAttr('class');
                                img_holder.attr('src', response.data.url);
                            } 

                            uploader.attr('activated', 1);
                            
                            return false;
                        } 
                    },
                    renderer : img_upload_renderer,
                    process_url  : $.osc_editor._IMG_ATTACH_URL
                });
            
            return container[0];
        }
        
        this.renderSpecialButton__link = function() {
            var container = $.createElement('div', {}, {
                cssFloat : 'left', 
                styleFloat : 'left'
            });
            
            this.inst._cmd_elms.link = $(this.renderCommandButton('link'));
            this.inst._cmd_elms.unlink = $(this.renderCommandButton('unlink', true));
            
            container.appendChild(this.inst._cmd_elms.link[0]);
            container.appendChild(this.inst._cmd_elms.unlink[0]);

            return container;
        }
        
        this._size = null;        
        this._font_preview = null;
        this._font_list = [
            ['Sand Serif', ['arial','helvetica','sans-serif']],
            ['Serif', ['times new roman','serif']],
            ['Narrow',['arial narrow']],
            ['Courier New',['courier new', 'monospace']],
            ['Garamond',['garamond']],
            ['Georgia',['georgia']],
            ['Tahoma',['tahoma']],
            ['Verdana',['verdana']]
        ];
        
        this._setFontPreview = function(font) {
            if(font == this._font) {
                return;
            }
            
            if(! this._font_preview || ! this._font_preview[0]) {
                return;
            }
            
            this._font = font;
            this._font_idx = -1;
            this._font_preview.html('');
            
            font = $.trim(font);
            font = font.replace(/['"']/g, '');
            font = font.replace(/\s{2,}/g, ' ');
            font = font.toLowerCase();
                            
            for(var x = 0; x < this._font_list.length; x ++) {        
                for(var y = 0; y < this._font_list[x][1].length; y ++) {
                    if(this._font_list[x][1][y] == font) {
                        this._font_idx = x;
                        this._font_preview.html(this._font_list[x][0]);
                        return;
                    }
                }
            }
        }
        
        this.renderSpecialButton__font = function() {
            var container = $('<div />').addClass('editor-font-btn');
            
            this._font_preview = $('<div />').addClass('preview').html('Tahoma');
            
            container.append(this._font_preview);
            container.append($('<div />').addClass('toggler'));
            container.append($('<div />').addClass('btn-wrap mrk-root-cmd-btn').attr('activated', 1));

            return container[0];
        }
        
        this.renderFontForm = function(e) {
            var self = this;
            
            var scene = this.renderPopupForm(e, 'font-frm');
            var container = $('<div />').addClass('editor-font-list').appendTo(scene);
            
            this.inst._setFontContext();
                        
            var matched = false;
             
            for(var x = 0; x < this._font_list.length; x ++) {
                var item = $('<div />').addClass('editor-font-item').appendTo(container).css('font-family', this._font_list[x][1].join(','));
                item.append($('<div />').html(this._font_list[x][0])).attr('rel', this._font_list[x][1][0]).click(function(e) {
                    self.inst._fontCommand(e, $(this).attr('rel'));         
                    self.removePopupForm();
                });
                
                if(! matched) {
                    for(var y = 0; y < this._font_list[x][1].length; y ++) {
                        if(this._font_list[x][1][y] == this._font) {
                            matched = true;
                            item.addClass('cur');
                        }
                    }
                }
            }          
        }
        
        this._size = null;
        this._size_list = [
            ['Small', 10],
            ['Normal', 13],
            ['Large', 18],
            ['Huge', 32]
        ];
        
        this._setSizePreview = function(size) {
            this._size = size;
        }
        
        this.renderSizeForm = function(e) {
            var self = this;
            
            this.inst._setSizeContext();
            
            var scene = this.renderPopupForm(e, 'size-frm');
            var container = $('<div />').addClass('editor-size-list').appendTo(scene);
            
            var cur_item = null;
             
            for(var x = 0; x < this._size_list.length; x ++) {
                var item = $('<div />').addClass('editor-size-item').appendTo(container).css('font-size', this._size_list[x][1] + 'px').css('height', this._size_list[x][1] + 'px');
                item.append($('<div />').html(this._size_list[x][0])).attr('rel', this._size_list[x][1] + 'px').click(function(e) {
                    self.inst._sizeCommand(e, $(this).attr('rel'));         
                    self.removePopupForm();
                });
                
                if($.osc_editor._formatFontsize(this._size_list[x][1] + 'px') <= this._size) {
                    cur_item = item;
                }
            }    
            
            if(cur_item) {
                cur_item.addClass('cur');
            }
        }
        
        this._color_swatches_data = [
            ['000000','444444','666666','999999','cccccc','eeeeee','f3f3f3','ffffff'],
            ['ff0000','ff9900','ffff00','00ff00','00ffff','0000ff','9900ff','ff00ff'],
            ['f4cccc','fce5cd','fff2cc','d9ead3','d0e0e3','cfe2f3','d9d2e9','ead1dc',
             'ea9999','f9cb9c','ffe599','b6d7a8','a2c4c9','9fc5e8','b4a7d6','d5a6bd',
             'e06666','f6b26b','ffd966','93c47d','76a5af','6fa8dc','8e7cc3','c27ba0',
             'cc0000','e69138','f1c232','6aa84f','45818e','3d85c6','674ea7','a64d79',
             '990000','b45f06','bf9000','38761d','134f5c','0b5394','351c75','741b47',
             '660000','783f04','7f6000','274e13','0c343d','073763','20124d','4c1130']
        ];
        
        this._text_color = null;
        this._back_color = null;
        
        this.setColorPreview = function(hex_val) {
            this._text_color = hex_val.substring(1);
        }
        
        this.setBackgroundPreview = function(hex_val) {
            this._back_color = hex_val.substring(1);
        }
        
        this.renderSwatchesForm = function(e) {            
            var self = this;
            
            var scene = this.renderPopupForm(e, 'swatches-frm');
            var container = $('<div />').addClass('editor-swatches-layout').appendTo(scene);
             
            var row = null;
            
            this.inst._setColorContext();
            this.inst._setBackContext();
            
            var commands = [
                [
                    'Text color',
                    this._text_color,
                    function(e){
                        self.inst._colorCommand(e, $(this).attr('rel'));
                        self.removePopupForm();
                    }
                ],
                [
                    'Background color',
                    this._back_color,
                    function(e){
                        self.inst._backCommand(e, $(this).attr('rel'));
                        self.removePopupForm();
                    }
                ]
            ];
            
            for(var i = 0; i < commands.length; i ++) {
                var command_container = $('<div />').addClass('swatches-col').appendTo(container);
                
                $('<div />').addClass('title').html(commands[i][0]).appendTo(command_container);
                
                var tbl = $('<div />').addClass('editor-swatches-table').appendTo(command_container);
                
                for(var x = 0; x < this._color_swatches_data.length; x ++) {
                    var group = $('<div />').addClass('group').appendTo(tbl);

                    for(var y = 0; y < this._color_swatches_data[x].length; y ++) {
                        if(y % 8 == 0) {
                            row = $('<div />').addClass('row').appendTo(group);
                        }

                        var item = $('<div />').click(commands[i][2])
                                .attr('rel', this._color_swatches_data[x][y])
                                .css('background-color', '#' + this._color_swatches_data[x][y])
                                .appendTo(row);
                        
                        if(this._color_swatches_data[x][y] == commands[i][1]) {
                            item.addClass('cur');
                        }
                    }
                }   
            }
        };
        
        this.popup_frm = null;
        
        this.renderPopupForm = function(e, type) {
            var self = this;
            
            if(this.popup_frm) {
                this.popup_frm.remove();
            }
            
            this.popup_frm = $('<div />').addClass('editor-popup-frm ' + type).appendTo(document.body);
            
            $('<div />').addClass('arrow').append($('<div />')).appendTo(this.popup_frm);
            $('<div />').addClass('disable').appendTo(this.popup_frm);
            
            e.stopPropagation();
            
            var doc_click_hook = function(e) {
                $(document).unbind('click', doc_click_hook);
                $(self.inst.editdoc).unbind('click', doc_click_hook);
                self.popup_frm.remove();
            };
            
            $(document).click(doc_click_hook);
            $(this.inst.editdoc).click(doc_click_hook);
            
            this._movePopupForm(e);
            
            return this.popup_frm;
        };
        
        this.disablePopupForm = function() {
            if(! this.popup_frm) {
                return;
            }
            
            $('> .disable', this.popup_frm[0]).addClass('active');
        };
        
        this.enablePopupForm = function() {
            if(! this.popup_frm) {
                return;
            }
            
            $('> .disable', this.popup_frm[0]).removeClass('active');
        };
        
        this.removePopupForm = function() {
            if(! this.popup_frm) {
                return;
            }          
                
            this.popup_frm.remove();  
        };
        
        this._movePopupForm = function(e) {
            if(! this.popup_frm) {
                return;
            }
            
            var toggler = $(e.target);
            
            while(true) {
                if(toggler.hasClass('mrk-root-cmd-btn')) {
                    break;
                }
                
                toggler = toggler.parent();
                
                if(toggler[0].nodeName == 'BODY') {
                    toggler = $(e.target);
                    break;
                }
            }
            
            var editbox_coords = $(this.inst.editbox).offset();
            
            this.popup_frm.click(function(e) { e.stopPropagation(); });
            
            var coords = toggler.offset();
            
            coords.left -= Math.ceil((this.popup_frm.width()-toggler.width())/2);
            
            if(coords.left < editbox_coords.left + 5) {
                coords.left = editbox_coords.left + 5;
            }
            
            this.popup_frm.css({
                top: (coords.top + toggler.height() + 10) + 'px',
                left: coords.left + 'px'
            });
            
            var arrow_left = toggler.offset().left - coords.left;
            
            if(arrow_left < 25) {
                arrow_left = 25;
            }
            
            $('> .arrow', this.popup_frm[0]).css('left', arrow_left + 'px');
        };
        
        this.renderImgForm = function(e) {
            var self = this;
            
            var scene = this.renderPopupForm(e, 'img-frm');
            
            var input_wrap = $('<div />').addClass('input-wrap').appendTo(scene);
            
            var url_input = $('<input />').appendTo($('<div />').appendTo($('<div />').addClass('text-input').appendTo(input_wrap)));
            
            url_input.val('Liên kết ảnh (URL)').attr('rel', 'Liên kết ảnh (URL)');
            
            url_input.focus(function(e){
                var obj = $(this);

                if(obj.val() == obj.attr('rel')) {
                    obj.val('');
                }
            }).blur(function(e){
                var obj = $(this);

                if(obj.val() == '') {
                    obj.val(obj.attr('rel'));
                }
            }).click(function(e) { this.select(); });
            
            var btn = $('<div />').html('Đính Ảnh').addClass('apply-btn').appendTo(scene);
            
            btn.click(function(e) {
                var url = url_input.val();
                
                if(! url || url == url_input.attr('rel')) {
                    alert('Bạn chưa điền link ảnh muốn đính');
                    return false;
                }
                
                self.removePopupForm();
                            
                var img_holder = self.inst._renderImgHolder(e);    
                
                $.ajax({
                    type: 'POST',
                    data : {url : url},
                    url: $.osc_editor._IMG_ATTACH_URL,
                    success: function(response){
                        eval("var response="+response);

                        if(response.result != 'OK') {
                            alert(response.data.message);
                            
                            if(img_holder) {
                                img_holder.remove();
                            }
                        } else if(img_holder) {                
                            img_holder.removeAttr('class');
                            img_holder.attr('src', response.data.url);      
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        img_holder.remove();
                        alert('ERROR [#' + xhr.status + ']: ' + thrownError);
                    }
                });
            });
        };
        
        this.renderEmbedCodeForm = function(e) {
            var self = this;
            
            var scene = this.renderPopupForm(e, 'embed-code-frm');
            
            var input_wrap = $('<div />').addClass('input-wrap').appendTo(scene);
            
            var code_input = $('<input />').appendTo($('<div />').appendTo($('<div />').addClass('text-input').appendTo(input_wrap)));
            
            code_input.val('Mã nhúng').attr('rel', 'Mã nhúng');
            
            code_input.focus(function(e){
                var obj = $(this);

                if(obj.val() == obj.attr('rel')) {
                    obj.val('');
                }
            }).blur(function(e){
                var obj = $(this);

                if(obj.val() == '') {
                    obj.val(obj.attr('rel'));
                }
            }).click(function(e) { this.select(); });
            
            var btn = $('<div />').html('Đính mã nhúng').addClass('apply-btn').appendTo(scene);
            
            btn.click(function(e) {
                var code = code_input.val();
                
                if(! code || code == code_input.attr('rel')) {
                    alert('Bạn chưa điền mã nhúng');
                    return false;
                }
                
                if(self.inst._embedcodeCommand(e, code)) {
                    self.removePopupForm();
                }
            });
        };
        
        this.renderAnchorForm = function(e, params) {            
            var self = this;
            
            var scene = this.renderPopupForm(e, 'anchor-frm');
            
            var input_wrap = $('<div />').addClass('input-wrap').appendTo(scene);
            
            var url_input = $('<input />').appendTo($('<div />').appendTo($('<div />').addClass('text-input').appendTo(input_wrap)));
            
            url_input.val('Link URL').attr('rel', 'Link URL');
            
            if(params.href) {
                url_input.val(params.href);
            }
            
            var title_input = $('<input />').appendTo($('<div />').appendTo($('<div />').addClass('text-input').appendTo(input_wrap)));
            
            title_input.val('Nội dung hiển thị').attr('rel', 'Nội dung hiển thị');
            
            if(params.text) {
                title_input.val(params.text);
            }
            
            var frms = [url_input,title_input];
            
            for(var x = 0; x < frms.length; x ++) {
                frms[x].focus(function(e){
                    var obj = $(this);
                    
                    if(obj.val() == obj.attr('rel')) {
                        obj.val('');
                    }
                }).blur(function(e){
                    var obj = $(this);
                    
                    if(obj.val() == '') {
                        obj.val(obj.attr('rel'));
                    }
                }).click(function(e) { this.select(); });
            }
            
            var btn = $('<div />').html('Đính Link').addClass('apply-btn').appendTo(scene);
            
            btn.click(function(e) {
                var url = url_input.val();
                
                if(! url || url == url_input.attr('rel')) {
                    alert('Bạn chưa điền link URL muốn đính');
                    return false;
                }
                
                var title = title_input.val();
                
                if(! title || title == title_input.attr('rel')) {
                    title = url;
                }
                            
                self.inst._linkCommand(e, {title : title, alt : title, href : url});    
                self.removePopupForm();
            });
    };
        
    this.toggleControlBar = function() {
        if(this._control_bar_toggle_locked) {
            return false;
        }
            
        this._control_bar_toggle_locked = true;
            
        var self = this;
            
        this.inst.control_bar[this._control_bar_hided ? 'slideDown' : 'slideUp'](function() {
            self._control_bar_toggle_locked = false;
            self._control_bar_hided = ! self._control_bar_hided;
        });
    };
        
    this._control_bar_toggle_locked = false;
    this._control_bar_hided = false;
        
    this.inst = null;
    this.context_controls = ['bold','italic','underline','justifyleft','justifycenter','justifyright','justifyfull','insertorderedlist','insertunorderedlist','strikethrough'];
    this.command_map = {
        insertunorderedlist : [1], 
        insertorderedlist : [1], 
        link : [2], 
        bold : [1], 
        quote : [1],
        rule : [1], 
        italic : [1],
        underline : [1],
        imglink : [1], 
        imgbrowse : [2],
        embedcode : [1],
        size : [1, true], 
        font : [2],
        colorswatches : [1, true],
        removeformat : [1]
    };
    this.win = null;
}
    
function OSC_Editor_Selection() {
    this.setInstance = function(inst) {
        this.inst = inst;
        return this;
    }
        
    this.selectNode = function(node, collapse, select_text_node, to_start) {
        if(! node) {
            return false;
        }

        if(typeof collapse == "undefined") {
            collapse = true;
        }

        if(typeof select_text_node == "undefined") {
            select_text_node = false;
        }

        if(typeof to_start == "undefined") {
            to_start = true;
        }

        if($.browser.msie) {
            var range = this.inst.editdoc.body.createTextRange();

            try {
                range.moveToElementText(node);

                if(collapse) {
                    range.collapse(to_start);
                }

                range.select();
            } catch(e) {}
        } else {
            var sel = this.inst.editwin.getSelection();

            if(! sel) {
                return false;
            }

            if($.browser.safari) {
                sel.setBaseAndExtent(node, 0, node, node.innerText.length);

                if(collapse) {
                    if(to_start) {
                        sel.collapseToStart();
                    } else {
                        sel.collapseToEnd();
                    }
                }

                this.scrollToNode(node);

                return true;
            }

            var range = this.inst.editdoc.createRange();

            if(select_text_node) {
                var nodes = this.getNodeTree(node, [], 3);
					
                if(nodes.length > 0) {
                    range.selectNodeContents(nodes[0]);
                } else {
                    range.selectNodeContents(node);
                }
            } else {
                range.selectNode(node);
            }

            if (collapse) {
                if(! to_start && node.nodeType == 3) {
                    range.setStart(node, node.nodeValue.length);
                    range.setEnd(node, node.nodeValue.length);
                } else {
                    range.collapse(to_start);
                }
            }

            sel.removeAllRanges();
            sel.addRange(range);
        }

        this.scrollToNode(node);
    }
        
    this.scrollToNode = function(node) {
        var node_offset = $(node).offset();
                        
        var vp = $.getViewPort(this.inst.editwin);

        if(node_offset.left < vp.x || node_offset.left > vp.x + vp.w || node_offset.top < vp.y || node_offset.top > vp.y + (vp.h - 25)) {
            this.inst.editwin.scrollTo(node_offset.left, node_offset.top - vp.h + 25);
        }
    }
        
    this.getSel = function() {
        if($.browser.msie) {
            return this.inst.editdoc.selection;
        }

        return this.inst.editwin.getSelection();
    }
        
    this.getNodeTree = function(n, na, t, nn) {
        return this.selectNodes(n, function(n) {
            return (!t || n.nodeType == t) && (!nn || n.nodeName == nn);
        }, na ? na : []);
    }
        
    this.selectNodes = function(n, f, a) {
        var i;

        if(! a) {
            a = [];
        }

        if(f(n)) {
            a[a.length] = n;
        }

        if(n.hasChildNodes()) {
            for(i = 0; i < n.childNodes.length; i ++) {
                this.selectNodes(n.childNodes[i], f, a);
            }
        }

        return a;
    }
        
    this.inst = null;
}
    
function OSC_Editor_EventHandler() {
    this.setInstance = function(inst) {
        this.inst = inst;
        return this;
    }
    
    this._getPastedData = function(e, type) {
        for(var i = 0; i < e.clipboardData.types.length; i ++) {
            if(e.clipboardData.types[i] == type) {
                return e.clipboardData.getData(type);
            }
        }
        
        return false;
    }
    
    this.paste = function(e) {
        e.preventDefault();
        
        var pasted_content = '';
        
        if (window.clipboardData && window.clipboardData.getData) {
            pasted_content = window.clipboardData.getData('Text');
        } else if (e.clipboardData && e.clipboardData.getData) {
            pasted_content = this._getPastedData(e, 'text/html');
            
            if(pasted_content === false) {
                pasted_content = this._getPastedData(e, 'text/plain');
                pasted_content = pasted_content.replace('\n', '<br />');
            }
        } else {
            return;
        }
        
        this.inst._processPastedContent(e, pasted_content);
    };
        
    this.winFocusHook = function(e) {
        $.osc_editor.CUR_INST = this.inst;
        this.inst.has_focus = true;
    };
        
    this.winBlurHook = function(e) {
        this.inst.setContent(this.inst.getContent(), true);
        this.inst.has_focus = false;
    };
        
    this.docMouseUpHook = function(e) {
        this.inst._setContext(e);
    };
        
    this.docKeyUpHook = function(e) {
        this.inst._setContext(e);
    };	
        
    this.commandButtonMouseEventHook = function(e) {
        var obj = $(this);

        if(obj.attr('activated') != 1) {
            return false;
        }
            
        var inst = obj.data('osc-editor');

        if(e.type == 'click') {
            inst._execCommand(e, obj.attr('cmd'), false, true);
        }

        inst._buttonContext(this, e.type);
            
        return true;
    };
    
    this.resizeDownHook = function(e) {
        $(document).mouseup(this.resize_up_hook_holder).mousemove(this.resize_move_hook_holder);
        
        $(document.body).addClass('editor-dis-sel').addClass('editor-resize-cursor');
        
        this.inst.disable();
        
        var editbox = $(this.inst.editbox);
        
        this.inst.resize_space = e.pageY - editbox.offset().top - editbox.height();
    };
    
    this.resizeUpHook = function(e) {
        $(document).unbind('mouseup', this.resize_up_hook_holder).unbind('mousemove', this.resize_move_hook_holder);
        $(document.body).removeClass('editor-dis-sel').removeClass('editor-resize-cursor');
        this.inst.enable();
    };
    
    this.resizeMoveHook = function(e) {        
        var editbox = $(this.inst.editbox);
        
        var height = e.pageY - this.inst.resize_space - editbox.offset().top;
        
        if(height < this.inst.min_height) {
            height = this.inst.min_height;
        } else if(height > this.inst.max_height) {
            height = this.inst.max_height;            
        }
        
        this.inst.setEditorHeight(height);
    };
       
    this.docKeyDownHook = function(e, obj) {
        if(e.keyCode == 13) {                
            if($.browser.msie) {     
                var _test = ['Indent','Outdent','JustifyLeft','JustifyCenter','JustifyRight','InsertOrderedList','InsertUnorderedList'];

                for(var i in _test) {
                    if(this.inst.editwin.document.queryCommandState(_test[i])) {alert('OK');
                        return true;
                    }
                }
            
                var sel = this.inst.editdoc.selection;
                var ts = this.inst.editdoc.selection.createRange();
                var t = ts.htmlText.replace(/<p([^>]*)>(.*)<\/p>/i, '$2');

                if(sel.type == "Text" || sel.type == "None") {
                    ts.pasteHTML("<br />" + t + "\n");
                } else {
                    this.inst.editdoc.innerHTML += "<br />\n";
                }

                e.returnValue = false;
                ts.select();
            } else {
                var parent = this.inst._getFocusElement();
                
                if(!parent) {
                    return true;
                }
                 
                function _chkSpecialBlock(elm) {
                    var obj = $(elm);
                    return obj.hasClass('editor-quote');
                }
                
                if(! _chkSpecialBlock(parent)) {
                    if(! e.shiftKey) {
                        return true;                        
                    }
                    
                    parent = $.osc_editor._getParentElement(parent, 'DIV', _chkSpecialBlock);

                    if(!parent) {
                        return true;
                    }
                }
                
                if(e.shiftKey) {
                    var elm = $('<span />').html('&#xFEFF;').insertAfter(parent);

                    this.inst.sel.selectNode(elm[0], false, false);                    
                    this.inst.sel.scrollToNode(elm[0]);
                                        
                    this.inst.editdoc.execCommand('Delete', false, null);
                } else {
                    this.inst.insertText("<br />\n", $.mb_strlen("<br />\n"), 0, true);
                }
                    
                e.returnValue = false;
            }
            
            this.inst.checkFocus();
            
            return false;
        } else if(e.keyCode == 86 && (e.ctrlKey || e.metaKey)) {
            var self = this;
            
            var wrap = $('<div />').css({
                position : 'absolute',
                top : $(this.inst.editdoc).scrollTop() + 'px',
                left : 0,
                width : '1px',
                height : '1px',
                background : '#ff0000',
                overflow : 'hidden'
            }).prop('contentEditable', false).appendTo(this.inst.editdoc.body);
            var container = $('<div />').prop('contentEditable', true).html('x').appendTo(wrap);

            var marker = '<span id="osc-paste-marker">&#xFEFF;</span>';
            this.inst.insertText(marker, $.mb_strlen(marker));

            this.inst.sel.selectNode(container[0], false, true);

            container.bind('paste', function(e) {
                e.stopPropagation();

                setTimeout(function() {
                    wrap.remove();
                    
                    var marker = $('#osc-paste-marker', self.inst.editdoc);
                                        
                    self.inst.sel.selectNode(marker[0], false, false);            
                    self.inst.editdoc.execCommand('Delete', false, null);
                    
                    self.inst._processPastedContent(e, container.html());
                }, 0);
            });
        }
        
        return true;
    }
    
    var self = this;
        
    this.inst = null;
    
    this.resize_up_hook_holder = function(e) {
        self.resizeUpHook(e);
    };
    
    this.resize_move_hook_holder = function(e) {
        self.resizeMoveHook(e);
    };
}
            
    $.osc_editor = new OSC_Editor();
    
    $.fn.osc_editor = function() {
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
        
        if(func && func.toLowerCase() == 'getcontent') {
            var instance = $(this[0]).data('osc-editor');
            
            if(instance) {
                return instance.getContent();                
            } else {
                return '';
            }
        }
               
        return this.each(function() {
            var instance = $(this).data('osc-editor');
                
            if(func) {
                if(instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if(! instance) {
                    $(this).data('osc-editor', new OSC_Editor_Item(this, opts));
                }
            }
        });
    }
    
    $(document).on('insert', '.mrk-osc-editor-init', function(e) {
        var opts = $(this).attr('uiconf');
        
        if(opts) {
            eval('opts = ' + opts);
        }
        
        if(typeof opts != 'object') {
            opts = {};
        }
        
        $(this).removeAttr('uiconf').removeClass('mrk-osc-editor-init').osc_editor(opts);
    });
    
    var _IN_SUBMIT_EVENT_FLAG = false;
    var _CLEAR_IN_SUBMIT_EVENT_FLAG = null;
    
    $(document).on('submit', 'form', function(e) {
        _IN_SUBMIT_EVENT_FLAG = true;
        
        clearTimeout(_CLEAR_IN_SUBMIT_EVENT_FLAG);
        
        _CLEAR_IN_SUBMIT_EVENT_FLAG = setTimeout(function(){ _IN_SUBMIT_EVENT_FLAG = false; }, 500);
    });
    
    $(window).bind('beforeunload', function(e){   
        if(_IN_SUBMIT_EVENT_FLAG) {
            return;
        }
        
        var collection = $('.mrk-editor-mark');
        
        if (collection.length < 1){
            return;
        }
        var message = '';

        collection.each(function() {
            if($(this).osc_editor('getContent')) {
                message = 'Hiện bạn đang có dữ liệu soạn thảo dở. Bạn có muốn thoát không?';     
                return false;
            }
        });
        
        if(! message) {
            return;
        }
        
        e.returnValue = message;
        // for webkit
        return message;
    }); 
    
    function TinyMCE_Tools() {
        /**
	 * Makes a name/object map out of an array with names.
	 *
	 * @method makeMap
	 * @param {Array/String} items Items to make map out of.
	 * @param {String} delim Optional delimiter to split string by.
	 * @param {Object} map Optional map to add items to.
	 * @return {Object} Name/value map of items.
	 */
	this.makeMap = function(items, delim, map) {
            var i;

            items = items || [];
            delim = delim || ',';

            if (typeof(items) == "string") {
                items = items.split(delim);
            }

            map = map || {};

            i = items.length;
            while (i--) {
                map[items[i]] = {};
            }

            return map;
	}
        
        /**
	 * Performs an iteration of all items in a collection such as an object or array. This method will execure the
	 * callback function for each item in the collection, if the callback returns false the iteration will terminate.
	 * The callback has the following format: cb(value, key_or_index).
	 *
	 * @method each
	 * @param {Object} o Collection to iterate.
	 * @param {function} cb Callback function to execute for each item.
	 * @param {Object} s Optional scope to execute the callback in.
	 * @example
	 * // Iterate an array
	 * tinymce.each([1,2,3], function(v, i) {
	 *     console.debug("Value: " + v + ", Index: " + i);
	 * });
	 *
	 * // Iterate an object
	 * tinymce.each({a: 1, b: 2, c: 3], function(v, k) {
	 *     console.debug("Value: " + v + ", Key: " + k);
	 * });
	 */
        
        this.each = function(o, cb, s) {
            var n, l;

            if (!o) {
                return 0;
            }

            s = s || o;

            if (o.length !== undefined) {
                // Indexed arrays, needed for Safari
                for (n=0, l = o.length; n < l; n++) {
                    if (cb.call(s, o[n], n, o) === false) {
                        return 0;
                    }
                }
            } else {
                // Hashtables
                for (n in o) {
                    if (o.hasOwnProperty(n)) {
                        if (cb.call(s, o[n], n, o) === false) {
                            return 0;
                        }
                    }
                }
            }

            return 1;
	}
    }
    
    function TinyMCE_WordFilter(content) {
        this.process = function(items) {
            Tools.each(items, function(v) {
                if (v.constructor == RegExp) {
                    content = content.replace(v, '');
                } else {
                    content = content.replace(v[0], v[1]);
                }
            });
        }
        
       /**
        * Converts fake bullet and numbered lists to real semantic OL/UL.
        *
        * @param {tinymce.html.Node} node Root node to convert children of.
        */
       
        this.convertFakeListsToProperLists = function(node) {
            var currentListNode, prevListNode, lastLevel = 1;

            function convertParagraphToLi(paragraphNode, listStartTextNode, listName, start) {
                var level = paragraphNode._listLevel || lastLevel;

                // Handle list nesting
                if (level != lastLevel) {
                    if (level < lastLevel) {
                        // Move to parent list
                        if (currentListNode) {
                            currentListNode = currentListNode.parent.parent;
                        }
                    } else {
                        // Create new list
                        prevListNode = currentListNode;
                        currentListNode = null;
                    }
                }

                if (!currentListNode || currentListNode.name != listName) {
                    prevListNode = prevListNode || currentListNode;
                    currentListNode = new TinyMCE_HTML_Node(listName, 1);

                    if (start > 1) {
                        currentListNode.attr('start', '' + start);
                    }

                    paragraphNode.wrap(currentListNode);
                } else {
                    currentListNode.append(paragraphNode);
                }

                paragraphNode.name = 'li';
                listStartTextNode.value = '';

                var nextNode = listStartTextNode.next;
                if (nextNode && nextNode.type == 3) {
                    nextNode.value = nextNode.value.replace(/^\u00a0+/, '');
                }

                // Append list to previous list
                if (level > lastLevel) {
                    prevListNode.lastChild.append(currentListNode);
                }

                lastLevel = level;
            }

            var paragraphs = node.getAll('p');

            for (var i = 0; i < paragraphs.length; i++) {
                node = paragraphs[i];

                if (node.name == 'p' && node.firstChild) {
                    // Find first text node in paragraph
                    var nodeText = '';
                    var listStartTextNode = node.firstChild;

                    while (listStartTextNode) {
                        nodeText = listStartTextNode.value;
                        
                        if (nodeText) {
                            break;
                        }

                        listStartTextNode = listStartTextNode.firstChild;
                    }

                    // Detect unordered lists look for bullets
                    if (/^\s*[\u2022\u00b7\u00a7\u00d8o\u25CF]\s*$/.test(nodeText)) {
                        convertParagraphToLi(node, listStartTextNode, 'ul');
                        continue;
                    }

                    // Detect ordered lists 1., a. or ixv.
                    if (/^\s*\w+\./.test(nodeText)) {
                        // Parse OL start number
                        var matches = /([0-9])\./.exec(nodeText);
                        var start = 1;
                        if (matches) {
                            start = parseInt(matches[1], 10);
                        }

                        convertParagraphToLi(node, listStartTextNode, 'ol', start);
                        continue;
                    }

                    currentListNode = null;
                }
            }
        }
        
        this.filterStyles = function(node, styleValue) {
            // Parse out list indent level for lists
            if (node.name === 'p') {
                var matches = /mso-list:\w+ \w+([0-9]+)/.exec(styleValue);

                if (matches) {
                    node._listLevel = parseInt(matches[1], 10);
                }
            }

            if (this.retain_style_properties) {
                var outputStyle = "";

                Tools.each($.parseStyle(styleValue), function(value, name) {
                    // Convert various MS styles to W3C styles
                    switch (name) {
                        case "horiz-align":
                            name = "text-align";
                            return;
                        case "vert-align":
                            name = "vertical-align";
                            return;
                        case "font-color":
                        case "mso-foreground":
                            name = "color";
                            return;
                        case "mso-background":
                        case "mso-highlight":
                            name = "background";
                            break;
                    }

                    // Output only valid styles
                    if (this.retain_style_properties == "all" || (this.valid_styles && this.valid_styles[name])) {
                        outputStyle += name + ':' + value + ';';
                    }
                });

                if (outputStyle) {
                    return outputStyle;
                }
            }

            return null;
        }
        
        this.convert = function(content) {
            if (! /class="?Mso|style="[^"]*\bmso-|style='[^'']*\bmso-|w:WordDocument/i.test(content)) {
                return content;
            }
            
            var self = this;
            
            // Remove basic Word junk
            this.process([
                // Word comments like conditional comments etc
                /<!--[\s\S]+?-->/gi,
                // Remove comments, scripts (e.g., msoShowComment), XML tag, VML content,
                // MS Office namespaced tags, and a few other tags
                /<(!|script[^>]*>.*?<\/script(?=[>\s])|\/?(\?xml(:\w+)?|img|meta|link|style|\w:\w+)(?=[\s\/>]))[^>]*>/gi,
                // Convert <s> into <strike> for line-though
                [/<(\/?)s>/gi, "<$1strike>"],
                // Replace nsbp entites to char since it's easier to handle
                [/&nbsp;/gi, "\u00a0"],
                // Convert <span style="mso-spacerun:yes">___</span> to string of alternating
                // breaking/non-breaking spaces of same length
                [/<span\s+style\s*=\s*"\s*mso-spacerun\s*:\s*yes\s*;?\s*"\s*>([\s\u00a0]*)<\/span>/gi,
                    function(str, spaces) {
                        return (spaces.length > 0) ? spaces.replace(/./, " ").slice(Math.floor(spaces.length/2)).split("").join("\u00a0") : "";
                    }
                ]
            ]);

            // Setup strict schema
            var schema = new TinyMCE_HTML_Schema({
                valid_elements : '@[style],-strong/b,-em/i,-span,-p,-ol,-ul,-li,-h1,-h2,-h3,-h4,-h5,-h6,-table,' +
                                '-tr,-td[colspan|rowspan],-th,-thead,-tfoot,-tbody,-a[!href]'
            });

            // Parse HTML into DOM structure
            var domParser = new TinyMCE_HTML_DomParser({}, schema);

            // Filte element style attributes
            domParser.addAttributeFilter('style', function(nodes) {
                var i = nodes.length, node;

                while (i--) {
                    node = nodes[i];
                    node.attr('style', self.filterStyles(node, node.attr('style')));

                    // Remove pointess spans
                    if (node.name == 'span' && !node.attributes.length) {
                        node.unwrap();
                    }
                }
            });

            // Parse into DOM structure
            var rootNode = domParser.parse(content);

            // Process DOM
            this.convertFakeListsToProperLists(rootNode);

            // Serialize DOM back to HTML
            return new TinyMCE_HTML_Serializer({}, schema).serialize(rootNode);
        }

        this.retain_style_properties = "font-size, font-weight, font-family, color, ul, ol, li, text-decoration, background";
        
        this.valid_styles = Tools.makeMap(this.retain_style_properties);
    }
    
    var Tools = new TinyMCE_Tools();
})(jQuery);