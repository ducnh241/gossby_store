/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

(function($){		
    function OSC_AvaUploader(container, options) {
        this.render = function() {
            var self = this;
            
            this.ava_container = $('<div />').addClass('ava-frm-img-wrap');
            this.ava_preview = $('<img />').addClass('ava-preview').attr('src', this.ava_url);
            this.ava_remove_btn = $('<span />').addClass('rmv-ava transition-0-25' + (this.ava_extension ? ' active' : '')).append($('<i />')).click(function() { self.remove(); });
            
            this.container.append(this.ava_container);
            this.ava_container.append(this.ava_preview);
            this.ava_container.append(this.ava_remove_btn);
            
            this.uploader = $('<div />');
            
            this.ava_container.append(this.uploader);
            
            this.setupUploader();
        }
        
        this.uploadSuccess = function(response) {            
            if(response.result == 'ERROR') {
                alert(response.data.message);
            } else {
                if(response.data.cropper == 1) {
                    var self = this;
                    
                    $('<img />').load(function() {
                        $(this).attr('origW', this.width);
                        $(this).attr('origH', this.height);
                        self.setupCropper($(this), response.data.ratio_dim, response.data.min_dim, response.data.max_dim);
                    }).attr('src', response.data.url);
                } else {
		
                }
            }
        }
        
        this.setupCropper = function(img, ratio_dim, min_dim, max_dim) {
            var self = this;
            var win = null;
            
            $.disablePage();
	
            this.crop_coords = {
                x1 : null,
                y1 : null,
                x2 : null,
                y2 : null
            };
            
            var container = $('<div />').addClass('ava-cropper');
            var cropper_area = $('<div />').addClass('cropper-area');
            container.append(cropper_area);
            cropper_area.append($('<div />').append(img));
            
            var action_bar = $('<div />').addClass('action-bar');
            container.append(action_bar);
            
            action_bar.append($('<div />').addClass('btn')
                    .append($('<i />').addClass('fa fa-crop mr5'))
                    .append('Do Crop')
                    .click(function(e) {
                        self.crop(e, win, img);
                    }));
            
            action_bar.append($('<div />').addClass('btn red ml5')
                    .append($('<i />').addClass('fa fa-minus-square-o mr5'))
                    .append('Cancel')
                    .click(function(e) {
                        win.destroy(e);
                    }));
                    
            img.addClass('cropper-preview');
            
            win = $.create_window({
                title : this.lang.cropper_frm_title,
                content : container,
                destroy_hook : function(e){ return self.cancel(e); }
            });

            img.osc_cropper({
                callback : function(coords) {
                    self.crop_coords.x1 = coords.x1;
                    self.crop_coords.y1 = coords.y1;
                    self.crop_coords.x2 = coords.x2;
                    self.crop_coords.y2 = coords.y2;
                },
                ratioDim : {
                    x : ratio_dim, 
                    y : ratio_dim
                },
                minDim : {
                    x : min_dim, 
                    y : min_dim
                },
                maxDim : {
                    x : max_dim, 
                    y : max_dim
                },
                displayOnInit : true
            });
        }
        
        this.setupUploader = function() {
            var avatar_upload_renderer = new $.OSC_Uploader_Renderer();
            
            avatar_upload_renderer.lang.click_to_upload = 'Upload avatar';
            avatar_upload_renderer.lang.drop_file_here = 'Drop avatar here';

            var self = this;

            this.uploader.html('').osc_uploader({
                name : 'ava',
                callback  : {
                    upload_complete : function(response) {
                        self.uploadSuccess(response);
                        return false;
                    }
                },
                renderer : avatar_upload_renderer,
                process_url  : this.upload_url
            });
        }
        
        this.crop = function(e, win, img) {	
            var origW = parseInt(img.attr('origW'));
            var origH = parseInt(img.attr('origH'));

            var ratio = origW/img.width();
            
            if(ratio != 1) {
                this.crop_coords.x1 = Math.ceil(this.crop_coords.x1*ratio);
                this.crop_coords.y1 = Math.ceil(this.crop_coords.y1*ratio);

                this.crop_coords.x2 = Math.floor(this.crop_coords.x2*ratio);

                if(this.crop_coords.x2 > origW) {
                    this.crop_coords.x2 = origW;
                }

                this.crop_coords.y2 = this.crop_coords.y1 + (this.crop_coords.x2 - this.crop_coords.x1);

                if(this.crop_coords.y2 > origH) {
                    this.crop_coords.y1 = origH - (this.crop_coords.y2-this.crop_coords.y1);
                    this.crop_coords.y2 = origH;
                }
            }
            
            var self = this;
            
            win.disable();
            
            $.ajax({
                type: "POST",
                url: this.crop_url,
                data: this.crop_coords,
                success: function(response){			
                    if(response.result == 'ERROR') {					
                        alert(response.data.message);
				
                        if(response.data.code == 1) {
                            self.cropper_script_close_flag = true;
                            win.destroy(e);
                        } else {
                            win.enable();
                        }
				
                        return false;
                    }
                    
                    self.cropper_script_close_flag = true;
                    win.destroy(e);
                    
                    self.ava_remove_btn.addClass('active');
                    self.ava_preview.attr('src', response.data);
                }
            });
        }
        
        this.cancel = function(e) {            
            if(! this.closeCropper(this.cropper_script_close_flag)) {
                return false;
            }
            
            if(! this.cropper_script_close_flag) {
                $.ajax({type: "GET", url: this.cancel_url});
            }
            
            this.cropper_script_close_flag = false;
        },
	
        this.closeCropper = function(force) {
            if(! force)	{
                if(! $.confirmAction(this.lang.confirm_close_cropper))	{
                    return false;
                }
            }

            $('#avaPreToCrop').osc_cropper('destroy');
            
            this.crop_coords = {};
			
            $.enablePage();
		
            return true;
        }
        
        this.remove = function(params) {
            if( ! $.confirmAction(this.lang.confirm_remove_ava) ){
                return false;
            }
            
            var self = this;

            this.ava_remove_btn.removeClass('active');

            $.showLoadingForm($.lang.waitForSaveData);

            $.ajax({
                type: "GET",
                url: this.remove_url,
                success: function(response){
                    $.hideLoadingForm();

                    if(response.result == 'ERROR') {					
                        alert(response.data.message);
                        self.ava_remove_btn.addClass('active');
                        return false;
                    }

                    self.ava_preview.attr('src', response.data);
                }
            });
        }
        
        if(typeof options != 'object') {
            options = {};
        }
        
        options.container = $(container);
        
        this.container = null;
        this.upload_url = null;
        this.crop_url = null;
        this.cancel_url = null;
        this.remove_url = null;
        this.ava_url = null;
        this.ava_extension = null;
        this.lang = {
            cropper_frm_title : 'Avatar cropper',
            confirm_close_cropper : 'Do you want to close cropper?',
            confirm_remove_ava : 'Do you want to delete the avatar?'
        };
        
        $.extend(this, options);
        
        this.ava_container = null;
        this.ava_preview = null;
        this.ava_remove_btn = null;
        this.uploader = null;
        this.crop_coords = {};
        this.cropper_script_close_flag = false;
                
        this.render();
    }
	
    $.fn.osc_avaUploader = function(options) {
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
               
        return this.each(function() {
            var instance = $(this).data('osc-ava-uploader');
            
            if(func) {
                if(instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if(! instance) {
                    $(this).data('osc-ava-uploader', new OSC_AvaUploader(this, opts));
                }
            }
        });
    };
})(jQuery)