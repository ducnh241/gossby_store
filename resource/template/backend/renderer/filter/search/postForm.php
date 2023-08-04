<?php
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
 * @copyright    Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
/* @var $this Helper_Backend_Template */
$this
    ->addComponent('datePicker')
    ->push([
        'vendor/bootstrap/bootstrap-grid.min.css',
        'common/select2.min.css',
        'frontend/backend.scss',
        'core/setting.scss',
        'filter/search.scss',
    ], 'css')
    ->push([
        'common/select2.min.js',
        'filter/search.js'
    ], 'js');

$collections = $params['collections'];
$popular_collections = $params['popular_collections'];
?>

<form id="form" method="post" action="<?= $this->getUrl('*/*/*', ['save' => 1]) ?>">
    <div class="setting-config-panel post-frm m25" style="width: auto;">
        <div class="setting-config-group">
            <div class="info">
                <div class="title">Popular Collections</div>
                <div class="desc">Set up popular collections in search box</div>
            </div>
            <div class="block">
                <div class="p20">
                    <div class="popular-collection-description input-desc mb-2">Select collection, enter title and upload custom image</div>
                    <script type="text/template" id="popular-collection-template">
                        <div class="col-3 mb20 popular-collection-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="popular-collection-label">Collection:</strong>
                                <button class="popular-collection-delete" type="button">Delete</button>
                            </div>
                            <select class="popular-collection-select styled-select" name="popular_collections[{{key}}][collection_id]" data-insert-cb="initSelect2" data-placeholder="Select collection" required>
                                <option value=""></option>
                                <?php foreach ($collections as $collection): ?>
                                    <option value="<?= $collection['collection_id'] ?>" data-title="<?= $collection['title'] ?>" data-image="<?= $collection['image'] ?>" ><?= $collection['title']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text"
                                   name="popular_collections[{{key}}][title]"
                                   class="popular-collection-title styled-input mt-2"
                                   placeholder="Title"
                                   value="">
                            <div data-insert-cb="initPostFrmSidebarImageUploader"
                                 class="popular-collection-uploader frm-image-uploader mt-2"
                                 data-upload-url="<?= $this->getUrl('filter/search/uploadImage') ?>"
                                 data-input="popular_collections[{{key}}][image]"
                                 data-value=""
                                 data-image=""
                                 data-s3-dir="<?= OSC::core('aws_s3')->getStorageDirUrl() ?>">
                            </div>
                        </div>
                    </script>

                    <div class="row popular-collection-list">
                        <div class="col-3 mb20 popular-collection-add-wrapper">
                            <div class="popular-collection-add">
                                <i></i>
                                <span>Add Collection</span>
                            </div>
                        </div>

                        <?php foreach ($popular_collections as $key => $popular_collection): ?>
                            <div class="col-3 mb20 popular-collection-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="popular-collection-label">Collection:</strong>
                                    <button class="popular-collection-delete" type="button">Delete</button>
                                </div>
                                <select class="popular-collection-select styled-select" name="popular_collections[<?= $key ?>][collection_id]" data-insert-cb="initSelect2" data-placeholder="Select collection" required>
                                    <option value=""></option>
                                    <?php foreach ($collections as $collection): ?>
                                        <option
                                                value="<?= $collection['collection_id'] ?>"
                                                data-title="<?= $collection['title'] ?>"
                                                data-image="<?= $collection['image'] ?>"
                                                <?php if ($popular_collection['collection_id'] == $collection['collection_id']): ?>selected<?php endif; ?>
                                        ><?= $collection['title']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text"
                                       name="popular_collections[<?= $key ?>][title]"
                                       class="popular-collection-title styled-input mt-2"
                                       placeholder="Title"
                                       value="<?= $popular_collection['title'] ?>">
                                <div data-insert-cb="initPostFrmSidebarImageUploader"
                                     class="popular-collection-uploader frm-image-uploader mt-2"
                                     data-upload-url="<?= $this->getUrl('filter/search/uploadImage') ?>"
                                     data-input="popular_collections[<?= $key ?>][image]"
                                     data-value="<?= $popular_collection['image'] ?>"
                                     data-image="<?= $popular_collection['image'] ? OSC::core('aws_s3')->getStorageUrl($popular_collection['image']) : '' ?>"
                                     data-s3-dir="<?= OSC::core('aws_s3')->getStorageDirUrl() ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="setting-config-group">
            <div class="info">
                <div class="title">Trending keywords</div>
                <div class="desc">Set up trending keywords in search box</div>
            </div>
            <div class="block">
                <div class="p20">
                    <div class="trending">
                        <input type="hidden" class="trending-hidden-input" name="trending_keywords_manual" value="<?= $this->safeString(OSC::encode($params['trending_keywords_manual'])) ?>">
                        <input type="hidden" class="trending-suggestion-input" value="<?= $this->safeString(OSC::encode($params['suggestions'])) ?>">
                        <script type="text/template" id="trending-item">
                            <div class="trending-item">
                                <span class="trending-text">{{text}}</span>
                                <button class="trending-delete" type="button" data-index="{{index}}"><?= $this->getIcon('trash-can') ?></button>
                            </div>
                        </script>
                        <div class="row">
                            <div class="col-3">
                                <div class="trending-input-dropdown">
                                    <div class="trending-suggestion-desc">Most used keywords</div>
                                    <div class="trending-suggestions"></div>
                                </div>
                            </div>
                            <div class="col-9 pl-4">
                                <div class="trending-list"></div>
                                <div class="trending-form">
                                    <div class="trending-input-group">
                                        <input class="trending-input" type="text">
                                    </div>
                                    <button class="trending-add" type="button">Add trending keyword</button>
                                </div>
                                <div class="d-flex align-items-center mt-3">
                                    <input id="trending-expired" name="trending-expired" readonly class="trending-expired" type="text" data-datepicker-config="<?= $this->safeString(OSC::encode(array('date_format' => 'DD/MM/YYYY', 'min_date' => date('m/d/Y')))) ?>" data-insert-cb="initDatePicker" />
                                    <label class="ml-2 mb-0" for="trending-expired">Set expire date</label>
                                </div>
                                <div class="mt-2" style="color: #999; font-style: italic">
                                    <p class="mb-2"><strong>Note:</strong></p>
                                    <ul class="mt-0">
                                        <li>Maximum 3 trending keywords</li>
                                        <li>If none keywords added or they're expired, system will auto select 3 <strong>most used keywords</strong> from top to bottom.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="action-bar">
            <button type="submit" class="btn btn-primary" name="">Save</button>
        </div>
    </div>
</form>
