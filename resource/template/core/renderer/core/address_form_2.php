<?php
/* @var $this OSC_Template */
$this->push('[core]core/address2.js', 'js/top');
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
<div class="address-frm post-frm" data-insert-cb="initAddress2FrmHandler" data-input-name-prefix="<?= $params['input_name_prefix'] ?>"<?php if ($params['require']): ?> data-require="1"<?php endif; ?> data-skip-contact-frm="<?= $skip_contact_frm ? 1 : 0 ?>">
    <?php if (!$skip_contact_frm) : ?>
        <div class="frm-grid frm-grid--separate">
            <div data-frm="full_name">
                <label class="minified-input">
                    <input type="text" placeholder="Full name" name="<?= $params['input_name_prefix'] ?>[full_name]" id="<?= $params['input_name_prefix'] ?>-full_name" value="<?= isset($address['full_name']) ? $this->safeString($address['full_name']) : '' ?>" />
                    <span class="label">Full name</span>
                </label>
            </div>
            <div data-frm="phone">
                <label class="minified-input">
                    <input type="text" placeholder="Phone number" name="<?= $params['input_name_prefix'] ?>[phone]" id="<?= $params['input_name_prefix'] ?>-phone" value="<?= isset($address['phone']) ? $this->safeString($address['phone']) : '' ?>" />
                    <span class="label">Phone number</span>
                </label>
            </div>
        </div>
    <?php endif; ?>
    <div class="frm-grid frm-grid--separate">
        <div data-frm="address1">
            <label class="minified-input">
                <input type="text" placeholder="Address" name="<?= $params['input_name_prefix'] ?>[address1]" id="<?= $params['input_name_prefix'] ?>-address1" value="<?= isset($address['address1']) ? $this->safeString($address['address1']) : '' ?>" />
                <span class="label">Address</span>
            </label>
        </div>
    </div>
    <div class="frm-grid frm-grid--separate">
        <div data-frm="address2">
            <label class="minified-input">
                <input type="text" placeholder="Apartment, suite, etc (optional)" name="<?= $params['input_name_prefix'] ?>[address2]" id="<?= $params['input_name_prefix'] ?>-address2" value="<?= isset($address['address2']) ? $this->safeString($address['address2']) : '' ?>" />
                <span class="label">Apartment, suite, etc (optional)</span>
            </label>
        </div>
    </div>
    <div class="frm-grid frm-grid--separate">
        <div data-frm="city">
            <label class="minified-input">
                <input type="text" placeholder="City" name="<?= $params['input_name_prefix'] ?>[city]" id="<?= $params['input_name_prefix'] ?>-city" value="<?= isset($address['city']) ? $this->safeString($address['city']) : '' ?>" />
                <span class="label">City</span>
            </label>
        </div>
        <div class="province-frm" data-frm="province">
            <label class="minified-input">
                <?php $provinces = OSC::helper('core/country')->getProvinces($selected_country_code ? $selected_country_code : array_key_first($countries)) ?>
                <?php if (count($provinces) > 0) : ?> 
                    <select name="<?= $params['input_name_prefix'] ?>[province]" id="<?= $params['input_name_prefix'] ?>-province">
                        <option value="N/A">Please select province/state</option>
                        <?php foreach ($provinces as $province_code => $province_title) : ?>
                            <option value="<?= $this->safeString($province_title) ?>"<?php if ($selected_province_code == $province_title) : ?> selected="selected"<?php endif; ?>><?= $this->safeString($province_title) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else : ?>
                    <input type="text" placeholder="Province" name="<?= $params['input_name_prefix'] ?>[province]" id="<?= $params['input_name_prefix'] ?>-province" value="<?= $selected_province ? $this->safeString($selected_province) : '' ?>" />
                    <span class="label">Province</span>
                <?php endif; ?>
            </label>
        </div>
    </div>
    <div class="frm-grid frm-grid--separate">
        <div data-frm="country">
            <label class="minified-input">
                <select name="<?= $params['input_name_prefix'] ?>[country]" id="<?= $params['input_name_prefix'] ?>-country">
                    <?php foreach ($countries as $country_code => $country_title) : ?>
                        <option value="<?= $this->safeString($country_title) ?>" data-code="<?= $country_code ?>"<?php if ($selected_province == $country_code) : ?> selected="selected"<?php endif; ?>><?= $this->safeString($country_title) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div data-frm="zip">
            <label class="minified-input">
                <input type="text" placeholder="ZIP/Postal code" name="<?= $params['input_name_prefix'] ?>[zip]" id="<?= $params['input_name_prefix'] ?>-zip" value="<?= isset($address['zip']) ? $this->safeString($address['zip']) : '' ?>" />
                <span class="label">ZIP/Postal code</span>
            </label>
        </div>
    </div>
</div>