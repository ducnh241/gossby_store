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
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
/* @var $this Helper_Backend_Template */
?>
<?php
$this->addComponent('datedropper', 'timedropper');
$this->push($this->base_url . '/tinymce/js/tinymce.min.js', 'js');


$this->push(<<<EOF
tinymce.init({
    entity_encoding : "raw",
    mode : "specific_textareas",
    selector : "#input-content",
    /*editor_selector : "#description",*/
    theme: "modern",
    menubar: false,
    relative_urls : false,
    remove_script_host: false,
    fontsize_formats: "8pt 9pt 10pt 11pt 12pt 14pt 18pt 26pt 36pt",
    autoresize_min_height : '200px',
    autoresize_max_height : '500px',
    plugins: [
        "autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor code"
    ],
    toolbar1: "bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | fontsizeselect | link image media | table | code removeformat",
    image_advtab: true,
    external_filemanager_path:"/tinymce/filemanager/",
    filemanager_title:"Responsive Filemanager" ,
    external_plugins: { "filemanager" : "../filemanager/plugin.min.js"},
    templates: [
        {title: 'Test template 1', content: 'Test 1'},
        {title: 'Test template 2', content: 'Test 2'}
    ],
    formats : {
        'alignleft' : {'selector' : 'p,h1,h2,h3,h4,h5,h6,td,th,div,li', styles: {"text-align":  'left'}},
        'aligncenter' : {'selector' : 'p,h1,h2,h3,h4,h5,h6,td,th,div,li', styles: {"text-align":  'center'}},
        'alignright' : {'selector' : 'p,h1,h2,h3,h4,h5,h6,td,th,div,li', styles: {"text-align":  'right'}},
        'alignfull' : {'selector' : 'p,h1,h2,h3,h4,h5,h6,td,th,div,li', styles: {"text-align":  'justify'}}
    }
});
tinymce.init({
    entity_encoding : "raw",
    mode : "specific_textareas",
    selector : "#input-description",
    /*editor_selector : "#description",*/
    theme: "modern",
    menubar: false,
    relative_urls : false,
    remove_script_host: false,
    fontsize_formats: "8pt 9pt 10pt 11pt 12pt 14pt 18pt 26pt 36pt",
    autoresize_min_height : '200px',
    autoresize_max_height : '500px',
    plugins: [
        "autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor code"
    ],
    toolbar1: "bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | code removeformat",
    image_advtab: true,
    external_filemanager_path:"/tinymce/filemanager/",
    filemanager_title:"Responsive Filemanager" ,
    external_plugins: { "filemanager" : "../filemanager/plugin.min.js"},
    templates: [
        {title: 'Test template 1', content: 'Test 1'},
        {title: 'Test template 2', content: 'Test 2'}
    ],
    formats : {
        'alignleft' : {'selector' : 'p,h1,h2,h3,h4,h5,h6,td,th,div,li', styles: {"text-align":  'left'}},
        'aligncenter' : {'selector' : 'p,h1,h2,h3,h4,h5,h6,td,th,div,li', styles: {"text-align":  'center'}},
        'alignright' : {'selector' : 'p,h1,h2,h3,h4,h5,h6,td,th,div,li', styles: {"text-align":  'right'}},
        'alignfull' : {'selector' : 'p,h1,h2,h3,h4,h5,h6,td,th,div,li', styles: {"text-align":  'justify'}}
    }
});
EOF
        , 'js_code');
