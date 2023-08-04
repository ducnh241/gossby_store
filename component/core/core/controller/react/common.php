<?php

class Controller_Core_React_Common extends Abstract_Frontend_ReactApiController {

    public function actionGetCountries()
    {
        $only_get_countries_active = intval($this->_request->get('only_countries_active', 0));

        $this->apiOutputCaching([
            'only_get_countries_active' => $only_get_countries_active
        ], 0, ['ignore_location']);

        $countries = $only_get_countries_active ? OSC::helper('core/country')->getCountriesActive() : OSC::helper('core/country')->getCountries();
        $this->sendSuccess($countries);
    }

    public function actionGetShippingCountry()
    {
        $this->apiOutputCaching([], 0, ['ignore_location']);

        $countries = OSC::helper('core/country')->getCountriesActive();
        $block_countries = OSC::helper('core/country')->getBlockCountries();
        $countries = array_diff($countries, $block_countries);
        $this->sendSuccess($countries);
    }

    public function actionGetAllCountries()
    {
        $this->apiOutputCaching([], 0, ['ignore_location']);

        $countries = OSC::helper('core/country')->getCountries();
        $this->sendSuccess($countries);
    }

    public function actionGetProvinces()
    {
        $country = trim($this->_request->get('country'));

        $this->apiOutputCaching([
            'country' => $country,
        ], 0, ['ignore_location']);

        $provinces = OSC::helper('core/country')->getProvinces($country);
        $this->sendSuccess($provinces);
    }

    public function actionGetClientIp() {
        $this->sendSuccess(OSC::helper('core/common')->getClientLocation());
    }

    public function actionLogFile()
    {
        $content = trim($this->_request->get('content'));
        $file = trim($this->_request->get('file')) ?: null;

        if (!$content) {
            $this->sendError('Content is required');
        }

        $this->sendSuccess();
    }

    public function actionGetNavigationBar() {
        $this->sendSuccess(OSC::helper('frontend/common')->getNavigationBar());
    }

    public function actionSetAutoCompleteAddressCookie() {
        $this->sendSuccess(OSC::getABTestValue('ab_test_autocomplete_address_countries_range'));
    }
}
