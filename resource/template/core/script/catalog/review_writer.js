(function ($) {
    window.initCatalogReviewWriter = function () {
        var frm = $(this);

        frm.find('.vote-item').click(function () {
            frm.attr('data-vote', this.getAttribute('data-value'));
            frm.find('.vote-item').removeClass('selected');
            $(this).addClass('selected');
            frm.attr('data-section', 'upload');
        });

        frm.find('.back-btn').click(function () {
            frm.attr('data-section', $(this).closest('section').prev('section').attr('data-section'));
        });

        frm.find('.next-btn').click(function () {
            frm.attr('data-section', $(this).closest('section').next('section').attr('data-section'));
        });

        var uploader = frm.find('.uploader-btn');
        var preview = frm.find('.uploader-preview');

        uploader.osc_uploader({
            max_files: 1,
            process_url: frm.attr('data-upload-url'),
            btn_content: '',
            dragdrop_content: 'Drop here to upload',
            extensions: ['png', 'jpg', 'gif'],
            xhrFields: {withCredentials: true},
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-OSC-Cross-Request': 'OK'
            }
        }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
            $(".uploader-error").text("");
            $(".uploader-error").css("display","none");
            uploader.hide();
            frm.attr('data-photo', '');
            frm.attr('disabled', 'disabled');
            uploader.closest('.upload-frm').attr('disabled', 'disabled');
            uploader.closest('[data-section]').find('.next-btn').text('Skip');
        }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
            try {
                response = JSON.parse(response);
            } catch (e) {
                return;
            }

            frm.removeAttr('disabled');
            $(".uploader-error").text("");
            $(".uploader-error").css("display","none");

            if (response.result === 'OK') {
                frm.attr('data-photo', response.data.file);
                frm.attr('data-section', 'write');
                uploader.closest('[data-section]').find('.next-btn').text('Next');
            } else {
                uploader.show();
                uploader.closest('.upload-frm').removeAttr('disabled');
                uploader.closest('[data-section]').find('.next-btn').text('Skip');
            }
        }).bind('uploader_cancel', function (e, file_id, error_code, error_message) {
            uploader.show();
            frm.attr('data-photo', '');
            frm.removeAttr('disabled');
            uploader.closest('.upload-frm').removeAttr('disabled');
            uploader.closest('[data-section]').find('.next-btn').text('Skip');
        }).bind('uploader_upload_error', function(e, file_id, error_code, error_message){
            var errorNotification = function(){
                $(".uploader-error").text("Upload Error! Only images with these formats png, jpg, gif are supported");
                $(".uploader-error").css({"display":"block","color":"#EB5757","font-size": "14px", "padding-top":"14px", "font-weight":"100","font-style": "normal"});
            }
            errorNotification();
        });

        initFileUploadHandler(uploader, preview);

        frm.find('textarea').keydown(function () {
            $(this).removeClass('error');
        });

        frm.find('.submit-btn').click(function () {
            if (frm.attr('disabled') === 'disabled') {
                return;
            }

            var post_data = {
                images: frm.attr('data-photo'),
                vote: parseInt(frm.attr('data-vote')),
                review: frm.find('textarea').val().trim()
            };
            
            if (isNaN(post_data.vote) || post_data.vote < 1 || post_data.vote > 5) {
                frm.attr('data-section', 'vote');
                return;
            }

            if (post_data.review === '') {
                frm.find('textarea').addClass('error')[0].focus();
                return;
            }

            frm.attr('disabled', 'disabled');

            $.ajax({
                url: frm.attr('data-process-url'),
                data: post_data,
                success: function (response) {
                    frm.removeAttr('disabled');

                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    frm.attr('data-section', 'finish');

                    if (response.data.discount_code) {
                        frm.find('[data-section="finish"] .discount-code .desc .discount-value').text(response.data.discount_code.value);
                        frm.find('[data-section="finish"] .discount-code .code').text(response.data.discount_code.code.replace(/^(.{4})(.{4})(.{4})$/, '$1-$2-$3'));
                        frm.find('[data-section="finish"] .discount-code .expire .expire-date').text(response.data.discount_code.expire_date);
                    } else {
                        frm.find('[data-section="finish"] .discount-code').remove();
                    }
                }
            });
        });
    };
})(jQuery);