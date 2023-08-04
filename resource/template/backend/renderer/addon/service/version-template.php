<script type="text/template" id="version-template">
    <div class="addon-version js-version" data-version-id="{{id}}">
        <div class="addon-version__header">
            <div class="addon-version__title">{{title}}</div>
            <?php if ($params['mode'] !== 'view'): ?>
                <button class="addon-version__remove js-version-delete" type="button" data-version-id="{{id}}">Remove</button>
            <?php endif; ?>
        </div>

        <div class="form-input">
            <div class="mb0">
                <div class="styled-radio">
                    <input class="js-version-input" id="is_default_version_{{id}}" type="radio" data-name="is_default_version" value="1" /><ins></ins>
                </div>
                <label for="is_default_version_{{id}}" class="ml5 label-inline">Default Version</label>
            </div>
        </div>

        <div class="form-input">
            <div class="mb0">
                <div class="styled-checkbox">
                    <input class="js-version-input" id="is_hide_{{id}}" type="checkbox" data-name="is_hide" />
                    <ins><?= $this->getIcon('check-solid') ?></ins>
                </div>
                <label for="is_hide_{{id}}" class="label-inline form-input-label">Hide this version</label>
            </div>
        </div>

        <div id="form-options">
            <div class="form-input">
                <label class="title form-input-label field field-addon">Service Option Group Title<span class="text-danger">*</span></label>
                <label class="title form-input-label field field-variant">Add-on Section Title<span class="text-danger">*</span></label>
                <input class="styled-input js-version-input" type="text" data-name="service_title" value="{{service_title}}" placeholder="example" maxlength="80" />
                <div class="form-input-error"></div>
            </div>

            <div class="form-input field field-addon js-extend-parent">
                <div class="form-input mb0">
                    <div class="styled-checkbox">
                        <input class="js-extend-toggle js-version-input" id="addon-same-price-enable" type="checkbox" data-name="enable_same_price" />
                        <ins><?= $this->getIcon('check-solid') ?></ins>
                    </div>
                    <label class="label-inline form-input-label" for="addon-same-price-enable">Options in this group have the same price</label>
                </div>

                <div class="form-input js-extend-field hidden mt15 same-price-enable">
                    <label class="title form-input-label">Price<span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input class="styled-input js-version-input" type="number" data-name="group_price" min="0" value="{{group_price}}" placeholder="5.00" step="any" maxlength="80" data-format="price" disabled />
                    </div>
                    <div class="form-input-error"></div>
                </div>
            </div>

            <div class="form-input field field-addon js-extend-parent">
                <div class="form-input mb0">
                    <div class="styled-checkbox">
                        <input class="js-extend-toggle js-version-input" id="show_message_{{id}}" type="checkbox" data-name="show_message" />
                        <ins><?= $this->getIcon('check-solid') ?></ins>
                    </div>
                    <label class="label-inline form-input-label" for="show_message_{{id}}">Add Message Field</label>
                </div>

                <div class="form-input js-extend-field hidden mt10 mb0">
                    <label class="title form-input-label">Message Field Description</label>
                    <textarea class="styled-textarea js-version-input" rows="2" placeholder="Write your gift message here" data-name="placeholder" maxlength="300">{{placeholder}}</textarea>
                </div>
            </div>

            <div class="form-input field field-addon">
                <label class="title form-input-label">Option Description</label>
                <textarea class="styled-textarea js-version-input" rows="2" placeholder="Write your description here" data-name="description" maxlength="300" >{{description}}</textarea>
            </div>

            <div class="form-input">
                <label class="title form-input-label field field-addon">Set Options</label>
                <label class="title form-input-label field field-variant">Set Variant Options</label>
                <div class="form-option-container">
                    <div class="form-option-list js-option-list"></div>
                    <?php if ($params['mode'] !== 'view'): ?>
                        <button class="btn btn-primary add-new-option js-option-add" type="button"><?= $this->getIcon('plus', ['class' => 'mr10']) ?>Add New Option</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="delimiter field field-addon"></div>

        <div class="field field-addon">
            <div class="form-input mb25">
                <label class="title form-input-label">Display Area</label>
                <div class="styled-radio">
                    <input type="radio"
                           id="display_area_cart_{{id}}"
                           name="display_area"
                           data-name="display_area"
                           class="js-version-input"
                           value="<?= Model_Addon_Service::DISPLAY_CART_ONLY; ?>" />
                    <ins></ins>
                </div>
                <label class="label-inline" for="display_area_cart_{{id}}">Cart Only</label>
                <div class="styled-radio" style="margin-left: 30px;">
                    <input type="radio"
                           id="display_area_product_detail_{{id}}"
                           name="display_area"
                           data-name="display_area"
                           class="js-version-input"
                           value="<?= Model_Addon_Service::DISPLAY_CART_AND_PRODUCT; ?>" />
                    <ins></ins>
                </div>
                <label class="label-inline" for="display_area_product_detail_{{id}}">Product Page + Cart</label>
            </div>

            <div class="form-input field field-addon mb25">
                <label class="title form-input-label">Selection</label>
                <div class="styled-checkbox">
                    <input class="js-version-input" id="auto_select" type="checkbox" data-name="auto_select" value="1" />
                    <ins><?= $this->getIcon('check-solid') ?></ins>
                </div>
                <label class="label-inline" for="auto_select">Automatically select when user views product</label>
            </div>

            <div class="form-input field field-addon">
                <label class="title form-input-label">Mockup Image</label>
                <div class="image-uploader mt15 js-image-uploader js-version-input"
                     data-name="images"
                     data-insert-cb="initImageUploader"
                     data-images=''
                     data-process-url="/core/backend/uploadMedia"
                     data-position="input"
                ></div>
            </div>

            <div class="form-input field field-addon">
                <label class="title form-input-label">Mockup Video</label>
                <div class="video-uploader mt0 js-video-uploader js-version-input"
                     data-name="videos"
                     data-insert-cb="initVideoUploader"
                     data-max-size="<?= $params['max_video_size'] ?>"
                     data-videos=''
                     data-process-url="/core/backend/uploadMedia"
                     data-position="input"
                ></div>
                <div class="form-input-error mt0"></div>
            </div>
        </div>
    </div>
</script>
