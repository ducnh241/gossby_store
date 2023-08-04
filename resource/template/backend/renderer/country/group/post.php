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
$this->push(['core/location.js'], 'js');
$this->addComponent('select2');

$country_code_selected = $params['model']->data['country_ids'];

$countries = $params['countries'];

$country = [];
$i = 0;
foreach ($countries as $item) {
    $country[$i]['id'] = $item->getId();
    $country[$i]['text'] = $item->data['country_name'];
    $country[$i]['selected'] = in_array($item->getId(), $country_code_selected) ? 'selected' : '';
    $i++;
}

$provinces = OSC::model('core/country_province')->getCollection()->load();
$countries = OSC::model('core/country_country')->getCollection()->addCondition('status', Helper_Core_Country::STATUS_ACTIVE)->load();
$groups = OSC::model('core/country_group')->getCollection()->load();
$country_arr = [];
if (count($countries) > 0) {
    $i = 0;
    foreach ($countries as $country) {
        $country_arr[$country->data['country_code']] = $country->data['country_name'];
        $country_locations[$i]['id'] = $country->data['id'];
        $country_locations[$i]['text'] = $country->data['country_name'] .' - '. $country->data['country_code'];
        $i++;
    }
}

if (count($provinces) > 0) {
    $i = 0;
    foreach ($provinces as $province) {
        $province_locations[$i]['id'] = $province->data['id'];
        $province_locations[$i]['text'] = $country_arr[$province->data['country_code']]. ' - ' .$province->data['province_name']. ' - '. $province->data['province_code'];
        $i++;
    }
}

if (count($groups) > 0) {
    $i = 0;
    foreach ($groups as $group) {
        $group_locations[$i]['id'] = $group->data['id'];
        $group_locations[$i]['text'] = $group->data['group_name'];
        $group_locations[$i]['selected'] = in_array($group->data['id'], $country_code_selected) ? 'selected' : '';
        $i++;
    }
}

?>

<div id="list_group_locations">
    <?= $this->getJSONTag($group_locations, 'list_group_locations');?>
</div>
<div id="list_provinces">
    <?= $this->getJSONTag($province_locations, 'list_provinces');?>
</div>
<div id="list_countries">
    <?= $this->getJSONTag($country_locations, 'list_countries');?>
</div>


<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post" class="post-frm p25" style="width: 550px">
    <div class="block">
        <div class="header">
            <div class="header__main-group"><div class="header__heading"><?= $params['form_title'] ?></div></div>
        </div>
        <div class="p20">
            <div class="frm-grid">
                <div>
                    <label for="input-group-name">Group Name</label>
                    <div><input class="styled-input" type="text" name="group_name" id="input-group-name" value="<?= $this->safeString($params['model']->data['group_name']); ?>" /></div>
                </div>
            </div>

            <div class="frm-grid">
                <div class="selection2-country-code">
                    <?= $this->build('country/group/addForm', ['select_group_location' => true]); ?>
                </div>
            </div>



            <div class="frm-grid">
                <label for="input-group-description">Group description</label>
                <textarea name="group_description" class="styled-textarea"><?= $this->safeString($params['model']->data['group_description']) ?></textarea>
            </div>

        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/group') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-primary "><?= $this->_('core.save') ?></button>
    </div>
</form>

<style>
    .select2-container, .select2-container--default .select2-selection--multiple {
        z-index: 9999999;
        border-radius: 2px !important;
        border-color: #E0E0E0 !important;
    }
    .select2-container {
        width: 100% !important;
    }
</style>