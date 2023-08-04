<?php

class Helper_Facebook_Common
{
    public function getFacebookPixelGroupByProductType()
    {
        static $cache_results = null;

        if ($cache_results === null) {
            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getReadAdapter();
            $DB->select('*', 'facebook_pixel_product_type_rel');

            $results = [];
            $facebook_pixel_product_type_rel = $DB->fetchArrayAll();
            foreach ($facebook_pixel_product_type_rel as $value) {
                $results[$value['product_type_id']][] = $value['pixel_id'];
            }

            $cache_results = $results;
        }

        return $cache_results;
    }
}