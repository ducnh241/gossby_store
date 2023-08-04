<?php

class Helper_Alias_Common
{
    public function validate($slug = '', $module = '', $module_id = 0)
    {
        switch ($module) {
            case 'catalog_product':
                $new_ukey = 'product/' . $module_id;
                break;
            case 'catalog_collection':
                $new_ukey = 'collection/' . $module_id;
                break;
            case 'post':
                $new_ukey = 'post/' . $module_id;
                break;
            case 'post_collection':
                $new_ukey = 'post/collection/' . $module_id;
                break;
            case 'page':
                $new_ukey = 'page/' . $module_id;
                break;
            default:
                throw new Exception("Module key invalid!");
                break;
        }

        $slug = trim($slug, '- ');

        if ($slug) {

            if (!preg_match('/^[a-zA-Z0-9-_]+$/', $slug)) {
                throw new Exception('Slug invalid!');
            }

            $alias_exist = false;
            $ukey = '';

            try {
                $alias = OSC::core('Controller_Alias_Model')->loadBySlug($slug);
                $alias_exist = $alias->isExists();
                $ukey = $alias->data['ukey'];
            } catch (Exception $ex) {

            }

            if ($alias_exist && ($new_ukey != $ukey)) {
                throw new Exception('Slug already exist!');
            }
        } else {
            throw new Exception('Empty Slug!');
        }
    }

    public function save($slug = '', $module = '', $module_id = 0)
    {
        switch ($module) {
            case 'catalog_product':
                $module_key = 'catalog/product';
                $ukey = 'product/' . $module_id;
                $destination = 'catalog/product/' . $module_id;
                break;
            case 'catalog_collection':
                $module_key = 'catalog/collection';
                $ukey = 'collection/' . $module_id;
                $destination = 'catalog/collection/' . $module_id;
                break;
            case 'post':
                $module_key = 'post';
                $ukey = 'post/' . $module_id;
                $destination = 'post/' . $module_id;
                break;
            case 'post_collection':
                $module_key = 'post/collection';
                $ukey = 'post/collection/' . $module_id;
                $destination = 'post/collection/' . $module_id;
                break;
            case 'page':
                $module_key = 'page';
                $ukey = 'page/' . $module_id;
                $destination = 'page/' . $module_id;
                break;
            default:
                throw new Exception("Module key invalid!");
                break;
        }
        $slug = trim($slug);
        $alias_model = new OSC_Controller_Alias_Model();
        if ($slug) {
            try {
                $alias_id = null;
                $this->validate($slug, $module, $module_id);
                try {
                    $alias = $alias_model->loadByUkey($ukey);
                    $alias_id = $alias->data['ukey'];
                } catch (Exception $ex) {

                }
                $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
                $alias_params = [
                    'module_key' => $module_key,
                    'ukey' => $ukey,
                    'slug' => $slug,
                    'lang_key' => $current_lang_key,
                    'destination' => $destination
                ];
                $alias_model->setData($alias_params)->save($alias_id);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

        } else {
            try {
                $alias = $alias_model->loadByUkey($ukey);
                $alias->delete();
            } catch (Exception $ex) {

            }

        }
    }

    public function getSlugByDestination($destination)
    {
        $slug = null;
        try {
            $alias_model = OSC::core('Controller_Alias_Model');
            $alias = $alias_model->loadByDestination($destination);
            $slug = $alias->data['slug'];
        } catch (Exception $ex) {

        }

        return $slug;
    }

    public function trimSlug($slug)
    {
        if (preg_match("/^(collection|blog|page)\/([\w-]+)$/", $slug, $matches)) {
            $slug = $matches[2];
        }
        if (preg_match('/^product\/([a-z0-9]{15})\/((\d+)\/)?([\w-]+)$/i', $slug, $matches)) {
            OSC::register('REWRITE-URL', $matches[4]);
        }
        return $slug;
    }

    public function renameSlugDuplicate($title, $slug, $id, $module_key = 'product')
    {
        /* @var $DB OSC_Database */
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->select('ukey', 'alias', " slug = '{$slug}' ", null, 1, 'check_isset_alias_slug');

        $check_isset = $DB->fetchArray('check_isset_alias_slug');

        $old_ukey = $check_isset ? $check_isset['ukey'] : '';

        $ukey = $module_key . '/' . $id;
        if ($check_isset && $ukey == $old_ukey) {
            return $slug;
        }

        $DB->free('check_isset_alias_slug');

        $condition = "(slug = '{$slug}' OR slug REGEXP '^{$slug}-[0-9]+$') AND ukey != '{$ukey}'";

        $slug_by_alias = $DB->select('slug', 'alias', $condition, null, null, 'select_slug_by_alias')->fetchArrayAll('select_slug_by_alias');

        $DB->free('select_slug_by_alias');

        if (!$slug_by_alias) {
            return $slug;
        }

        $data_number = [];
        $arr_slug = [];

        foreach ($slug_by_alias as $key => $item) {
            $arr_slug[] = $item['slug'];
            $data_number[] = intval(str_replace('-', ' ', str_replace($slug, ' ', $item['slug'])));
        }

        $i = min($data_number);
        $number_slug = 0;

        foreach ($data_number as $key => $value) {
            if (in_array($i + 1, $data_number)) {
                $i++;
                continue;
            } else {
                $number_slug = $i + 1;
                break;
            }
        }

        if (!in_array($slug, array_values($arr_slug))) {
            $alias_slug = $slug;
        } else {
            $alias_slug = $number_slug > 0 ? $slug . '-' . $number_slug : $slug;
        }

        return $alias_slug;
    }
}
