   function generateSlug(input) {
       var title = document.getElementById('input-title');
       var topic = document.getElementById('input-vendor');
       var inp_seo_title = document.getElementById('input-seo-title');
       var meta_title  = '';

       if(!title.value.trim()){
           return false;
       }

       if(topic && topic.value) {
           meta_title = meta_title.concat(topic.value.trim()) + ' - ';
       }

       inp_seo_title.value = meta_title.concat(title.value.trim());

       title_generate_slug();
    }

    function title_generate_slug() {
        var inp_seo_title = document.getElementById('input-seo-title');

        var slug = removeInvalidCharacters(inp_seo_title.value.toLowerCase())
        var $id_item = document.getElementById('input-id-item').value;
        var current_meta_slug = document.getElementById('input-id-seo_slug').value;

        if ($id_item != 0 && current_meta_slug){
            if (inp_seo_title.value.length < 60) {
                document.getElementById('warnning_character_title').innerHTML = 'The current length (' + inp_seo_title.value.length + ').The ideal length of meta seo title is 60 - 80';
                document.getElementById('warnning_character_title').style.display = "block";
            } else if (inp_seo_title.value.length >= 80) {
                document.getElementById('warnning_character_title').innerHTML = 'The current length (' + inp_seo_title.value.length + ').The ideal length of meta seo title is 60 - 80';
                document.getElementById('warnning_character_title').style.display = "block";
            } else if (inp_seo_title.value.length >= 60 && inp_seo_title.value.length < 80) {
                document.getElementById('warnning_character_title').innerHTML = '';
                document.getElementById('warnning_character_title').style.display = "none";
            }
            return false;
        }
        if (inp_seo_title.value.length < 60) {
            document.getElementById('warnning_character_title').innerHTML = 'The current length (' + inp_seo_title.value.length + ').The ideal length of meta seo title is 60 - 80';
            document.getElementById('warnning_character_title').style.display = "block";
        } else if (inp_seo_title.value.length >= 80) {
            document.getElementById('warnning_character_title').innerHTML = 'The current length (' + inp_seo_title.value.length + ').The ideal length of meta seo title is 60 - 80';
            document.getElementById('warnning_character_title').style.display = "block";
        } else if (inp_seo_title.value.length >= 60 && inp_seo_title.value.length < 80) {
            document.getElementById('warnning_character_title').innerHTML = '';
            document.getElementById('warnning_character_title').style.display = "none";
        }
        document.getElementById('input-seo-slug').value = slug;
    }

   function removeInvalidCharacters(input) {

       if (input) {
           input = cleanVNMask(input).replace(/[^a-zA-Z0-9\-|]/g, '-').trim()
               .replace(/\.{2,}|-{2,}|_{2,}/g, '-')
               .replace(/^[\.\-\_]+|[\.\-\_]+$/g, '');
       }
       return input;
   }

    function description_length(){
        var inp_seo_description = document.getElementById('input-seo-description');
        if (inp_seo_description.value.length < 160){
            document.getElementById('warning_character_description').innerHTML = 'The current length ('+ inp_seo_description.value.length +').The ideal length of meta seo description is 160 - 300';
            document.getElementById('warning_character_description').style.display = "block";
        }else if (inp_seo_description.value.length > 300){
            document.getElementById('warning_character_description').innerHTML = 'The current length ('+ inp_seo_description.value.length +').The ideal length of meta seo description is 160 - 300';
            document.getElementById('warning_character_description').style.display = "block";
        }else if (inp_seo_description.value.length >= 160 && inp_seo_description.value.length <= 300) {
            document.getElementById('warning_character_description').innerHTML = '';
            document.getElementById('warning_character_description').style.display = "none";
        }
    }

    window.initPostFrmMetaImageUploader = function () {
        var container = $(this);
        var max_filesize = 10*1024*1024;

        container.addClass('meta-image-uploader');

        var preview = $('<div />').addClass('preview').appendTo(container);
        var image_url = container.attr('data-image');
        var input_name = container.attr('data-input');
        if (image_url !== '') {
            preview.find('svg').hide();

            preview.css('background-image', 'url(' + image_url + ')');

            $('<input />').attr({
                type: 'hidden',
                name: input_name,
                value: container.attr('data-value')
            }).appendTo(preview);
        }

        var uploader_container = $('<div />').addClass('mt10 btn btn-primary p0').appendTo(container);

        var __initRemoveBtn = function () {
            uploader_container.find('.image-uploader').hide();
            uploader_container.find('.remove-btn').remove();

            $('<div />').addClass('btn btn-danger remove-btn').appendTo(uploader_container).text('Remove image').click(function () {
                preview.removeAttr('file-id');
                preview.removeAttr('data-uploader-step');
                image_url = '';
                preview.find('.step').remove();
                preview.find('.uploader-progress-bar').remove();
                preview.css('background-image', 'initial');
                preview.find('input').remove();

                preview.find('svg').removeAttr('style');

                __initUploader();
            });
        };

        var __initUploader = function () {
            uploader_container.find('.remove-btn').hide();
            uploader_container.find('.image-uploader').remove();

            var uploader = $('<div />').addClass('image-uploader').appendTo(uploader_container);

            uploader.osc_uploader({
                max_files: 1,
                process_url: container.attr('data-upload-url'),
                btn_content: 'Upload image',
                dragdrop_content: 'Drop here to upload',
                max_filesize: max_filesize,
                image_mode: true,
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                uploader.hide();
                preview.find('svg').hide();

                __initRemoveBtn();

                preview.attr('file-id', file_id).attr('data-uploader-step', 'queue');

                $('<div />').addClass('uploader-progress-bar').appendTo(preview).append($('<div />'));
                $('<div />').addClass('step').appendTo(preview);

                var reader = new FileReader();
                reader.onload = function (e) {
                    if (preview.attr('file-id') !== file_id) {
                        return;
                    }

                    var img = document.createElement('img');

                    img.onload = function () {
                        var canvas = document.createElement('canvas');

                        var MAX_WIDTH = 400;
                        var MAX_HEIGHT = 400;

                        var width = img.width;
                        var height = img.height;

                        if (width > height) {
                            if (width > MAX_WIDTH) {
                                height *= MAX_WIDTH / width;
                                width = MAX_WIDTH;
                            }
                        } else {
                            if (height > MAX_HEIGHT) {
                                width *= MAX_HEIGHT / height;
                                height = MAX_HEIGHT;
                            }
                        }

                        canvas.width = width;
                        canvas.height = height;

                        var ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob(function (blob) {
                            preview.css('background-image', 'url(' + URL.createObjectURL(blob) + ')');
                        });
                    };

                    img.src = e.target.result;
                };

                reader.readAsDataURL(file);
            }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
                if (preview.attr('file-id') !== file_id) {
                    return;
                }

                if (parseInt(uploaded_percent) === 100) {
                    preview.attr('data-uploader-step', 'process');
                } else {
                    preview.attr('data-uploader-step', 'upload');
                    preview.find('.uploader-progress-bar > div').css('width', uploaded_percent + '%');
                }

            }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                if (preview.attr('file-id') !== file_id) {
                    return;
                }

                eval('response = ' + response);

                preview.removeAttr('file-id');
                preview.removeAttr('data-uploader-step');
                preview.find('.step').remove();
                preview.find('.uploader-progress-bar').remove();

                if (response.result !== 'OK') {
                    preview.find('svg').removeAttr('style');
                    preview.css('background-image', image_url !== '' ? ('url(' + image_url + ')') : 'initial');
                    alert(response.message);

                    __initUploader();

                    return;
                }

                preview.css('background-image', 'url(' + response.data.url + ')');

                image_url = response.data.url;
                preview.find('input').remove();

                $('<input />').attr({type: 'hidden', name: input_name, value: response.data.file}).appendTo(preview);
            }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                if (error_code === 'maxSizeError' || file_id === 'sizeError') {
                    alert('File size exceeds the maximum limit of 10MB, please upload a smaller file.');
                } else if (file_id === 'typeError') {
                    alert('Sorry! The image file format you uploaded is not supported.');
                } else {
                    alert('Upload error, please try again');
                }

                if (preview.attr('file-id') !== file_id) {
                    return;
                }

                __initUploader();

                preview.find('svg').removeAttr('style');
                preview.removeAttr('file-id');
                preview.removeAttr('data-uploader-step');
                preview.find('.step').remove();
                preview.find('.uploader-progress-bar').remove();
                preview.css('background-image', image_url !== '' ? ('url(' + image_url + ')') : 'initial');

                alert('Có vấn đề xảy ra trong quá trình upload file, xin hãy thử lại');
            });
        };

        if (image_url !== '') {
            __initRemoveBtn();
        } else {
            __initUploader();
        }
    };
