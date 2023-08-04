<?php
class Helper_Frontend_Settings {

	protected $_data = array();

	public function __construct() {
		$setting_cache_file = OSC_VAR_PATH . '/setting/homepage.php';

		if (!file_exists($setting_cache_file)) {
			/* @var $DB OSC_Database */
			$DB = OSC::core('database');

			$DB->select('*', 'home_settings');

			$setting_items = $DB->fetchArrayAll();

			$SETTINGS = array();

			foreach ($setting_items as $setting_item) {
				if (!$setting_item['tab_key'] || !$setting_item['group_key'] || !$setting_item['item_key'] || !$setting_item['setting_key'] || !$setting_item['input_type']) {
					continue;
				}

				$SETTINGS[$setting_item['setting_key']] = in_array($setting_item['input_type'], array('checkbox', 'multi_select'), true) ? unserialize($setting_item['setting_value']) : $setting_item['setting_value'];
			}

			OSC::writeToFile($setting_cache_file, OSC::core('string')->toPHP($SETTINGS, 'SETTINGS'), array('chmod' => 0600));
		} else {
			include_once $setting_cache_file;
		}

		$this->_data = $SETTINGS;
	}

	public function get($key, $default = null) {
		return isset($this->_data[$key]) ? $this->_data[$key] : $default;
	}

}