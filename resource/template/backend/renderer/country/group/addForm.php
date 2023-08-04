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
$key = $params['key'] ? $params['key'] : OSC_Core::makeUniqid();
$countries = OSC::model('core/country_country')->getCollection()->addCondition('status', Helper_Core_Country::STATUS_ACTIVE)->load();
$country_locations = [];
$country_arr = [];
if (count($countries) > 0) {
    $i = 0;
    foreach ($countries as $country) {
        $country_arr[$country->data['country_code']] = $country->data['country_name'];
        $country_locations[$i]['id'] = $country->data['id'];
        $country_locations[$i]['country'] = $country->data['country_name'];
        $i++;
    }
}

$provinces = OSC::model('core/country_province')->getCollection()->load();

$province_locations = [];
if (count($provinces) > 0) {
    $i = 0;
    foreach ($provinces as $province) {
        $province_locations[$i]['id'] = $province->data['id'];
        $province_locations[$i]['country'] = $country_arr[$province->data['country_code']];
        $province_locations[$i]['province'] = $province->data['province_name'];
        $i++;
    }
}

$groups = OSC::model('core/country_group')->getCollection()->load();
$location_groups = [];
if (count($groups) > 0) {
    $i = 0;
    foreach ($groups as $group) {
        $location_groups[$i]['id'] = $group->data['id'];
        $location_groups[$i]['group'] = $group->data['group_name'];
        $location_groups[$i]['selected'] = in_array($group->data['id'], $country_code_selected) ? 'selected' : '';
        $i++;
    }
}
?>
<div id="list_countries">
    <?= $this->getJSONTag($country_locations, 'list_countries');?>
</div>
<div id="list_provinces">
    <?= $this->getJSONTag($province_locations, 'list_provinces');?>
</div>
<div id="list_location_groups">
    <?= $this->getJSONTag($location_groups, 'list_location_groups');?>
</div>


