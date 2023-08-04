<?php

class Observer_Facebook_Backend
{

    public static function collectMenu()
    {
        $menus = [];

        if (OSC::controller()->checkPermission('catalog/facebook_pixel', false)) {
            $menus[] = [
                'key' => 'catalog/facebook_pixel',
                'parent_key' => 'catalog',
                'title' => 'Facebook Pixel',
                'position' => -1,
                'url' => OSC::getUrl('facebook/backend/index'),
            ];
        }

        return $menus;
    }

}
