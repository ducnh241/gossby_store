<?php

class Controller_Developer_Algolia extends Abstract_Core_Controller {

    public function actionResync() {
        try {
            OSC::helper('catalog/algolia_product')->resync();
        } catch (Exception $ex) {
            dd($ex->getMessage());
        }

        echo 'DONE';
    }

    public function actionCreateIndex() {
        try {
            // setting attribute faceting
            $root_tag = OSC::model('filter/tag')->getCollection()
                ->addField('title')
                ->addCondition('parent_id', 0)
                ->load();
            $attribute_faceting_tag = [];
            foreach ($root_tag as $tag) {
                $attribute_faceting_tag[] = "product_tag.{$tag->data['title']}";
            }

            $attribute_faceting_tag[] = 'filterOnly(supply_location)';
            $attribute_faceting_tag[] = 'filterOnly(selling_type)';

            // Setting config algolia
            $configs = [
                'attributesForFaceting' => $attribute_faceting_tag,
                'searchableAttributes' => [
                    'product_title',
                    'description',
                    'topic',
                    'product_type',
                    'supply_location',
                    'content',
                    'product_tag',
                    'product_variant'
                ]
            ];

            OSC::core('algolia')->settingConfig(ALGOLIA_PRODUCT_INDEX,
                array_merge($configs,
                    [
                        'replicas' => [
                            ALGOLIA_REPLICAS_VIRTUAL_NEWEST,
                            ALGOLIA_REPLICAS_VIRTUAL_BEST_SELL
                        ]
                    ]
                )
            );

            OSC::core('algolia')->settingConfig(ALGOLIA_REPLICAS_VIRTUAL_NEWEST,
                array_merge($configs, [
                    'ranking' => [
                        'desc(added_timestamp)',
                        'typo',
                        'geo',
                        'words',
                        'filters',
                        'proximity',
                        'attribute',
                        'exact',
                        'custom'
                    ]
                ])
            );

            OSC::core('algolia')->settingConfig(ALGOLIA_REPLICAS_VIRTUAL_BEST_SELL,
                array_merge($configs, [
                    'ranking' => [
                        'desc(solds)',
                        'typo',
                        'geo',
                        'words',
                        'filters',
                        'proximity',
                        'attribute',
                        'exact',
                        'custom'
                    ]
                ])
                );
        } catch (Exception $ex) {
            dd($ex);
        }

        echo 'DONE';
    }

    public function actionSearchProduct() {

        $keywords = $this->_request->get('keywords');
        $sort = $this->_request->get('sort');
        try {
            $hits = OSC::helper('catalog/algolia_product')->searchProduct($keywords, ['sort' => $sort]);
            dd($hits);
        } catch (Exception $ex) {
            dd($ex);
        }
    }
}
