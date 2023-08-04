<script type="text/template" id="option-template">
    <div class="form-option js-option" data-option-id="{{id}}">
        <div class="form-option-header">
            <div class="form-option-label">Option 01</div>
            <?php if ($params['mode'] !== 'view'): ?>
                <button class="form-option-delete js-option-delete" type="button">Delete</button>
            <?php endif; ?>
        </div>
        <div class="form-input">
            <div class="styled-radio">
                <input type="radio" id="option_make_default_{{id}}" name="option_default" data-name="is_default" class="js-option-set-default js-option-input" />
                <ins></ins>
            </div>
            <label class="label-inline form-input-label" for="option_make_default_{{id}}">Set as default</label>
        </div>
        <div class="form-input">
            <label class="title form-input-label">Title<span class="text-danger">*</span></label>
            <input type="text" class="styled-input js-option-input" data-name="title" value="{{title}}" placeholder="example" maxlength="80" />
            <div class="form-input-error"></div>
        </div>
        <div class="form-input">
            <label class="title form-input-label">Price<span class="text-danger">*</span> <span class="variant-price-note"></span></label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" class="styled-input js-option-input" data-name="price" value="{{price}}" min="0" placeholder="5.00" step="any" maxlength="80" data-format="price" />
            </div>
            <div class="form-input-error"></div>
        </div>
        <div class="form-input" <?php if ($params['mode'] === 'view'): ?>style="pointer-events: none;"<?php endif; ?>>
            <label class="title form-input-label field field-addon">Thumbnail/ Image<span class="text-danger">*</span></label>
            <label class="title form-input-label field field-variant">Thumbnail/ Image (Optional)</label>
            <div data-insert-cb="initAddonImageUploader"
                 class="addon-uploader js-option-image-uploader js-option-input"
                 data-name="image"
                 data-upload-url="<?= $this->getUrl('addon/backend_service/uploadImage') ?>"
                 data-input="option_image"
                 data-value="{{image}}"
                 data-image="{{image_url}}">
            </div>
            <div class="form-input-error"></div>
        </div>
    </div>
</script>
