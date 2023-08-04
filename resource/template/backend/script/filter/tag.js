(function ($) {
    'use strict';
    window.initTagParent = function () {
        const select = $(this);
        const tag_select_name = '#' + select.attr('id');

        $(document).ready(function () {
            $(tag_select_name).select2().change(function () {
                if (parseInt($(this).val()) === 0) {
                    $('.wrap-choose-type-tag').removeClass('hidden')
                } else {
                    $('.wrap-choose-type-tag').addClass('hidden')
                }
            });
        });
    }

    $('.tag-list-image').on('click', function() {
        const $image = $(this);

        const isHidden = $image.hasClass('hidden');
        const imageUrl = $image.data('image');
        const modalKey = 'modalPreviewTagImage';

        if (isHidden || !imageUrl) return false;

        $.unwrapContent(modalKey);

        const $modal = $(`
<div class="osc-modal md-quicklook">
    <div class="md-quicklook__header">
        <div class="md-quicklook__title">Quick Look</div>
        <button type="button" class="md-quicklook__close"></button>
    </div>
    <div class="md-quicklook__frame">
        <img src="${imageUrl}" alt="">
    </div>
</div>
        `);

        $.wrapContent($modal, { key: modalKey });

        $modal
            .css({
                top: '100px',
                width: '548px',
            })
            .moveToCenter()
            .on('click', '.md-quicklook__close', function() {
                $.unwrapContent(modalKey);
            });
    });
})(jQuery);

window.initUploadTagImage = function() {
    const $btn = $(this);

    $btn.on('click', function(e) {
        e.preventDefault();

        const modalKey = 'modalUploadTagImage';
        const id = $btn.data('id');
        const title = $btn.data('title');
        const imageValue = $btn.data('imageValue');
        const imageUrl = $btn.data('imageUrl');
        const uploadUrl = $btn.data('uploadUrl');

        $.unwrapContent(modalKey);

        const $modal = $(`
<div class="osc-modal md-tag">
    <div class="md-tag__title">Add Image<button type="button" class="md-tag__close"></button></div>
    <div class="md-tag__body">
        <div>
            <label for="input-title"><b>Title*</b></label>
            <div class="mt10">
                <input type="text" class="styled-input md-tag__input" name="title" value="${title}" />
            </div>
        </div>
        <div class="mt15">
            <label for="input-title"><b>Example Image</b></label>
            <div class="mt10">
                <div data-insert-cb="initPostFrmSidebarImageUploader"
                     class="md-tag__image frm-image-uploader"
                     data-upload-url="${uploadUrl}"
                     data-input="image"
                     data-value="${imageValue}"
                     data-image="${imageUrl}">
                </div>
            </div>
        </div>
    </div>
    <div class="md-tag__footer">
        <button type="button" class="btn btn-outline mr15 md-tag__cancel">Cancel</button>
        <button type="button" class="btn btn-secondary md-tag__apply">Apply</button>
    </div>
</div>
        `).css({
            width: 600,
        });

        $.wrapContent($modal, { key: modalKey });

        $modal.moveToCenter().css('top', '100px')
            .on('click', '.md-tag__cancel, .md-tag__close', function() {
                $.unwrapContent(modalKey);
            })
            .on('click', '.md-tag__apply', function() {
                const $this = $(this);
                const title = $modal.find('.md-tag__input').val();
                const image = $modal.find('[name="image"]').val() || '';

                if (!title) {
                    alert('Title field is required.');
                    return false;
                }

                if ($modal.find('.preview').css('background-image').indexOf('blob') >= 0) {
                    alert('Your image is in progress. Try again later.');
                    return false;
                }

                $this.addClass('disabled');

                $.ajax({
                    url: $.base_url + '/filter/tag/addImage',
                    data: {
                        hash: OSC_HASH,
                        id,
                        title,
                        image,
                    }
                }).success(function (response) {
                    const data = response.data;

                    if (data.updated) {
                        $btn.data('title', data.title);
                        $btn.data('imageValue', data.image);
                        $btn.data('imageUrl', data.image_url);

                        const $tr = $btn.closest('tr');

                        $tr.find('.tag-list-title').text(data.title);

                        const $image = $tr.find('.tag-list-image');

                        if (data.image) {
                            $image.removeClass('hidden').css({
                                backgroundImage: 'url("' + data.image_url + '")'
                            }).data('image', data.image_url);
                            $tr.addClass('tag-table-has-image');
                        } else {
                            $image.addClass('hidden').data('image', '');
                            $tr.removeClass('tag-table-has-image');
                        }
                    }

                    $.unwrapContent(modalKey);
                }).error(function (request, status, error) {
                    alert(request.responseText);
                    $this.removeClass('disabled');
                })

            })
            .find('[data-insert-cb]').each(function () {
                const cb = $(this).data('insert-cb');
                if (typeof window[cb] === 'function') {
                    window[cb].bind(this);
                }
            });
    });
}
