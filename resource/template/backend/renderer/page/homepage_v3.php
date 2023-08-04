<?php
/* @var $this Helper_Backend_Template */
?>

<?php $this->addComponent('datePicker', 'timePicker');
$this->push(
    [
        'vendor/bootstrap/bootstrap-grid.min.css',
        'frontend/backend.scss',
        'page/homepage_v3.scss',
    ], 'css');
$this->push([
    'page/homepage_v3.js'
], 'js');

$homepage_v3_data = $params['homepage_v3_data'];
$catalog_collections = $params['catalog_collections'];
?>
<form action="<?php echo $this->getUrl('*/*/*'); ?>" method="post"
      class="post-frm p25 post-form-homepage-v3">


    <div class="post-frm-grid backend-sections-form post-frm">
        <div class="post-frm-grid__main-col">
            <div class="block mt15 mb15">
                <div class="header">
                    <div class="header__main-group">
                        <h3 class="m-0">Clear cache Homepage V3</h3>
                    </div>
                </div>
                <div class="p20" style="padding-top: 0 !important;">
                    <div class="row">
                        <div class="col-12 frm-grid frm-grid--separate">
                            <div class="setting-item">
                                <div>
                                    <a class="btn btn-danger" href="<?php echo $this->getUrl('*/*/clearCache'); ?>">Clear cache</a>
                                </div>
                                <div class="input-desc font-weight--normal">
                                    If you changed any option but home page not apply the change, you may clear cache for home page to quick apply the change !
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="section-item" data-name="[#1] Banner slider">
                <h3 data-insert-cb="initCollapseSection">
                    [#1] Banner slider
                    <span class="x-minus"></span>
                </h3>
                <div class="config-generate">
                    <div class="block mt15">
                        <div class="header">
                            <div class="header__main-group">
                                <div class="input-desc font-weight--normal">
                                    Enter title, url and upload banner for PC and Mobile
                                </div>
                            </div>
                        </div>
                        <div class="p20" style="padding-top: 0 !important;">
                            <?php
                            $section_banners = $homepage_v3_data['banner'] ??
                                [
                                    'default' => [
                                        'title' => '',
                                        'url' => '',
                                        'images' => [
                                            'pc' => '',
                                            'mobile' => ''
                                        ]
                                    ]
                                ];
                            ?>
                            <div class="hero-items">
                                <?php foreach ($section_banners as $key => $item): ?>
                                    <div class="row line-top p-2 item-banner-slider">
                                        <div class="col-4">
                                            <h3>Banner info</h3>
                                            <div class="input-desc font-weight--normal">Banner title</div>
                                            <input type="text"
                                                   name="homepage_v3[banner][<?= $key ?>][title]"
                                                   class="styled-input mb10"
                                                   placeholder="Title"
                                                   value="<?= $homepage_v3_data['banner'][$key]['title'] ?>">
                                            <div class="input-desc font-weight--normal">Banner URL</div>
                                            <input type="text"
                                                   name="homepage_v3[banner][<?= $key ?>][url]"
                                                   class="styled-input mb10"
                                                   placeholder="URL"
                                                   value="<?= $homepage_v3_data['banner'][$key]['url'] ?>">
                                            <button class="btn btn-danger"
                                                    onclick="$(this).closest('div.row.item-banner-slider').remove()"><?= $this->getIcon('trash-alt-regular') ?></button>
                                        </div>
                                        <div class="col-4">
                                            <div class="input-desc font-weight--normal">Upload banner for PC</div>
                                            <div data-insert-cb="initPostFrmSidebarImageUploader"
                                                 class="frm-image-uploader"
                                                 data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                                 data-input="homepage_v3[banner][<?= $key ?>][images][pc]"
                                                 data-value="<?= $homepage_v3_data['banner'][$key]['images']['pc'] ?>"
                                                 data-image="<?= isset($homepage_v3_data['banner'][$key]['images']['pc']) ? OSC::core('aws_s3')->getStorageUrl($homepage_v3_data['banner'][$key]['images']['pc']) : '' ?>">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="input-desc font-weight--normal">Upload banner for Mobile</div>
                                            <div data-insert-cb="initPostFrmSidebarImageUploader"
                                                 class="frm-image-uploader"
                                                 data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                                 data-input="homepage_v3[banner][<?= $key ?>][images][mobile]"
                                                 data-value="<?= $homepage_v3_data['banner'][$key]['images']['mobile'] ?>"
                                                 data-image="<?= isset($homepage_v3_data['banner'][$key]['images']['mobile']) ? OSC::core('aws_s3')->getStorageUrl($homepage_v3_data['banner'][$key]['images']['mobile']) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="btn btn-primary mt10 add-banner-slider line-top"
                                 data-insert-cb="initFormRepeater" data-items=".hero-items"
                                 data-template="#item-banner-slider-template">
                                <?= $this->getIcon('icon-plus', ['class' => 'mr5']) ?> Add new banner
                            </div>
                            <script type="text/template" id="item-banner-slider-template">
                                <div class="row line-top p-2 item-banner-slider">
                                    <div class="col-4">
                                        <h3>Banner info</h3>
                                        <div class="input-desc font-weight--normal">Banner title</div>
                                        <input type="text"
                                               name="homepage_v3[banner][{key}][title]"
                                               class="styled-input mb10"
                                               placeholder="Title"
                                               value="">
                                        <div class="input-desc font-weight--normal">Banner URL</div>
                                        <input type="text"
                                               name="homepage_v3[banner][{key}][url]"
                                               class="styled-input mb10"
                                               placeholder="URL"
                                               value="">
                                        <button class="btn btn-danger"
                                                onclick="$(this).closest('div.row.item-banner-slider').remove()"><?= $this->getIcon('trash-alt-regular') ?></button>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-desc font-weight--normal">Upload banner for PC</div>
                                        <div data-insert-cb="initPostFrmSidebarImageUploader"
                                             class="frm-image-uploader"
                                             data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                             data-input="homepage_v3[banner][{key}][images][pc]"
                                             data-value=""
                                             data-image="">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-desc font-weight--normal">Upload banner for Mobile</div>
                                        <div data-insert-cb="initPostFrmSidebarImageUploader"
                                             class="frm-image-uploader"
                                             data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                             data-input="homepage_v3[banner][{key}][images][mobile]"
                                             data-value=""
                                             data-image="">
                                        </div>
                                    </div>
                                </div>
                            </script>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-item" data-name="[#2] Section Explore Popular Collections">
                <h3 data-insert-cb="initCollapseSection">
                    [#2] Section Explore Popular Collections
                    <span class="x-minus"></span>
                </h3>
                <div class="config-generate">
                    <div class="block mt15">
                        <div class="p20 mt15" style="padding-top: 0 !important;">
                            <div class="row">
                                <div class="col-12">
                                    <div class="setting-item">
                                        <div class="title">Section title</div>
                                        <div>
                                            <input type="text" name="homepage_v3[popular_collection][title]" class="styled-input"
                                                   value="<?= $homepage_v3_data['popular_collection']['title'] ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="header py-3">
                                <div class="header__main-group">
                                    <div class="input-desc font-weight--normal">
                                        Enter title, url and upload image for PC and Mobile for each item
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <?php foreach (range(1, 4) as $key => $item): ?>
                                    <div class="col-3">
                                        <input type="text"
                                               name="homepage_v3[popular_collection][items][<?= $key ?>][title]"
                                               class="styled-input mb10"
                                               placeholder="Title"
                                               value="<?= $homepage_v3_data['popular_collection']['items'][$key]['title'] ?>">
                                        <input type="text"
                                               name="homepage_v3[popular_collection][items][<?= $key ?>][url]"
                                               class="styled-input mb10"
                                               placeholder="URL"
                                               value="<?= $homepage_v3_data['popular_collection']['items'][$key]['url'] ?>">
                                        <div class="input-desc font-weight--normal mt-3">
                                            Upload image for PC
                                        </div>
                                        <div data-insert-cb="initPostFrmSidebarImageUploader"
                                             class="frm-image-uploader"
                                             data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                             data-input="homepage_v3[popular_collection][items][<?= $key ?>][images][pc]"
                                             data-value="<?= $homepage_v3_data['popular_collection']['items'][$key]['images']['pc'] ?>"
                                             data-image="<?= isset($homepage_v3_data['popular_collection']['items'][$key]['images']['pc']) ? OSC::core('aws_s3')->getStorageUrl($homepage_v3_data['popular_collection']['items'][$key]['images']['pc']) : '' ?>">
                                        </div>
                                        <div class="input-desc font-weight--normal mt-3">
                                            Upload image for Mobile
                                        </div>
                                        <div data-insert-cb="initPostFrmSidebarImageUploader"
                                             class="frm-image-uploader"
                                             data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                             data-input="homepage_v3[popular_collection][items][<?= $key ?>][images][mobile]"
                                             data-value="<?= $homepage_v3_data['popular_collection']['items'][$key]['images']['mobile'] ?>"
                                             data-image="<?= isset($homepage_v3_data['popular_collection']['items'][$key]['images']['mobile']) ? OSC::core('aws_s3')->getStorageUrl($homepage_v3_data['popular_collection']['items'][$key]['images']['mobile']) : '' ?>">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-item" data-name="[#3] Sales Campaign">
                <h3 data-insert-cb="initCollapseSection">
                    [#3] Sales Campaign
                    <span class="x-minus"></span>
                </h3>
                <div class="config-generate">
                    <div class="block mt15">
                        <div class="header">
                            <div class="header__main-group">
                                <div class="input-desc font-weight--normal">
                                    Enter section title and video config
                                </div>
                            </div>
                        </div>
                        <div class="p20" style="padding-top: 0 !important;">
                            <div class="row">
                                <div class="col-12 frm-grid frm-grid--separate">
                                    <div class="setting-item">
                                        <div class="title">Banner url</div>
                                        <div>
                                            <input type="text" name="homepage_v3[sales_campaign][banner_url]"
                                                   class="styled-input"
                                                   value="<?= $homepage_v3_data['sales_campaign']['banner_url'] ?>">
                                        </div>
                                    </div>
                                    <div class="setting-item">
                                        <div class="title">On/Off section</div>
                                        <div>
                                            <input type="checkbox" name='homepage_v3[sales_campaign][on_off]' value='<?= $homepage_v3_data['sales_campaign']['on_off'];?>' <?= $homepage_v3_data['sales_campaign']['on_off'] == 1 ? 'checked' : '';?>
                                            data-insert-cb="initSwitcher">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-6">
                                    <div class="input-desc font-weight--normal">Upload banner for PC</div>
                                    <div data-insert-cb="initPostFrmSidebarImageUploader"
                                         class="frm-image-uploader"
                                         data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                         data-input="homepage_v3[sales_campaign][images][pc]"
                                         data-value="<?= $homepage_v3_data['sales_campaign']['images']['pc']; ?>"
                                         data-image="<?= isset($homepage_v3_data['sales_campaign']['images']['pc']) ? OSC::core('aws_s3')->getStorageUrl($homepage_v3_data['sales_campaign']['images']['pc']) : '' ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-desc font-weight--normal">Upload banner for Mobile</div>
                                    <div data-insert-cb="initPostFrmSidebarImageUploader"
                                         class="frm-image-uploader"
                                         data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                         data-input="homepage_v3[sales_campaign][images][mobile]"
                                         data-value="<?= $homepage_v3_data['sales_campaign']['images']['mobile']; ?>"
                                         data-image="<?= isset($homepage_v3_data['sales_campaign']['images']['mobile']) ? OSC::core('aws_s3')->getStorageUrl($homepage_v3_data['sales_campaign']['images']['mobile']) : '' ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-item" data-name="[#4] Section Shop Our Most Popular Categories">
                <h3 data-insert-cb="initCollapseSection">
                    [#4] Section Shop Our Most Popular Categories
                    <span class="x-minus"></span>
                </h3>
                <div class="config-generate">
                    <div class="block mt15">
                        <div class="p20 mt15" style="padding-top: 0 !important;">
                            <div class="row">
                                <div class="col-12">
                                    <div class="setting-item">
                                        <div class="title">Section title</div>
                                        <div>
                                            <input type="text" name="homepage_v3[popular_categories][title]" class="styled-input"
                                                   value="<?= $homepage_v3_data['popular_categories']['title'] ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p20" style="padding-top: 0 !important;">
                            <?php
                            $popular_cat_data = $homepage_v3_data['popular_categories']['items'] ?? [
                                    'default' => [
                                        'title' => '',
                                        'url' => '',
                                        'images' => [
                                            'pc' => '',
                                            'mobile' => ''
                                        ]
                                    ]
                                ];;
                            ?>
                            <div class="popular_category_items">
                                <?php foreach ($popular_cat_data as $key => $item): ?>
                                    <div class="row frm-grid line-top item-cat-popular py-3">
                                        <div class="col-4">
                                            <div class="input-desc font-weight--normal">
                                                Category title
                                            </div>
                                            <input type="text"
                                                   name="homepage_v3[popular_categories][items][<?= $key ?>][title]"
                                                   class="styled-input mb10"
                                                   placeholder="Title"
                                                   value="<?= $homepage_v3_data['popular_categories']['items'][$key]['title'] ?>">
                                            <div class="input-desc font-weight--normal">
                                                Category URL
                                            </div>
                                            <input type="text"
                                                   name="homepage_v3[popular_categories][items][<?= $key ?>][url]"
                                                   class="styled-input mb10"
                                                   placeholder="URL"
                                                   value="<?= $homepage_v3_data['popular_categories']['items'][$key]['url'] ?>">
                                            <button class="btn btn-danger"
                                                    onclick="$(this).closest('div.row.item-cat-popular').remove()"><?= $this->getIcon('trash-alt-regular') ?></button>
                                        </div>
                                        <div class="col-4">
                                            <div class="input-desc font-weight--normal">
                                                Upload image for PC
                                            </div>
                                            <div data-insert-cb="initPostFrmSidebarImageUploader"
                                                 class="frm-image-uploader"
                                                 data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                                 data-input="homepage_v3[popular_categories][items][<?= $key ?>][images][pc]"
                                                 data-value="<?= $homepage_v3_data['popular_categories']['items'][$key]['images']['pc'] ?>"
                                                 data-image="<?= isset($homepage_v3_data['popular_categories']['items'][$key]['images']['pc']) ? OSC::core('aws_s3')->getStorageUrl($homepage_v3_data['popular_categories']['items'][$key]['images']['pc']) : '' ?>">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="input-desc font-weight--normal">
                                                Upload image for Mobile
                                            </div>
                                            <div data-insert-cb="initPostFrmSidebarImageUploader"
                                                 class="frm-image-uploader"
                                                 data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                                 data-input="homepage_v3[popular_categories][items][<?= $key ?>][images][mobile]"
                                                 data-value="<?= $homepage_v3_data['popular_categories']['items'][$key]['images']['mobile'] ?>"
                                                 data-image="<?= isset($homepage_v3_data['popular_categories']['items'][$key]['images']['mobile']) ? OSC::core('aws_s3')->getStorageUrl($homepage_v3_data['popular_categories']['items'][$key]['images']['mobile']) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="btn btn-primary mt10 add-category-item line-top"
                                 data-insert-cb="initFormRepeater" data-items=".popular_category_items"
                                 data-template="#item-popular-cat-template">
                                <?= $this->getIcon('icon-plus', ['class' => 'mr5']) ?> Add new category
                            </div>
                            <script type="text/template" id="item-popular-cat-template">
                                <div class="row frm-grid line-top item-cat-popular py-3">
                                    <div class="col-4">
                                        <div class="input-desc font-weight--normal">
                                            Category title
                                        </div>
                                        <input type="text"
                                               name="homepage_v3[popular_categories][items][{key}][title]"
                                               class="styled-input mb10"
                                               placeholder="Title"
                                               value="">
                                        <div class="input-desc font-weight--normal">
                                            Category URL
                                        </div>
                                        <input type="text"
                                               name="homepage_v3[popular_categories][items][{key}][url]"
                                               class="styled-input mb10"
                                               placeholder="URL"
                                               value="">
                                        <button class="btn btn-danger"
                                                onclick="$(this).closest('div.row.item-cat-popular').remove()"><?= $this->getIcon('trash-alt-regular') ?></button>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-desc font-weight--normal">
                                            Upload image for PC
                                        </div>
                                        <div data-insert-cb="initPostFrmSidebarImageUploader"
                                             class="frm-image-uploader"
                                             data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                             data-input="homepage_v3[popular_categories][items][{key}][images][pc]"
                                             data-value=""
                                             data-image="">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-desc font-weight--normal">
                                            Upload image for Mobile
                                        </div>
                                        <div data-insert-cb="initPostFrmSidebarImageUploader"
                                             class="frm-image-uploader"
                                             data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                             data-input="homepage_v3[popular_categories][items][{key}][images][mobile]"
                                             data-value=""
                                             data-image="">
                                        </div>
                                    </div>
                                </div>
                            </script>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-item" data-name="[#5] Section Collection">
                <h3 data-insert-cb="initCollapseSection">
                    [#5] Section Collection
                    <span class="x-minus"></span>
                </h3>
                <div class="config-generate">

                    <div class="block mt15">
                        <div class="header">
                            <div class="header__main-group">
                                <div class="input-desc font-weight--normal">
                                    Select one catalog collection
                                </div>
                            </div>
                        </div>
                        <div class="p20" style="padding-top: 0 !important;">
                            <div class="row">
                                <div class="col-3">
                                    <div class="input-desc font-weight--normal">
                                        Select collection
                                    </div>
                                    <div class="styled-select">
                                        <select name="homepage_v3[collection][collection_id]">
                                            <?php foreach ($catalog_collections as $catalog_collection): ?>
                                                <option value="<?= $catalog_collection->data['collection_id'] ?>"
                                                    <?= ($homepage_v3_data['collection']['collection_id'] == $catalog_collection->data['collection_id']) ? 'selected' : '' ?>
                                                ><?= $catalog_collection->data['title'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <ins></ins>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="input-desc font-weight--normal">
                                        Section tile
                                    </div>
                                    <input type="text"
                                           name="homepage_v3[collection][title]"
                                           class="styled-input mb10"
                                           placeholder="Title"
                                           value="<?= $homepage_v3_data['collection']['title'] ?>">
                                </div>
                                <div class="col-3">
                                    <div class="input-desc font-weight--normal">
                                        Number product for PC
                                    </div>
                                    <input type="number"
                                           name="homepage_v3[collection][number_pc]"
                                           class="styled-input mb10"
                                           value="<?= $homepage_v3_data['collection']['number_pc'] ?>">
                                </div>
                                <div class="col-3">
                                    <div class="input-desc font-weight--normal">
                                        Number product for Mobile
                                    </div>
                                    <input type="number"
                                           name="homepage_v3[collection][number_mobile]"
                                           class="styled-input mb10"
                                           value="<?= $homepage_v3_data['collection']['number_mobile'] ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-item" data-name="[#6] Love us">
                <h3 data-insert-cb="initCollapseSection">
                    [#6] Love us
                    <span class="x-minus"></span>
                </h3>
                <div class="config-generate">
                    <div class="block mt15">
                        <div class="header">
                            <div class="header__main-group">
                                <div class="input-desc font-weight--normal">
                                    Fixed in front-end code
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-item" data-name="[#7] Section banner 50%">
                <h3 data-insert-cb="initCollapseSection">
                    [#7] Section banner 50%
                    <span class="x-minus"></span>
                </h3>
                <div class="config-generate">
                    <div class="block mt15">
                        <div class="header">
                            <div class="header__main-group">
                                <div class="input-desc font-weight--normal">
                                    Enter title, url and upload image for PC and Mobile
                                </div>
                            </div>
                        </div>
                        <div class="p20" style="padding-top: 0 !important;">
                            <div class="row">
                                <?php foreach (range(1, 2) as $key => $item): ?>
                                    <div class="col-6">
                                        <input type="text"
                                               name="homepage_v3[banner_ads][<?= $key ?>][title]"
                                               class="styled-input mb10"
                                               placeholder="Title"
                                               value="<?= $homepage_v3_data['banner_ads'][$key]['title'] ?>">
                                        <input type="text"
                                               name="homepage_v3[banner_ads][<?= $key ?>][url]"
                                               class="styled-input mb10"
                                               placeholder="URL"
                                               value="<?= $homepage_v3_data['banner_ads'][$key]['url'] ?>">
                                        <div class="input-desc font-weight--normal mt-3">
                                            Upload image for PC
                                        </div>
                                        <div data-insert-cb="initPostFrmSidebarImageUploader"
                                             class="frm-image-uploader"
                                             data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                             data-input="homepage_v3[banner_ads][<?= $key ?>][images][pc]"
                                             data-value="<?= $homepage_v3_data['banner_ads'][$key]['images']['pc'] ?>"
                                             data-image="<?= isset($homepage_v3_data['banner_ads'][$key]['images']['pc']) ? OSC::core('aws_s3')->getStorageUrl($homepage_v3_data['banner_ads'][$key]['images']['pc']) : '' ?>">
                                        </div>
                                        <div class="input-desc font-weight--normal mt-3">
                                            Upload image for Mobile
                                        </div>
                                        <div data-insert-cb="initPostFrmSidebarImageUploader"
                                             class="frm-image-uploader"
                                             data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                             data-input="homepage_v3[banner_ads][<?= $key ?>][images][mobile]"
                                             data-value="<?= $homepage_v3_data['banner_ads'][$key]['images']['mobile'] ?>"
                                             data-image="<?= isset($homepage_v3_data['banner_ads'][$key]['images']['mobile']) ? OSC::core('aws_s3')->getStorageUrl($homepage_v3_data['banner_ads'][$key]['images']['mobile']) : '' ?>">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-item" data-name="[#8] Preview 3D">
                <h3 data-insert-cb="initCollapseSection">
                    [#8] Preview 3D
                    <span class="x-minus"></span>
                </h3>
                <div class="config-generate">
                    <div class="block mt15">
                        <div class="header">
                            <div class="header__main-group">
                                <div class="input-desc font-weight--normal">
                                    Enter pairs campaign_id and print_template_id, campaign_id and print_template_id
                                    separated by '-' sign<br>
                                    One line each pair, maximum 4 pairs<br>
                                    Example:<br>
                                    24-1<br>
                                    25-6<br>
                                </div>
                            </div>
                        </div>
                        <div class="p20" style="padding-top: 0 !important;">
                            <div class="frm-grid">
                                <textarea name="homepage_v3[preview_3d]" id="input-preview-3d"
                                          class="styled-textarea"
                                          rows="5"><?= $homepage_v3_data['preview_3d'] ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-item" data-name="[#9] Our Community">
                <h3 data-insert-cb="initCollapseSection">
                    [#9] Our Community
                    <span class="x-minus"></span>
                </h3>
                <div class="config-generate">
                    <div class="block mt15">
                        <div class="header">
                            <div class="header__main-group">
                                <div class="input-desc font-weight--normal">
                                    Upload banner or video for each item
                                </div>
                            </div>
                        </div>
                        <div class="p20" style="padding-top: 0 !important;">
                            <div class="row">
                                <div class="col-12">
                                    <div class="setting-item">
                                        <div class="title">Section title</div>
                                        <div>
                                            <input type="text" name="homepage_v3[community][title]" class="styled-input"
                                                   value="<?= $homepage_v3_data['community']['title'] ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="header py-3">
                                <div class="header__main-group">
                                    <div class="input-desc font-weight--normal">
                                        Enter title, url and upload image for PC and Mobile of each step
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <?php foreach (range(1, 6) as $key => $item): ?>
                                    <div class="col-4 py-3 line-top">
                                        <h3>Item <?= $key + 1 ?></h3>
                                        <input type="text"
                                               name="homepage_v3[community][items][<?= $key ?>][title]"
                                               class="styled-input mb10"
                                               placeholder="Title <?= $key + 1; ?>"
                                               value="<?= $homepage_v3_data['community']['items'][$key]['title'] ?>">
                                        <div class="input-desc font-weight--normal mt-3">
                                            Upload image <?= $key + 1; ?> for PC
                                        </div>
                                        <div data-insert-cb="initPostFrmSidebarImageUploader"
                                             class="frm-image-uploader"
                                             data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                             data-input="homepage_v3[community][items][<?= $key ?>][images][pc]"
                                             data-value="<?= $homepage_v3_data['community']['items'][$key]['images']['pc'] ?>"
                                             data-image="<?= isset($homepage_v3_data['community']['items'][$key]['images']['pc']) ? OSC::core('aws_s3')->getStorageUrl($homepage_v3_data['community']['items'][$key]['images']['pc']) : '' ?>">
                                        </div>
                                        <div class="input-desc font-weight--normal mt-3">
                                            Upload image <?= $key + 1; ?> for Mobile
                                        </div>
                                        <div data-insert-cb="initPostFrmSidebarImageUploader"
                                             class="frm-image-uploader"
                                             data-upload-url="<?= $this->getUrl('page/homepageV3/uploadImage') ?>"
                                             data-input="homepage_v3[community][items][<?= $key ?>][images][mobile]"
                                             data-value="<?= $homepage_v3_data['community']['items'][$key]['images']['mobile'] ?>"
                                             data-image="<?= isset($homepage_v3_data['community']['items'][$key]['images']['mobile']) ? OSC::core('aws_s3')->getStorageUrl($homepage_v3_data['community']['items'][$key]['images']['mobile']) : '' ?>">
                                        </div>
                                        <div class="input-desc font-weight--normal mt-3">
                                            Upload video <?= $key + 1; ?>
                                        </div>
                                        <div class="col-12 frm-grid frm-grid--separate">
                                            <div class="setting-item">
                                                <div class="video-uploader mt0 js-video-uploader js-version-input"
                                                     data-name="homepage_v3[community][items][<?= $key ?>][video]"
                                                     data-processUrl="/core/backend/uploadMedia"
                                                     data-insert-cb="initVideoUploader"
                                                     data-max-size="200"
                                                     data-video='<?= $homepage_v3_data['community']['items'][$key]['video'] ?? null; ?>'
                                                     data-position="input"
                                                     data-use_thumbnail="1"
                                                ></div>
                                                <div class="form-input-error mt0"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-item" data-name="[#10] What Our Customers Say">
                <h3 data-insert-cb="initCollapseSection">
                    [#10] What Our Customers Say
                    <span class="x-minus"></span>
                </h3>
                <div class="config-generate">
                    <div class="block mt15">
                        <div class="header">
                            <div class="header__main-group">
                                <div class="input-desc font-weight--normal">
                                    Enter the review id, each review id is separated by a '-'.
                                </div>
                            </div>
                        </div>
                        <div class="p20" style="padding-top: 0 !important;">
                            <div class="frm-grid">
                                <textarea name="homepage_v3[customer_review]" id="input-customer-say"
                                          class="styled-textarea"
                                          rows="5"><?= $homepage_v3_data['customer_review'] ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <input type="hidden" value="1" name="submit_form">
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/*') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>