?>
<div class="p25">
    <form action="<?php echo $this->getUrl('*/*/*', array('section_id' => $params['section']->getId(), 'cat' => $params['cat_info']['id'], 'id' => $params['model']->getId())); ?>" method="post" enctype="multipart/form-data">        
        <div class="tab-system" data-insert-cb="initTabSystem" selected-tab="general">
            <ul class="tabs">
                <li tab-key="general">Thông tin chung</li>
                <li tab-key="content">Bài viết</li>
                <li tab-key="seo">Cài đặt SEO</li>
                <li tab-key="setting">Cài đặt</li>
            </ul>
            <div class="tabs-content">
                <div tab-key="general">
                    <div class="block">
                        <div class="plr15">
                            <table class="frm-grid">
                                <tr>
                                    <td><label for="input-title">Tiêu đề</label></td>
                                    <td><input type="text" name="title" id="input-title" value="<?php echo $this->safeString($params['model']->data['title']); ?>" /></td>
                                </tr>
                                <tr>
                                    <td><label for="input-tags">Tags</label><div class="description">Các tag cách nhau bởi dấu ","</div></td>
                                    <td><input type="text" name="tags" id="input-tags" value="<?php echo $this->safeString($params['model']->data['tags']); ?>" /></td>
                                </tr>
                                <tr>
                                    <td><label for="input-position">Section</label></td>
                                    <td><?php echo $params['section']->data['name']; ?></td>
                                </tr>
                                <tr>
                                    <td><label for="input-cat-id">Thư mục</label></td>
                                    <td>
                                        <select name="cat_id" id="input-cat-id">
                                            <option value="-1">Thư mục gốc</option>
                                            <?php foreach ($params['cat_items'] as $cat_id => $cat_name) : ?>
                                                <option value="<?php echo $cat_id; ?>"<?php if ($cat_id == $params['model']->data['cat_id']) : ?> selected="selected"<?php endif; ?>><?php echo $this->safeString($cat_name); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="input-display-date">Thời gian</label></td>
                                    <td>
                                        <div class="date-dropper" data-insert-cb="initDateDropper"><input type="text" value="<?php echo date('d/m/Y', $params['model']->data['display_timestamp'] ? $params['model']->data['display_timestamp'] : time()); ?>" id="input-display-date" name="display_date" /><i class="fa fa-calendar"></i></div>
                                        <div class="time-dropper" data-insert-cb="initTimeDropper"><input type="text" value="<?php echo date('H:i', $params['model']->data['display_timestamp'] ? $params['model']->data['display_timestamp'] : time()); ?>" id="input-display-time" name="display_time" /><i class="fa fa-clock-o"></i></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="input-img">Upload ảnh thumbnail</label></td>
                                    <td><input type="file" name="img" id="input-img" /></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="block">
                        <h3>Bài viết rút gọn</h3>
                        <div class="frm-editor">
                            <textarea name="description" id="input-description"><?php echo $this->safeString($params['model']->data['description']); ?></textarea>
                        </div>
                    </div>
                </div>
                <div tab-key="content">
                    <div class="block">
                        <h3>Nội dung bài viết</h3>
                        <div class="frm-editor">
                            <textarea name="content" id="input-content"><?php echo $this->safeString($params['model']->data['content']); ?></textarea>
                        </div>
                    </div>
                </div>
                <div tab-key="seo">
                    <div class="block">
                        <div class="plr15">
                            <table class="frm-grid">
                                <tr>
                                    <td><label for="input-seo-title">SEO :: Title</label></td>
                                    <td><input type="text" name="seo_title" id="input-seo-title" value="<?php echo $this->safeString($params['model']->data['seo_title']); ?>" /></td>
                                </tr>
                                <tr>
                                    <td><label for="input-seo-keywords">SEO :: Keywords</label></td>
                                    <td><input type="text" name="seo_keywords" id="input-seo-keywords" value="<?php echo $this->safeString($params['model']->data['seo_keywords']); ?>" /></td>
                                </tr>
                                <tr>
                                    <td><label for="input-seo-description">SEO :: Description</label></td>
                                    <td><input type="text" name="seo_description" id="input-seo-description" value="<?php echo $this->safeString($params['model']->data['seo_description']); ?>" /></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div tab-key="setting">
                    <div class="block">
                        <div class="plr15">
                            <table class="frm-grid">
                                <tr>
                                    <td><label for="switcher-highlight-flag">Bài viết nổi bật</label></td>
                                    <td><input type="checkbox" class="mrk-switcher" name="highlight_flag" value="1" id="switcher-highlight-flag"<?php if ($params['model']->data['highlight_flag'] == 1) : ?> checked="checked"<?php endif; ?> /></td>
                                </tr>
                                <tr>
                                    <td><label for="switcher-homepage-flag">Hiện ở trang chủ</label></td>
                                    <td><input type="checkbox" class="mrk-switcher" name="homepage_flag" value="1" id="switcher-homepage-flag"<?php if ($params['model']->data['homepage_flag'] == 1) : ?> checked="checked"<?php endif; ?> /></td>
                                </tr>
                                <tr>
                                    <td><label for="switcher-display-date-flag">Hiện thị ngày tháng</label></td>
                                    <td><input type="checkbox" class="mrk-switcher" name="display_date_flag" value="1" id="switcher-display-date-flag"<?php if ($params['model']->data['display_date_flag'] == 1) : ?> checked="checked"<?php endif; ?> /></td>
                                </tr>
                                <tr>
                                    <td><label for="switcher-social-flag">Hiện thị Facebook button</label></td>
                                    <td><input type="checkbox" class="mrk-switcher" name="social_flag" value="1" id="switcher-social-flag"<?php if ($params['model']->data['social_flag'] == 1) : ?> checked="checked"<?php endif; ?> /></td>
                                </tr>
                                <?php if (OSC::helper('user/authentication')->getMember()->isRoot()) : ?>
                                    <tr>
                                        <td><label for="switcher-system-item-flag">Item hệ thống</label></td>
                                        <td><input type="checkbox" class="mrk-switcher" name="system_item_flag" value="1" id="switcher-system-item-flag"<?php if ($params['model']->data['system_item_flag'] == 1) : ?> checked="checked"<?php endif; ?> /></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="frm-action-bar">
            <button type="submit" class="btn btn-large btn-red"><i class="fa fa-save"></i>Cập nhật</button>
        </div>
    </form>
</form>
</div>