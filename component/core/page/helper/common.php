<?php

class Helper_Page_Common
{
    const TYPE_ABOUT_US = 'about_us';
    const TYPE_FAQ = 'faq';
    const TYPE_CONTACT_US = 'contact_us';
    const TYPE_PAYMENT_METHOD = 'payment_method';
    const TYPE_POLICY = 'policy';
    const TYPE_TERMS_OF_SERVICE = 'terms_of_service';
    const TYPE_DEFAULT = '';

    const TYPE_OPTIONS = [
        self::TYPE_DEFAULT => 'Default',
        self::TYPE_ABOUT_US => 'About Us',
        self::TYPE_CONTACT_US => 'Contact us',
        self::TYPE_FAQ => 'FAQs',
        self::TYPE_PAYMENT_METHOD => 'Payment Methods',
        self::TYPE_POLICY => 'Policy',
        self::TYPE_TERMS_OF_SERVICE => 'Terms Of Service',
    ];

    public function getOptionPageType()
    {
        return self::TYPE_OPTIONS;
    }

    public function getOptionPageParent()
    {
        $page_parent = [];
        try {
            $pages = OSC::model('page/page')->getCollection()->addCondition('parent_id', 0)->addField('page_id', 'title')->load();
        } catch (Exception $ex) {
            $pages = [];
        }
        if (!empty($pages)) {
            foreach ($pages as $page) {
                $page_parent[$page->data['page_id']] = $page->data['title'];
            }
        }
        return $page_parent;
    }

    public function replaceInfoStore(string $content = '')
    {
        if (empty($content)) {
            return '';
        }

        $patterns = [];
        $replacements = [];
        $site_name = OSC::helper('core/setting')->get('theme/site_name');
        $store_site = OSC_FRONTEND_BASE_URL;
        $store_email = OSC::helper('core/setting')->get('theme/contact/email');

        $patterns[0] = '{{store_name}}';
        $patterns[1] = '{{store_email_address}}';
        $patterns[2] = '{{store_site}}';
        $patterns[3] = '';
        $patterns[4] = '{{display_covid_notify}}';

        $replacements[0] = $site_name;
        $replacements[1] = $store_email;
        $replacements[2] = '<a href="' . $store_site . '"> ' . $store_site . '</a>';
        $replacements[3] = '';
        $replacements[4] = '';

        return str_replace($patterns, $replacements, $content);
    }

    public function getHeadingOfContent($content = '', $tag_heading = 'h4', $min_heading = 3)
    {
        $heading_content = [];
        $content = $this->replaceInfoStore($content);
        $pattern = "/<tag_html[^>]*>(.*?)<\/tag_html>/";
        $pattern = str_replace('tag_html', $tag_heading, $pattern);
        if (preg_match_all($pattern, $content, $matches)) {
            if (is_array($matches) && isset($matches[1]) && count($matches[1]) > $min_heading) {
                $heading_content = $matches[1];
            }
        }
        return $heading_content;
    }

    public function checkDisplayCovidNotify($content = '')
    {
        if (strpos($content, '{{display_covid_notify}}')) {
            return true;
        }
        return false;
    }

    public function formatPageApi(Model_Page_Page_Collection  $collection_collection ) {
        $result = [];

        if ($collection_collection->length() > 0) {

            foreach ($collection_collection as $collection) {

                $result[] = [
                    'title' => $collection->data['title'],
                    'link' => $collection->getDetailUrl(),
                    'image' => OSC::wrapCDN($collection->getImageUrl()),
                ];
            }

        }

        return $result;
    }

    /**
     * @param $slug
     * @return Model_Abstract_Model|null
     * @throws OSC_Database_Model_Exception
     * @throws OSC_Exception_Runtime
     */
    public function loadPageBySlug($slug) {
        $model = OSC::model('page/page')->getCollection()
            ->addCondition('slug', $slug)
            ->load()
            ->first();

        if (!$model instanceof Model_Page_Page  || $model->getId() < 1) {
            throw new OSC_Database_Model_Exception("Model load failed: Slug" . ':' . $slug, 404);
        }

        return $model;
    }
}