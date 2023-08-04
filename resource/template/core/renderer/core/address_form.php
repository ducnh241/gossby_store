<?php
/* @var $this OSC_Template */
$this->push('[core]core/address.js', 'js/top');
$this->addComponent('autoCompletePopover');
$address = (isset($params['data']) && is_array($params['data'])) ? $params['data'] : [];
$skip_contact_frm = isset($params['skip_contact_frm']) && $params['skip_contact_frm'];

$selected_country_code = isset($address['country']) ? OSC::helper('core/country')->getCountryCode($address['country']) : '';
$selected_province = $selected_country_code && isset($address['province']) ? $address['province'] : '';

$countries = OSC::helper('core/country')->getCountries();
asort($countries);
$countries = array_merge(['US' => $countries['US']], $countries);

if (!$selected_country_code && isset($params['default_country_code'])) {
    $selected_country_code = $params['default_country_code'];

    $selected_province = '';

    if (isset($params['default_province_code']) && OSC::helper('core/country')->verifyProvinceCode($selected_country_code, $params['default_province_code'])) {
        $selected_province = $params['default_province_code'];
    }

    if ($selected_province == '' && isset($params['default_province'])) {
        $selected_province = $params['default_province'];
    }
}
?>
<div class="address-frm post-frm" data-insert-cb="initAddressFrmHandler" data-input-name-prefix="<?= $params['input_name_prefix'] ?>"<?php if ($params['require']): ?> data-require="1"<?php endif; ?> data-skip-contact-frm="<?= $skip_contact_frm ? 1 : 0 ?>">
    <?php if (!$skip_contact_frm) : ?>
        <div class="frm-grid frm-grid--separate">
            <div data-frm="first_name">
                <label for="<?= $params['input_name_prefix'] ?>-first_name">First name</label>
                <div><input type="text" class="styled-input" name="<?= $params['input_name_prefix'] ?>[first_name]" id="<?= $params['input_name_prefix'] ?>-first_name" value="<?= isset($address['first_name']) ? $this->safeString($address['first_name']) : '' ?>" /></div>
            </div>
            <div data-frm="last_name">
                <label for="<?= $params['input_name_prefix'] ?>-last_name">Last name</label>
                <div><input type="text" class="styled-input" name="<?= $params['input_name_prefix'] ?>[last_name]" id="<?= $params['input_name_prefix'] ?>-last_name" value="<?= isset($address['last_name']) ? $this->safeString($address['last_name']) : '' ?>" /></div>
            </div>
        </div>
        <div class="frm-grid frm-grid--separate">
            <div data-frm="phone">
                <label for="<?= $params['input_name_prefix'] ?>-phone">Phone</label>
                <div><input type="text" class="styled-input" name="<?= $params['input_name_prefix'] ?>[phone]" id="<?= $params['input_name_prefix'] ?>-phone" value="<?= isset($address['phone']) ? $this->safeString($address['phone']) : '' ?>" /></div>
            </div>
        </div>
    <?php endif; ?>
    <div class="frm-grid frm-grid--separate">
        <div data-frm="address1">
            <label for="<?= $params['input_name_prefix'] ?>-address1">Address</label>
            <div><input type="text" class="styled-input" name="<?= $params['input_name_prefix'] ?>[address1]" id="<?= $params['input_name_prefix'] ?>-address1" value="<?= isset($address['address1']) ? $this->safeString($address['address1']) : '' ?>" /></div>
        </div>
    </div>
    <div class="frm-grid frm-grid--separate">
        <div data-frm="address2">
            <label for="<?= $params['input_name_prefix'] ?>-address2">Apartment, suite, etc (optional)</label>
            <div><input type="text" class="styled-input" name="<?= $params['input_name_prefix'] ?>[address2]" id="<?= $params['input_name_prefix'] ?>-address2" value="<?= isset($address['address2']) ? $this->safeString($address['address2']) : '' ?>" /></div>
        </div>
    </div>
    <div class="frm-grid frm-grid--separate">
        <div data-frm="city">
            <label for="<?= $params['input_name_prefix'] ?>-city">City</label>
            <div><input type="text" class="styled-input" name="<?= $params['input_name_prefix'] ?>[city]" id="<?= $params['input_name_prefix'] ?>-city" value="<?= isset($address['city']) ? $this->safeString($address['city']) : '' ?>" /></div>
        </div>
    </div>
    <div class="frm-grid frm-grid--separate">
        <div data-frm="country">
            <label for="<?= $params['input_name_prefix'] ?>-country">Country</label>
            <div class="styled-select">
                <select name="<?= $params['input_name_prefix'] ?>[country]" id="<?= $params['input_name_prefix'] ?>-country">
                    <?php foreach ($countries as $country_code => $country_title) : ?>
                        <option value="<?= $this->safeString($country_title) ?>" data-code="<?= $country_code ?>"<?php if ($selected_country_code == $country_code) : ?> selected="selected"<?php endif; ?>><?= $this->safeString($country_title) ?></option>
                    <?php endforeach; ?>
                </select>
                <ins></ins>
            </div>
        </div>
        <div class="province-frm" data-frm="province">
            <label for="<?= $params['input_name_prefix'] ?>-province">Province/State</label>
            <div class="mrk-province-input">
                <?php $provinces = OSC::helper('core/country')->getProvinces($selected_country_code ? $selected_country_code : array_key_first($countries)) ?>
                <?php if (count($provinces) > 0) : ?>
                    <div class="styled-select">
                        <select name="<?= $params['input_name_prefix'] ?>[province]" id="<?= $params['input_name_prefix'] ?>-province">
                            <option value="N/A">Please select province/state</option>
                            <?php foreach ($provinces as $province_code => $province_title) : ?>
                                <option value="<?= $this->safeString($province_title) ?>"<?php if ($selected_province == $province_title) : ?> selected="selected"<?php endif; ?>><?= $this->safeString($province_title) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <ins></ins>
                    </div>
                <?php else : ?>
                    <div>
                        <input type="text" class="styled-input" placeholder="Province" name="<?= $params['input_name_prefix'] ?>[province]" id="<?= $params['input_name_prefix'] ?>-province" value="<?= $selected_province ? $this->safeString($selected_province) : '' ?>" />
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div data-frm="zip">
            <label for="<?= $params['input_name_prefix'] ?>-zip">ZIP/Postal code</label>
            <div><input type="text" class="styled-input" name="<?= $params['input_name_prefix'] ?>[zip]" id="<?= $params['input_name_prefix'] ?>-zip" value="<?= isset($address['zip']) ? $this->safeString($address['zip']) : '' ?>" /></div>
        </div>
    </div>
</div>