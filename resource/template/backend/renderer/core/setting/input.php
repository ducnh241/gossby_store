<?php
/* @var $this Helper_Backend_Template */
?>
<?php $setting_key_hash = md5($params['setting_key']); ?>
<div>
    <?php if ($params['label']) : ?><label for="input-<?= $setting_key_hash ?>"><?= $params['label'] ?></label><?php endif; ?>
    <div>
        <?php if ($params['input_type'] == 'input') : ?>
            <input type="text" name="setting[<?= $params['setting_key'] ?>]" class="styled-input" id="input-<?= $setting_key_hash ?>" value="<?= $this->safeString($params['value']) ?>" />
        <?php elseif ($params['input_type'] == 'address') : ?>
            <?= OSC::core('template')->build('core/address_form', ['input_name_prefix' => 'setting[' . $params['setting_key'] . ']', 'skip_contact_frm' => true, 'data' => $params['value'], 'require' => $params['empty_allowed_flag'] ? false : true]) ?>
        <?php elseif ($params['input_type'] == 'textarea') : ?>
            <textarea name="setting[<?= $params['setting_key'] ?>]" class="styled-textarea" id="input-<?= $setting_key_hash ?>"><?= $this->safeString(preg_replace('/<br\s*\/?>/i', PHP_EOL, $params['value'])) ?></textarea>
        <?php elseif ($params['input_type'] == 'editor') : ?>                                                    
            <textarea name="setting[<?= $params['setting_key'] ?>]" data-insert-cb="initEditor" id="input-<?= $setting_key_hash ?>"><?= $this->safeString($params['value']) ?></textarea>
        <?php elseif ($params['input_type'] == 'onoff') : ?>
            <input type="checkbox" class="mrk-switcher" name="setting[<?= $params['setting_key'] ?>]" value="1" id="input-<?= $setting_key_hash ?>"<?php if ($params['value'] == 1) : ?> checked="checked"<?php endif; ?> />
        <?php elseif (in_array($params['input_type'], array('checkbox', 'radio'), true)) : ?>
            <?php $setting_name = 'setting[' . $params['setting_key'] . ']' . ($params['input_type'] == 'checkbox' ? '[]' : ''); ?>
            <?php foreach ($params['input_data'] as $value => $label) : ?>
                <div>
                    <input type="<?= $params['input_type'] ?>" class="styled-<?= $params['input_type'] ?>" id="" name="<?= $setting_name ?>" value="<?= $this->safeString($value) ?>" />
                    <label for=""><?= $label ?></label>
                </div>
            <?php endforeach; ?>
        <?php elseif ($params['input_type'] == 'navigation') : ?>
            <?php
            $collection = OSC::model('navigation/navigation')->getCollection();
            $collection->sort('navigation_id', OSC_Database::ORDER_DESC)->load();
            if ($collection->length() > 0):
                ?>
                <div class="styled-select"><select name="setting[<?= $params['setting_key'] ?>]">
                        <?php
                        foreach ($collection as $nav):
                            $selected = ($params['value'] == $nav->data['navigation_id']) ? "selected='selected'" : '';
                            ?>
                            <option value="<?php echo $nav->data['navigation_id']; ?>" <?php echo $selected; ?>><?php echo $nav->data['title']; ?></option>
                            <?php
                        endforeach;
                        ?>
                    </select><ins></ins></div>
                <?php
            else:
                echo "Please create a navigation before setting up this config";
            endif;
            ?>
        <?php elseif ($params['input_type'] == 'collection') : ?>
            <?php
            $collections = OSC::model('catalog/collection')->getCollection();
            $collections->sort('collection_id', OSC_Database::ORDER_DESC)->load();
            if ($collections->length() > 0):
                ?>
                <div class="styled-select"><select name="setting[<?= $params['setting_key'] ?>]">
                        <?php
                        foreach ($collections as $collection):
                            $selected = ($params['value'] == $collection->data['collection_id']) ? "selected='selected'" : '';
                            ?>
                            <option value="<?php echo $collection->data['collection_id']; ?>" <?php echo $selected; ?>><?php echo $collection->data['title']; ?></option>
                        <?php
                        endforeach;
                        ?>
                        <option value="0" <?php if ($params['value'] == 0) : echo"selected='selected'" ; endif;?>>All Product</option>
                    </select>
                    <ins></ins>
                </div>
            <?php
            else:
                echo "Please create a collection before setting up this config";
            endif;
            ?>
        <?php else : ?>
            <?php
            $setting_name = 'setting[' . $params['setting_key'] . ']';
            $setting_value = $params['value'];
            $multiple = '';

            if ($params['input_type'] == 'multi_select') {
                $setting_name .= '[]';
                $multiple = ' multiple="multiple"';
            } else {
                $setting_value = array($setting_value);
            }
            ?>
            <div class="styled-select">                
                <select name="<?= $setting_name ?>"<?= $multiple ?>>
                    <?php foreach ($params['input_data'] as $value => $label) : ?>
                        <option value="<?= $this->safeString($value) ?>"<?php if (in_array($value, $setting_value, true)) : ?> selected="selected"<?php endif; ?>><?= $this->safeString($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <ins></ins>
            </div>
        <?php endif; ?>   
        <?php if ($params['description']) : ?>
            <div class="input-desc"><?= $params['description'] ?></div>
        <?php endif; ?>     
    </div>
</div>