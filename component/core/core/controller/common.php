<?php

class Controller_Core_Common extends Abstract_Core_Controller {
    
    public function actionMigrateUnitData() {
        $data = [
            'suppliers' => [
                [
                    'key' => 'dpi',
                    'title' => 'DPI'
                ],
                [
                    'key' => 'custom_cat',
                    'title' => 'custom_cat'
                ],
                [
                    'key' => 'harrier',
                    'title' => 'Harrier'
                ],
                [
                    'key' => 'prima',
                    'title' => 'Prima'
                ],
                [
                    'key' => 'cw',
                    'title' => 'CW'
                ],
                [
                    'key' => 'tee_launch',
                    'title' => 'Tee Launch'
                ],
                [
                    'key' => 'print_geek',
                    'title' => 'Print Geek'
                ]
            ],
            'product_types' => [
                [
                    'tab' => 'Canvas',
                    'group' => 'Canvas',
                    'key' => 'wrapped_canvas',
                    'title' => 'Wrapped Canvas',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Wrapped Canvas',
                        'photo' => 'Photo Wrapped Canvas',
                        'image' => 'Wrapped Canvas'
                    ],
                    'image' => 'catalog/campaign/type/icon/canvas-portrait.png',
                    'options' => [
                        [
                            'key' => 'canvas_size',
                            'type' => 'select',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '8x10', 'title' => '8"x10"'],
                                ['key' => '8x12', 'title' => '8"x12"'],
                                ['key' => '11x14', 'title' => '11"x14"'],
                                ['key' => '12x18', 'title' => '12"x18"'],
                                ['key' => '12x24', 'title' => '12"x24"'],
                                ['key' => '16x20', 'title' => '16"x20"'],
                                ['key' => '20x24', 'title' => '20"x24"'],
                                ['key' => '20x30', 'title' => '20"x30"'],
                                ['key' => '10x8', 'title' => '10"x8"'],
                                ['key' => '12x8', 'title' => '12"x8"'],
                                ['key' => '14x11', 'title' => '14"x11"'],
                                ['key' => '18x12', 'title' => '18"x12"'],
                                ['key' => '20x16', 'title' => '20"x16"'],
                                ['key' => '24x20', 'title' => '24"x20"'],
                                ['key' => '24x12', 'title' => '24"x12"'],
                                ['key' => '30x20', 'title' => '30"x20"'],
                                ['key' => '12x12', 'title' => '12"x12"'],
                                ['key' => '16x16', 'title' => '16"x16"'],
                                ['key' => '24x24', 'title' => '24"x24"']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['8x10'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1865', 'print_template_id' => 11], 'harrier' => ['sku' => '810SLIMCANVAS', 'print_template_id' => 43]]],
                        ['options' => ['8x12'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF34407', 'print_template_id' => 12]]],
                        ['options' => ['11x14'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1867', 'print_template_id' => 13]]],
                        ['options' => ['12x18'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF34275', 'print_template_id' => 14]]],
                        ['options' => ['12x24'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF34280', 'print_template_id' => 15]]],
                        ['options' => ['16x20'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1868', 'print_template_id' => 16], 'harrier' => ['sku' => '1620SLIMCANVAS', 'print_template_id' => 47], 'prima' => ['sku' => 'SF34276', 'print_template_id' => 46]]],
                        ['options' => ['20x24'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1869', 'print_template_id' => 17], 'harrier' => ['sku' => '2024SLIMCANVAS', 'print_template_id' => 50]]],
                        ['options' => ['20x30'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1870', 'print_template_id' => 18], 'harrier' => ['sku' => '2030SLIMCANVAS', 'print_template_id' => 0], 'prima' => ['sku' => 'SF34277', 'print_template_id' => 52]]],
                        ['options' => ['10x8'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1865', 'print_template_id' => 19, 'meta_data' => ['rotate' => 90]], 'harrier' => ['sku' => '810SLIMCANVAS', 'print_template_id' => 44, 'meta_data' => ['rotate' => 90]]]],
                        ['options' => ['12x8'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF34407', 'print_template_id' => 23, 'meta_data' => ['rotate' => 90]]]],
                        ['options' => ['14x11'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1867', 'print_template_id' => 20, 'meta_data' => ['rotate' => 90]]]],
                        ['options' => ['18x12'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF34275', 'print_template_id' => 24, 'meta_data' => ['rotate' => 90]]]],
                        ['options' => ['20x16'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1868', 'print_template_id' => 21, 'meta_data' => ['rotate' => 90]], 'harrier' => ['sku' => '1620SLIMCANVAS', 'print_template_id' => 49, 'meta_data' => ['rotate' => 90]], 'prima' => ['sku' => 'SF34276', 'print_template_id' => 48, 'meta_data' => ['rotate' => 90]]]],
                        ['options' => ['24x20'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1869', 'print_template_id' => 22, 'meta_data' => ['rotate' => 90]], 'harrier' => ['sku' => '2024SLIMCANVAS', 'print_template_id' => 51, 'meta_data' => ['rotate' => 90]]]],
                        ['options' => ['24x12'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF34280', 'print_template_id' => 25, 'meta_data' => ['rotate' => 90]]]],
                        ['options' => ['30x20'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1870', 'print_template_id' => 26, 'meta_data' => ['rotate' => 90]], 'harrier' => ['sku' => '2030SLIMCANVAS', 'print_template_id' => 0], 'prima' => ['sku' => 'SF34277', 'print_template_id' => 53, 'meta_data' => ['rotate' => 90]]]],
                        ['options' => ['12x12'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1866', 'print_template_id' => 27], 'prima' => ['sku' => 'SF34271', 'print_template_id' => 45]]],
                        ['options' => ['16x16'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF34272', 'print_template_id' => 28]]],
                        ['options' => ['24x24'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF34283', 'print_template_id' => 29]]]
                    ]
                ],
                [
                    'tab' => 'Poster',
                    'group' => 'Poster',
                    'key' => 'matte_poster',
                    'title' => 'Matte Poster',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Poster',
                        'photo' => 'Photo Poster',
                        'image' => 'Poster'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'poster_size',
                            'type' => 'select',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '10x15', 'title' => '10"x15"'],
                                ['key' => '12x18', 'title' => '12"x18"'],
                                ['key' => '16x20', 'title' => '16"x20"'],
                                ['key' => '20x30', 'title' => '20"x30"'],
                                ['key' => '24x36', 'title' => '24"x36"'],
                                ['key' => '30x40', 'title' => '30"x40"'],
                                ['key' => '20x24', 'title' => '20"x24"'],
                                ['key' => '12x12', 'title' => '12"x12"'],
                                ['key' => '11x14', 'title' => '11"x14"']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['10x15'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['harrier' => ['sku' => 'poster1015', 'print_template_id' => 0]]],
                        ['options' => ['12x18'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['harrier' => ['sku' => 'poster1218', 'print_template_id' => 0]]],
                        ['options' => ['16x20'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '231', 'print_template_id' => 0], 'harrier' => ['sku' => 'poster1620', 'print_template_id' => 0]]],
                        ['options' => ['20x30'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '233', 'print_template_id' => 0], 'harrier' => ['sku' => 'poster2030', 'print_template_id' => 0]]],
                        ['options' => ['24x36'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '244', 'print_template_id' => 0], 'harrier' => ['sku' => 'poster2436', 'print_template_id' => 0]]],
                        ['options' => ['30x40'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['harrier' => ['sku' => 'poster3040', 'print_template_id' => 0]]],
                        ['options' => ['20x24'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '235', 'print_template_id' => 0]]],
                        ['options' => ['12x12'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '197', 'print_template_id' => 0]]],
                        ['options' => ['11x14'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '140', 'print_template_id' => 0]]]
                    ]
                ],
                [
                    'tab' => 'Poster',
                    'group' => 'Poster',
                    'key' => 'glossy_poster',
                    'title' => 'Glossy Poster',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Poster',
                        'photo' => 'Photo Poster',
                        'image' => 'Poster'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'poster_size',
                            'type' => 'select',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '10x15', 'title' => '10"x15"'],
                                ['key' => '11x14', 'title' => '11"x14"'],
                                ['key' => '12x16', 'title' => '12"x16"'],
                                ['key' => '16x20', 'title' => '16"x20"'],
                                ['key' => '20x24', 'title' => '20"x24"'],
                                ['key' => '20x30', 'title' => '20"x30"'],
                                ['key' => '15x10', 'title' => '15"x10"'],
                                ['key' => '14x11', 'title' => '14"x11"'],
                                ['key' => '16x12', 'title' => '16"x12"'],
                                ['key' => '20x16', 'title' => '20"x16"'],
                                ['key' => '24x20', 'title' => '24"x20"'],
                                ['key' => '30x20', 'title' => '30"x20"']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['10x15'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'CommerceProduct_68260', 'print_template_id' => 0]]],
                        ['options' => ['11x14'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'CommerceProduct_68261', 'print_template_id' => 0]]],
                        ['options' => ['12x16'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'CommerceProduct_68262', 'print_template_id' => 0]]],
                        ['options' => ['16x20'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF1402G', 'print_template_id' => 0]]],
                        ['options' => ['20x24'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF1406G', 'print_template_id' => 0]]],
                        ['options' => ['20x30'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF1403G', 'print_template_id' => 0]]],
                        ['options' => ['15x10'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'CommerceProduct_68260', 'print_template_id' => 0]]],
                        ['options' => ['14x11'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'CommerceProduct_68261', 'print_template_id' => 0]]],
                        ['options' => ['16x12'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'CommerceProduct_68262', 'print_template_id' => 0]]],
                        ['options' => ['20x16'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF1402G', 'print_template_id' => 0]]],
                        ['options' => ['24x20'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF1406G', 'print_template_id' => 0]]],
                        ['options' => ['30x20'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'SF1403G', 'print_template_id' => 0]]],
                    ]
                ],
                [
                    'tab' => 'Apparel',
                    'group' => 'Shirts',
                    'key' => 'gildan_g500_classic_tee',
                    'title' => 'Gildan G500 Classic Tee',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Shirt',
                        'photo' => 'Photo Shirt',
                        'image' => 'Shirt'
                    ],
                    'image' => 'catalog/campaign/type/icon/classicTee/front.png',
                    'options' => [
                        [
                            'key' => 'gildan_g500_classic_tee_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'black', 'title' => 'Black', 'meta_data' => ['hex' => '#000']],
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#EEE']],
                                ['key' => 'sport_grey', 'title' => 'Sport Grey', 'meta_data' => ['hex' => '#bcbcc6']],
                                ['key' => 'navy', 'title' => 'Navy', 'meta_data' => ['hex' => '#2f3b4e']],
                                ['key' => 'light_blue', 'title' => 'Light Blue', 'meta_data' => ['hex' => '#9ab5d2']],
                                ['key' => 'cardinal', 'title' => 'Cardinal', 'meta_data' => ['hex' => '#9c263a']],
                                ['key' => 'dark_chocolate', 'title' => 'Dark Chocolate', 'meta_data' => ['hex' => '#4c3025']],
                                ['key' => 'gold', 'title' => 'Gold', 'meta_data' => ['hex' => '#fea821']],
                                ['key' => 'irish_green', 'title' => 'Irish Green', 'meta_data' => ['hex' => '#259b5e']],
                                ['key' => 'light_pink', 'title' => 'Light Pink', 'meta_data' => ['hex' => '#e2b5c9']],
                                ['key' => 'orange', 'title' => 'Orange', 'meta_data' => ['hex' => '#ff3300']],
                                ['key' => 'purple', 'title' => 'Purple', 'meta_data' => ['hex' => '#5c4881']],
                                ['key' => 'red', 'title' => 'Red', 'meta_data' => ['hex' => '#cd0102']],
                                ['key' => 'royal', 'title' => 'Royal', 'meta_data' => ['hex' => '#1d4e9a']],
                                ['key' => 'kiwi', 'title' => 'Kiwi', 'meta_data' => ['hex' => '#8cac69']],
                                ['key' => 'military_green', 'title' => 'Military Green', 'meta_data' => ['hex' => '#4b4d34']],
                                ['key' => 'ash', 'title' => 'Ash', 'meta_data' => ['hex' => '#e6e7ec']]
                            ]
                        ],
                        [
                            'key' => 'shirt_size',
                            'type' => 'select',
                            'title' => 'Size',
                            'values' => [
                                ['key' => 'xs', 'title' => 'XS'],
                                ['key' => 's', 'title' => 'S'],
                                ['key' => 'm', 'title' => 'M'],
                                ['key' => 'l', 'title' => 'L'],
                                ['key' => 'xl', 'title' => 'XL'],
                                ['key' => '2xl', 'title' => '2XL'],
                                ['key' => '3xl', 'title' => '3XL'],
                                ['key' => '4xl', 'title' => '4XL'],
                                ['key' => '5xl', 'title' => '5XL']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['black', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['cw' => ['sku' => 'N0302017-BK-XS', 'print_template_id' => 42]]],
                        ['options' => ['black', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48144', 'print_template_id' => 42], 'cw' => ['sku' => 'N0302017-BK-S', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48144', 'print_template_id' => 42]]],
                        ['options' => ['black', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48145', 'print_template_id' => 42], 'cw' => ['sku' => 'N0302017-BK-M', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48145', 'print_template_id' => 42]]],
                        ['options' => ['black', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48146', 'print_template_id' => 42], 'cw' => ['sku' => 'N0302017-BK-l', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48146', 'print_template_id' => 42]]],
                        ['options' => ['black', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48147', 'print_template_id' => 42], 'cw' => ['sku' => 'N0302017-BK-XL', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48147', 'print_template_id' => 42]]],
                        ['options' => ['black', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48148', 'print_template_id' => 42], 'cw' => ['sku' => 'N0302017-BK-XXL', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48148', 'print_template_id' => 42]]],
                        ['options' => ['black', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48149', 'print_template_id' => 42], 'cw' => ['sku' => 'N0302017-BK-3XL', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48149', 'print_template_id' => 42]]],
                        ['options' => ['black', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48150', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48150', 'print_template_id' => 42]]],
                        ['options' => ['black', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48151', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48151', 'print_template_id' => 42]]],
                        ['options' => ['white', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['cw' => ['sku' => 'N0302017-WH-XS', 'print_template_id' => 42]]],
                        ['options' => ['white', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48300', 'print_template_id' => 42], 'cw' => ['sku' => 'N0302017-WH-S', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48300', 'print_template_id' => 42]]],
                        ['options' => ['white', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48301', 'print_template_id' => 42], 'cw' => ['sku' => 'N0302017-WH-M', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48301', 'print_template_id' => 42]]],
                        ['options' => ['white', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48302', 'print_template_id' => 42], 'cw' => ['sku' => 'N0302017-WH-L', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48302', 'print_template_id' => 42]]],
                        ['options' => ['white', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48303', 'print_template_id' => 42], 'cw' => ['sku' => 'N0302017-WH-XL', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48303', 'print_template_id' => 42]]],
                        ['options' => ['white', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48304', 'print_template_id' => 42], 'cw' => ['sku' => 'N0302017-WH-XXL', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48304', 'print_template_id' => 42]]],
                        ['options' => ['white', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48305', 'print_template_id' => 42], 'cw' => ['sku' => 'N0302017-WH-3XL', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48305', 'print_template_id' => 42]]],
                        ['options' => ['white', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48306', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48306', 'print_template_id' => 42]]],
                        ['options' => ['white', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48307', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48307', 'print_template_id' => 42]]],
                        ['options' => ['sport_grey', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48200', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48200', 'print_template_id' => 42]]],
                        ['options' => ['sport_grey', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48201', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48201', 'print_template_id' => 42]]],
                        ['options' => ['sport_grey', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48202', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48202', 'print_template_id' => 42]]],
                        ['options' => ['sport_grey', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48203', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48203', 'print_template_id' => 42]]],
                        ['options' => ['sport_grey', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48204', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48204', 'print_template_id' => 42]]],
                        ['options' => ['sport_grey', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48205', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48205', 'print_template_id' => 42]]],
                        ['options' => ['sport_grey', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48206', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48206', 'print_template_id' => 42]]],
                        ['options' => ['sport_grey', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48207', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48207', 'print_template_id' => 42]]],
                        ['options' => ['navy', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48248', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48248', 'print_template_id' => 42]]],
                        ['options' => ['navy', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48249', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48249', 'print_template_id' => 42]]],
                        ['options' => ['navy', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48250', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48250', 'print_template_id' => 42]]],
                        ['options' => ['navy', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48251', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48251', 'print_template_id' => 42]]],
                        ['options' => ['navy', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48252', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48252', 'print_template_id' => 42]]],
                        ['options' => ['navy', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48253', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48253', 'print_template_id' => 42]]],
                        ['options' => ['navy', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48254', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48254', 'print_template_id' => 42]]],
                        ['options' => ['navy', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48255', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48255', 'print_template_id' => 42]]],
                        ['options' => ['light_blue', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48168', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48168', 'print_template_id' => 42]]],
                        ['options' => ['light_blue', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48169', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48169', 'print_template_id' => 42]]],
                        ['options' => ['light_blue', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48170', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48170', 'print_template_id' => 42]]],
                        ['options' => ['light_blue', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48171', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48171', 'print_template_id' => 42]]],
                        ['options' => ['light_blue', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48172', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48172', 'print_template_id' => 42]]],
                        ['options' => ['light_blue', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48173', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48173', 'print_template_id' => 42]]],
                        ['options' => ['light_blue', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48174', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48174', 'print_template_id' => 42]]],
                        ['options' => ['light_blue', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48175', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48175', 'print_template_id' => 42]]],
                        ['options' => ['cardinal', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48308', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48308', 'print_template_id' => 42]]],
                        ['options' => ['cardinal', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48309', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48309', 'print_template_id' => 42]]],
                        ['options' => ['cardinal', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48310', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48310', 'print_template_id' => 42]]],
                        ['options' => ['cardinal', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48311', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48311', 'print_template_id' => 42]]],
                        ['options' => ['cardinal', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48312', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48312', 'print_template_id' => 42]]],
                        ['options' => ['cardinal', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48313', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48313', 'print_template_id' => 42]]],
                        ['options' => ['cardinal', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48314', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48314', 'print_template_id' => 42]]],
                        ['options' => ['cardinal', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48315', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48315', 'print_template_id' => 42]]],
                        ['options' => ['dark_chocolate', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48152', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48152', 'print_template_id' => 42]]],
                        ['options' => ['dark_chocolate', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48153', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48153', 'print_template_id' => 42]]],
                        ['options' => ['dark_chocolate', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48154', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48154', 'print_template_id' => 42]]],
                        ['options' => ['dark_chocolate', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48155', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48155', 'print_template_id' => 42]]],
                        ['options' => ['dark_chocolate', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48156', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48156', 'print_template_id' => 42]]],
                        ['options' => ['dark_chocolate', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48157', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48157', 'print_template_id' => 42]]],
                        ['options' => ['dark_chocolate', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48158', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48158', 'print_template_id' => 42]]],
                        ['options' => ['dark_chocolate', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48159', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48159', 'print_template_id' => 42]]],
                        ['options' => ['gold', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48176', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48176', 'print_template_id' => 42]]],
                        ['options' => ['gold', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48177', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48177', 'print_template_id' => 42]]],
                        ['options' => ['gold', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48178', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48178', 'print_template_id' => 42]]],
                        ['options' => ['gold', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48179', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48179', 'print_template_id' => 42]]],
                        ['options' => ['gold', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48180', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48180', 'print_template_id' => 42]]],
                        ['options' => ['gold', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48181', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48181', 'print_template_id' => 42]]],
                        ['options' => ['gold', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48182', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48182', 'print_template_id' => 42]]],
                        ['options' => ['gold', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48183', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48183', 'print_template_id' => 42]]],
                        ['options' => ['irish_green', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48216', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48216', 'print_template_id' => 42]]],
                        ['options' => ['irish_green', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48217', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48217', 'print_template_id' => 42]]],
                        ['options' => ['irish_green', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48218', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48218', 'print_template_id' => 42]]],
                        ['options' => ['irish_green', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48219', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48219', 'print_template_id' => 42]]],
                        ['options' => ['irish_green', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48220', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48220', 'print_template_id' => 42]]],
                        ['options' => ['irish_green', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48221', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48221', 'print_template_id' => 42]]],
                        ['options' => ['irish_green', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48222', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48222', 'print_template_id' => 42]]],
                        ['options' => ['irish_green', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48223', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48223', 'print_template_id' => 42]]],
                        ['options' => ['light_pink', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48224', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48224', 'print_template_id' => 42]]],
                        ['options' => ['light_pink', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48225', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48225', 'print_template_id' => 42]]],
                        ['options' => ['light_pink', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48226', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48226', 'print_template_id' => 42]]],
                        ['options' => ['light_pink', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48227', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48227', 'print_template_id' => 42]]],
                        ['options' => ['light_pink', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48228', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48228', 'print_template_id' => 42]]],
                        ['options' => ['light_pink', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48229', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48229', 'print_template_id' => 42]]],
                        ['options' => ['light_pink', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48230', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48230', 'print_template_id' => 42]]],
                        ['options' => ['light_pink', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48231', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48231', 'print_template_id' => 42]]],
                        ['options' => ['orange', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48262', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48262', 'print_template_id' => 42]]],
                        ['options' => ['orange', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48263', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48263', 'print_template_id' => 42]]],
                        ['options' => ['orange', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48264', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48264', 'print_template_id' => 42]]],
                        ['options' => ['orange', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48265', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48265', 'print_template_id' => 42]]],
                        ['options' => ['orange', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48266', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48266', 'print_template_id' => 42]]],
                        ['options' => ['orange', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48267', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48267', 'print_template_id' => 42]]],
                        ['options' => ['orange', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48268', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48268', 'print_template_id' => 42]]],
                        ['options' => ['orange', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48269', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48269', 'print_template_id' => 42]]],
                        ['options' => ['purple', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48270', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48270', 'print_template_id' => 42]]],
                        ['options' => ['purple', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48271', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48271', 'print_template_id' => 42]]],
                        ['options' => ['purple', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48272', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48272', 'print_template_id' => 42]]],
                        ['options' => ['purple', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48273', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48273', 'print_template_id' => 42]]],
                        ['options' => ['purple', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48274', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48274', 'print_template_id' => 42]]],
                        ['options' => ['purple', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48275', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48275', 'print_template_id' => 42]]],
                        ['options' => ['purple', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48276', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48276', 'print_template_id' => 42]]],
                        ['options' => ['purple', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48277', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48277', 'print_template_id' => 42]]],
                        ['options' => ['red', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48278', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48278', 'print_template_id' => 42]]],
                        ['options' => ['red', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48279', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48279', 'print_template_id' => 42]]],
                        ['options' => ['red', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48280', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48280', 'print_template_id' => 42]]],
                        ['options' => ['red', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48281', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48281', 'print_template_id' => 42]]],
                        ['options' => ['red', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48282', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48282', 'print_template_id' => 42]]],
                        ['options' => ['red', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48283', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48283', 'print_template_id' => 42]]],
                        ['options' => ['red', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48284', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48284', 'print_template_id' => 42]]],
                        ['options' => ['red', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48285', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48285', 'print_template_id' => 42]]],
                        ['options' => ['royal', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48286', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48286', 'print_template_id' => 42]]],
                        ['options' => ['royal', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48287', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48287', 'print_template_id' => 42]]],
                        ['options' => ['royal', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48288', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48288', 'print_template_id' => 42]]],
                        ['options' => ['royal', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48289', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48289', 'print_template_id' => 42]]],
                        ['options' => ['royal', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48290', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48290', 'print_template_id' => 42]]],
                        ['options' => ['royal', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48291', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48291', 'print_template_id' => 42]]],
                        ['options' => ['royal', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48292', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48292', 'print_template_id' => 42]]],
                        ['options' => ['royal', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48293', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48293', 'print_template_id' => 42]]],
                        ['options' => ['kiwi', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48393', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48393', 'print_template_id' => 42]]],
                        ['options' => ['kiwi', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48394', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48394', 'print_template_id' => 42]]],
                        ['options' => ['kiwi', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48395', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48395', 'print_template_id' => 42]]],
                        ['options' => ['kiwi', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48396', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48396', 'print_template_id' => 42]]],
                        ['options' => ['kiwi', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48397', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48397', 'print_template_id' => 42]]],
                        ['options' => ['kiwi', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48398', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48398', 'print_template_id' => 42]]],
                        ['options' => ['kiwi', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48399', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48399', 'print_template_id' => 42]]],
                        ['options' => ['kiwi', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48400', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48400', 'print_template_id' => 42]]],
                        ['options' => ['military_green', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '55488', 'print_template_id' => 42], 'tee_launch' => ['sku' => '55488', 'print_template_id' => 42]]],
                        ['options' => ['military_green', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '55489', 'print_template_id' => 42], 'tee_launch' => ['sku' => '55489', 'print_template_id' => 42]]],
                        ['options' => ['military_green', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '55490', 'print_template_id' => 42], 'tee_launch' => ['sku' => '55490', 'print_template_id' => 42]]],
                        ['options' => ['military_green', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '55491', 'print_template_id' => 42], 'tee_launch' => ['sku' => '55491', 'print_template_id' => 42]]],
                        ['options' => ['military_green', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '55492', 'print_template_id' => 42], 'tee_launch' => ['sku' => '55492', 'print_template_id' => 42]]],
                        ['options' => ['military_green', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '55493', 'print_template_id' => 42], 'tee_launch' => ['sku' => '55493', 'print_template_id' => 42]]],
                        ['options' => ['military_green', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '55494', 'print_template_id' => 42], 'tee_launch' => ['sku' => '55494', 'print_template_id' => 42]]],
                        ['options' => ['military_green', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '55495', 'print_template_id' => 42], 'tee_launch' => ['sku' => '55495', 'print_template_id' => 42]]],
                        ['options' => ['ash', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48184', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48184', 'print_template_id' => 42]]],
                        ['options' => ['ash', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48185', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48185', 'print_template_id' => 42]]],
                        ['options' => ['ash', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48186', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48186', 'print_template_id' => 42]]],
                        ['options' => ['ash', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48187', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48187', 'print_template_id' => 42]]],
                        ['options' => ['ash', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48188', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48188', 'print_template_id' => 42]]],
                        ['options' => ['ash', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48189', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48189', 'print_template_id' => 42]]],
                        ['options' => ['ash', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48190', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48190', 'print_template_id' => 42]]],
                        ['options' => ['ash', '5xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '48191', 'print_template_id' => 42], 'tee_launch' => ['sku' => '48191', 'print_template_id' => 42]]],
                    ]
                ],
                [
                    'tab' => 'Apparel',
                    'group' => 'Shirts',
                    'key' => 'bella_canvas_3001c_unisex_jersey_short_sleeve',
                    'title' => 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Shirt',
                        'photo' => 'Photo Shirt',
                        'image' => 'Shirt'
                    ],
                    'image' => 'catalog/campaign/type/icon/bellaCanvasTee/front.png',
                    'options' => [
                        [
                            'key' => 'bella_canvas_3001c_unisex_jersey_short_sleeve_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'black', 'title' => 'Black', 'meta_data' => ['hex' => '#000000']],
                                ['key' => 'dark_grey_heather', 'title' => 'Dark Grey Heather', 'meta_data' => ['hex' => '#302e2f']],
                                ['key' => 'light_blue', 'title' => 'Light Blue', 'meta_data' => ['hex' => '#9cb4cc']],
                                ['key' => 'navy', 'title' => 'Navy', 'meta_data' => ['hex' => '#292838']],
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#EEE']],
                                ['key' => 'canvas_red', 'title' => 'Canvas Red', 'meta_data' => ['hex' => '#9f1d2a']],
                                ['key' => 'cardinal', 'title' => 'Cardinal', 'meta_data' => ['hex' => '#6c2333']],
                                ['key' => 'gold', 'title' => 'Gold', 'meta_data' => ['hex' => '#ffa624']],
                                ['key' => 'orange', 'title' => 'Orange', 'meta_data' => ['hex' => '#fa8b31']],
                                ['key' => 'soft_pink', 'title' => 'Soft Pink', 'meta_data' => ['hex' => '#f5e4ec']],
                                ['key' => 'asphalt', 'title' => 'Asphalt', 'meta_data' => ['hex' => '#43474a']],
                                ['key' => 'heather_royal', 'title' => 'Heather Royal', 'meta_data' => ['hex' => '#364c81']],
                                ['key' => 'kelly', 'title' => 'Kelly', 'meta_data' => ['hex' => '#036650']],
                            ]
                        ],
                        [
                            'key' => 'shirt_size',
                            'type' => 'select',
                            'title' => 'Size',
                            'values' => [
                                ['key' => 'xs', 'title' => 'XS'],
                                ['key' => 's', 'title' => 'S'],
                                ['key' => 'm', 'title' => 'M'],
                                ['key' => 'l', 'title' => 'L'],
                                ['key' => 'xl', 'title' => 'XL'],
                                ['key' => '2xl', 'title' => '2XL'],
                                ['key' => '3xl', 'title' => '3XL'],
                                ['key' => '4xl', 'title' => '4XL'],
                                ['key' => '5xl', 'title' => '5XL']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['black', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45474', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45474', 'print_template_id' => 58]]],
                        ['options' => ['black', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45475', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45475', 'print_template_id' => 58]]],
                        ['options' => ['black', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45476', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45476', 'print_template_id' => 58]]],
                        ['options' => ['black', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45478', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45478', 'print_template_id' => 58]]],
                        ['options' => ['black', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45479', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45479', 'print_template_id' => 58]]],
                        ['options' => ['black', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45480', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45480', 'print_template_id' => 58]]],
                        ['options' => ['black', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45481', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45481', 'print_template_id' => 58]]],
                        ['options' => ['black', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45482', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45482', 'print_template_id' => 58]]],
                        ['options' => ['dark_grey_heather', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45515', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45515', 'print_template_id' => 58]]],
                        ['options' => ['dark_grey_heather', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45516', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45516', 'print_template_id' => 58]]],
                        ['options' => ['dark_grey_heather', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45517', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45517', 'print_template_id' => 58]]],
                        ['options' => ['dark_grey_heather', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45518', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45518', 'print_template_id' => 58]]],
                        ['options' => ['dark_grey_heather', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45519', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45519', 'print_template_id' => 58]]],
                        ['options' => ['dark_grey_heather', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45520', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45520', 'print_template_id' => 58]]],
                        ['options' => ['dark_grey_heather', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45521', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45521', 'print_template_id' => 58]]],
                        ['options' => ['dark_grey_heather', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45522', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45522', 'print_template_id' => 58]]],
                        ['options' => ['light_blue', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45523', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45523', 'print_template_id' => 58]]],
                        ['options' => ['light_blue', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45524', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45524', 'print_template_id' => 58]]],
                        ['options' => ['light_blue', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45525', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45525', 'print_template_id' => 58]]],
                        ['options' => ['light_blue', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45526', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45526', 'print_template_id' => 58]]],
                        ['options' => ['light_blue', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45527', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45527', 'print_template_id' => 58]]],
                        ['options' => ['light_blue', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45528', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45528', 'print_template_id' => 58]]],
                        ['options' => ['light_blue', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45529', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45529', 'print_template_id' => 58]]],
                        ['options' => ['light_blue', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45530', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45530', 'print_template_id' => 58]]],
                        ['options' => ['navy', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45539', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45539', 'print_template_id' => 58]]],
                        ['options' => ['navy', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45540', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45540', 'print_template_id' => 58]]],
                        ['options' => ['navy', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45541', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45541', 'print_template_id' => 58]]],
                        ['options' => ['navy', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45542', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45542', 'print_template_id' => 58]]],
                        ['options' => ['navy', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45543', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45543', 'print_template_id' => 58]]],
                        ['options' => ['navy', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45544', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45544', 'print_template_id' => 58]]],
                        ['options' => ['navy', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45545', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45545', 'print_template_id' => 58]]],
                        ['options' => ['navy', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45546', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45546', 'print_template_id' => 58]]],
                        ['options' => ['white', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45603', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45603', 'print_template_id' => 58]]],
                        ['options' => ['white', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45604', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45604', 'print_template_id' => 58]]],
                        ['options' => ['white', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45605', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45605', 'print_template_id' => 58]]],
                        ['options' => ['white', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45606', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45606', 'print_template_id' => 58]]],
                        ['options' => ['white', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45607', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45607', 'print_template_id' => 58]]],
                        ['options' => ['white', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45608', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45608', 'print_template_id' => 58]]],
                        ['options' => ['white', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45609', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45609', 'print_template_id' => 58]]],
                        ['options' => ['white', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45610', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45610', 'print_template_id' => 58]]],
                        ['options' => ['canvas_red', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45571', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45571', 'print_template_id' => 58]]],
                        ['options' => ['canvas_red', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45572', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45572', 'print_template_id' => 58]]],
                        ['options' => ['canvas_red', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45573', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45573', 'print_template_id' => 58]]],
                        ['options' => ['canvas_red', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45574', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45574', 'print_template_id' => 58]]],
                        ['options' => ['canvas_red', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45575', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45575', 'print_template_id' => 58]]],
                        ['options' => ['canvas_red', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45576', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45576', 'print_template_id' => 58]]],
                        ['options' => ['canvas_red', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45577', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45577', 'print_template_id' => 58]]],
                        ['options' => ['canvas_red', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45578', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45578', 'print_template_id' => 58]]],
                        ['options' => ['cardinal', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45579', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45579', 'print_template_id' => 58]]],
                        ['options' => ['cardinal', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45580', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45580', 'print_template_id' => 58]]],
                        ['options' => ['cardinal', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45581', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45581', 'print_template_id' => 58]]],
                        ['options' => ['cardinal', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45582', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45582', 'print_template_id' => 58]]],
                        ['options' => ['cardinal', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45583', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45583', 'print_template_id' => 58]]],
                        ['options' => ['cardinal', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45584', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45584', 'print_template_id' => 58]]],
                        ['options' => ['cardinal', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45585', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45585', 'print_template_id' => 58]]],
                        ['options' => ['cardinal', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45586', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45586', 'print_template_id' => 58]]],
                        ['options' => ['gold', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45491', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45491', 'print_template_id' => 58]]],
                        ['options' => ['gold', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45492', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45492', 'print_template_id' => 58]]],
                        ['options' => ['gold', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45493', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45493', 'print_template_id' => 58]]],
                        ['options' => ['gold', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45494', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45494', 'print_template_id' => 58]]],
                        ['options' => ['gold', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45495', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45495', 'print_template_id' => 58]]],
                        ['options' => ['gold', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45496', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45496', 'print_template_id' => 58]]],
                        ['options' => ['gold', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45497', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45497', 'print_template_id' => 58]]],
                        ['options' => ['gold', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45498', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45498', 'print_template_id' => 58]]],
                        ['options' => ['orange', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45547', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45547', 'print_template_id' => 58]]],
                        ['options' => ['orange', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45548', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45548', 'print_template_id' => 58]]],
                        ['options' => ['orange', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45549', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45549', 'print_template_id' => 58]]],
                        ['options' => ['orange', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45550', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45550', 'print_template_id' => 58]]],
                        ['options' => ['orange', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45551', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45551', 'print_template_id' => 58]]],
                        ['options' => ['orange', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45552', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45552', 'print_template_id' => 58]]],
                        ['options' => ['orange', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45553', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45553', 'print_template_id' => 58]]],
                        ['options' => ['orange', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45554', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45554', 'print_template_id' => 58]]],
                        ['options' => ['soft_pink', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45563', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45563', 'print_template_id' => 58]]],
                        ['options' => ['soft_pink', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45564', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45564', 'print_template_id' => 58]]],
                        ['options' => ['soft_pink', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45565', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45565', 'print_template_id' => 58]]],
                        ['options' => ['soft_pink', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45566', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45566', 'print_template_id' => 58]]],
                        ['options' => ['soft_pink', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45567', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45567', 'print_template_id' => 58]]],
                        ['options' => ['soft_pink', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45568', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45568', 'print_template_id' => 58]]],
                        ['options' => ['soft_pink', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45569', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45569', 'print_template_id' => 58]]],
                        ['options' => ['soft_pink', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '45570', 'print_template_id' => 58], 'tee_launch' => ['sku' => '45570', 'print_template_id' => 58]]],
                        ['options' => ['asphalt', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54621', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54621', 'print_template_id' => 58]]],
                        ['options' => ['asphalt', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54622', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54622', 'print_template_id' => 58]]],
                        ['options' => ['asphalt', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54624', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54624', 'print_template_id' => 58]]],
                        ['options' => ['asphalt', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54625', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54625', 'print_template_id' => 58]]],
                        ['options' => ['asphalt', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54626', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54626', 'print_template_id' => 58]]],
                        ['options' => ['asphalt', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54627', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54627', 'print_template_id' => 58]]],
                        ['options' => ['asphalt', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54628', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54628', 'print_template_id' => 58]]],
                        ['options' => ['asphalt', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54629', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54629', 'print_template_id' => 58]]],
                        ['options' => ['heather_royal', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54612', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54612', 'print_template_id' => 58]]],
                        ['options' => ['heather_royal', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54614', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54614', 'print_template_id' => 58]]],
                        ['options' => ['heather_royal', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54615', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54615', 'print_template_id' => 58]]],
                        ['options' => ['heather_royal', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54616', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54616', 'print_template_id' => 58]]],
                        ['options' => ['heather_royal', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54617', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54617', 'print_template_id' => 58]]],
                        ['options' => ['heather_royal', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54618', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54618', 'print_template_id' => 58]]],
                        ['options' => ['heather_royal', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54619', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54619', 'print_template_id' => 58]]],
                        ['options' => ['heather_royal', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54620', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54620', 'print_template_id' => 58]]],
                        ['options' => ['kelly', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54638', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54638', 'print_template_id' => 58]]],
                        ['options' => ['kelly', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54639', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54639', 'print_template_id' => 58]]],
                        ['options' => ['kelly', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54640', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54640', 'print_template_id' => 58]]],
                        ['options' => ['kelly', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54641', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54641', 'print_template_id' => 58]]],
                        ['options' => ['kelly', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54642', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54642', 'print_template_id' => 58]]],
                        ['options' => ['kelly', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54643', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54643', 'print_template_id' => 58]]],
                        ['options' => ['kelly', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54644', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54644', 'print_template_id' => 58]]],
                        ['options' => ['kelly', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54645', 'print_template_id' => 58], 'tee_launch' => ['sku' => '54645', 'print_template_id' => 58]]]
                    ]
                ],
                [
                    'tab' => 'Apparel',
                    'group' => 'Shirts',
                    'key' => 'next_level_nl3600_premium_short_sleeve',
                    'title' => 'Next Level NL3600 Premium Short Sleeve',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Shirt',
                        'photo' => 'Photo Shirt',
                        'image' => 'Shirt'
                    ],
                    'image' => 'catalog/campaign/type/icon/nextLevelTee/front.png',
                    'options' => [
                        [
                            'key' => 'next_level_nl3600_premium_short_sleeve_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'black', 'title' => 'Black', 'meta_data' => ['hex' => '#000']],
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#EEE']],
                                ['key' => 'heather_grey', 'title' => 'Heather Grey', 'meta_data' => ['hex' => '#b3b2ba']],
                                ['key' => 'midnight_navy', 'title' => 'Midnight Navy', 'meta_data' => ['hex' => '#212632']],
                                ['key' => 'military_green', 'title' => 'Military Green', 'meta_data' => ['hex' => '#534e3f']],
                                ['key' => 'cardinal', 'title' => 'Cardinal', 'meta_data' => ['hex' => '#651728']],
                                ['key' => 'kelly_green', 'title' => 'Kelly Green', 'meta_data' => ['hex' => '#0b7140']],
                                ['key' => 'light_blue', 'title' => 'Light Blue', 'meta_data' => ['hex' => '#aac0cd']],
                                ['key' => 'red', 'title' => 'Red', 'meta_data' => ['hex' => '#bb0321']],
                                ['key' => 'royal', 'title' => 'royal', 'meta_data' => ['hex' => '#1c3c6f']],
                                ['key' => 'tahiti_blue', 'title' => 'Tahiti Blue', 'meta_data' => ['hex' => '#2a8d95']],
                                ['key' => 'banana_cream', 'title' => 'Banana Cream', 'meta_data' => ['hex' => '#efdeab']],
                                ['key' => 'maroon', 'title' => 'Maroon', 'meta_data' => ['hex' => '#4e1323']],
                                ['key' => 'purple_rush', 'title' => 'Purple Rush', 'meta_data' => ['hex' => '#47346c']],
                            ]
                        ],
                        [
                            'key' => 'shirt_size',
                            'type' => 'select',
                            'title' => 'Size',
                            'values' => [
                                ['key' => 'xs', 'title' => 'XS'],
                                ['key' => 's', 'title' => 'S'],
                                ['key' => 'm', 'title' => 'M'],
                                ['key' => 'l', 'title' => 'L'],
                                ['key' => 'xl', 'title' => 'XL'],
                                ['key' => '2xl', 'title' => '2XL'],
                                ['key' => '3xl', 'title' => '3XL'],
                                ['key' => '4xl', 'title' => '4XL'],
                                ['key' => '5xl', 'title' => '5XL']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['black', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39170', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39170', 'print_template_id' => 59]]],
                        ['options' => ['black', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39409', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39409', 'print_template_id' => 59]]],
                        ['options' => ['black', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39410', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39410', 'print_template_id' => 59]]],
                        ['options' => ['black', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39411', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39411', 'print_template_id' => 59]]],
                        ['options' => ['black', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39412', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39412', 'print_template_id' => 59]]],
                        ['options' => ['black', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39413', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39413', 'print_template_id' => 59]]],
                        ['options' => ['black', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39414', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39414', 'print_template_id' => 59]]],
                        ['options' => ['black', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '46978', 'print_template_id' => 59], 'tee_launch' => ['sku' => '46978', 'print_template_id' => 59]]],
                        ['options' => ['white', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39514', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39514', 'print_template_id' => 59]]],
                        ['options' => ['white', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39515', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39515', 'print_template_id' => 59]]],
                        ['options' => ['white', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39516', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39516', 'print_template_id' => 59]]],
                        ['options' => ['white', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39517', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39517', 'print_template_id' => 59]]],
                        ['options' => ['white', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39518', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39518', 'print_template_id' => 59]]],
                        ['options' => ['white', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39519', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39519', 'print_template_id' => 59]]],
                        ['options' => ['white', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39520', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39520', 'print_template_id' => 59]]],
                        ['options' => ['white', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '46985', 'print_template_id' => 59], 'tee_launch' => ['sku' => '46985', 'print_template_id' => 59]]],
                        ['options' => ['heather_grey', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39429', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39429', 'print_template_id' => 59]]],
                        ['options' => ['heather_grey', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39430', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39430', 'print_template_id' => 59]]],
                        ['options' => ['heather_grey', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39431', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39431', 'print_template_id' => 59]]],
                        ['options' => ['heather_grey', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39432', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39432', 'print_template_id' => 59]]],
                        ['options' => ['heather_grey', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39433', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39433', 'print_template_id' => 59]]],
                        ['options' => ['heather_grey', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39434', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39434', 'print_template_id' => 59]]],
                        ['options' => ['heather_grey', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39435', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39435', 'print_template_id' => 59]]],
                        ['options' => ['heather_grey', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '46979', 'print_template_id' => 59], 'tee_launch' => ['sku' => '46979', 'print_template_id' => 59]]],
                        ['options' => ['midnight_navy', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39472', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39472', 'print_template_id' => 59]]],
                        ['options' => ['midnight_navy', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39473', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39473', 'print_template_id' => 59]]],
                        ['options' => ['midnight_navy', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39474', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39474', 'print_template_id' => 59]]],
                        ['options' => ['midnight_navy', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39475', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39475', 'print_template_id' => 59]]],
                        ['options' => ['midnight_navy', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39476', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39476', 'print_template_id' => 59]]],
                        ['options' => ['midnight_navy', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39477', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39477', 'print_template_id' => 59]]],
                        ['options' => ['midnight_navy', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39478', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39478', 'print_template_id' => 59]]],
                        ['options' => ['midnight_navy', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '46982', 'print_template_id' => 59], 'tee_launch' => ['sku' => '46982', 'print_template_id' => 59]]],
                        ['options' => ['military_green', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39451', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39451', 'print_template_id' => 59]]],
                        ['options' => ['military_green', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39452', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39452', 'print_template_id' => 59]]],
                        ['options' => ['military_green', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39453', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39453', 'print_template_id' => 59]]],
                        ['options' => ['military_green', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39454', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39454', 'print_template_id' => 59]]],
                        ['options' => ['military_green', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39455', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39455', 'print_template_id' => 59]]],
                        ['options' => ['military_green', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39456', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39456', 'print_template_id' => 59]]],
                        ['options' => ['military_green', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39457', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39457', 'print_template_id' => 59]]],
                        ['options' => ['cardinal', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39415', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39415', 'print_template_id' => 59]]],
                        ['options' => ['cardinal', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39416', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39416', 'print_template_id' => 59]]],
                        ['options' => ['cardinal', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39417', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39417', 'print_template_id' => 59]]],
                        ['options' => ['cardinal', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39418', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39418', 'print_template_id' => 59]]],
                        ['options' => ['cardinal', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39419', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39419', 'print_template_id' => 59]]],
                        ['options' => ['cardinal', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39420', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39420', 'print_template_id' => 59]]],
                        ['options' => ['cardinal', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39421', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39421', 'print_template_id' => 59]]],
                        ['options' => ['kelly_green', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39458', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39458', 'print_template_id' => 59]]],
                        ['options' => ['kelly_green', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39459', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39459', 'print_template_id' => 59]]],
                        ['options' => ['kelly_green', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39460', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39460', 'print_template_id' => 59]]],
                        ['options' => ['kelly_green', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39461', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39461', 'print_template_id' => 59]]],
                        ['options' => ['kelly_green', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39462', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39462', 'print_template_id' => 59]]],
                        ['options' => ['kelly_green', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39463', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39463', 'print_template_id' => 59]]],
                        ['options' => ['kelly_green', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39464', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39464', 'print_template_id' => 59]]],
                        ['options' => ['light_blue', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39422', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39422', 'print_template_id' => 59]]],
                        ['options' => ['light_blue', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39423', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39423', 'print_template_id' => 59]]],
                        ['options' => ['light_blue', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39424', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39424', 'print_template_id' => 59]]],
                        ['options' => ['light_blue', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39425', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39425', 'print_template_id' => 59]]],
                        ['options' => ['light_blue', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39426', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39426', 'print_template_id' => 59]]],
                        ['options' => ['light_blue', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39427', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39427', 'print_template_id' => 59]]],
                        ['options' => ['light_blue', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39428', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39428', 'print_template_id' => 59]]],
                        ['options' => ['red', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39486', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39486', 'print_template_id' => 59]]],
                        ['options' => ['red', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39487', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39487', 'print_template_id' => 59]]],
                        ['options' => ['red', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39488', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39488', 'print_template_id' => 59]]],
                        ['options' => ['red', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39489', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39489', 'print_template_id' => 59]]],
                        ['options' => ['red', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39490', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39490', 'print_template_id' => 59]]],
                        ['options' => ['red', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39491', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39491', 'print_template_id' => 59]]],
                        ['options' => ['red', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39492', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39492', 'print_template_id' => 59]]],
                        ['options' => ['red', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '46983', 'print_template_id' => 59], 'tee_launch' => ['sku' => '46983', 'print_template_id' => 59]]],
                        ['options' => ['royal', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39493', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39493', 'print_template_id' => 59]]],
                        ['options' => ['royal', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39494', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39494', 'print_template_id' => 59]]],
                        ['options' => ['royal', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39495', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39495', 'print_template_id' => 59]]],
                        ['options' => ['royal', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39496', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39496', 'print_template_id' => 59]]],
                        ['options' => ['royal', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39497', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39497', 'print_template_id' => 59]]],
                        ['options' => ['royal', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39498', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39498', 'print_template_id' => 59]]],
                        ['options' => ['royal', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39499', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39499', 'print_template_id' => 59]]],
                        ['options' => ['royal', '4xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '46984', 'print_template_id' => 59], 'tee_launch' => ['sku' => '46984', 'print_template_id' => 59]]],
                        ['options' => ['tahiti_blue', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39500', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39500', 'print_template_id' => 59]]],
                        ['options' => ['tahiti_blue', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39501', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39501', 'print_template_id' => 59]]],
                        ['options' => ['tahiti_blue', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39502', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39502', 'print_template_id' => 59]]],
                        ['options' => ['tahiti_blue', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39503', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39503', 'print_template_id' => 59]]],
                        ['options' => ['tahiti_blue', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39504', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39504', 'print_template_id' => 59]]],
                        ['options' => ['tahiti_blue', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39505', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39505', 'print_template_id' => 59]]],
                        ['options' => ['tahiti_blue', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39506', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39506', 'print_template_id' => 59]]],
                        ['options' => ['banana_cream', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '51860', 'print_template_id' => 59], 'tee_launch' => ['sku' => '51860', 'print_template_id' => 59]]],
                        ['options' => ['banana_cream', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '51861', 'print_template_id' => 59], 'tee_launch' => ['sku' => '51861', 'print_template_id' => 59]]],
                        ['options' => ['banana_cream', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '51862', 'print_template_id' => 59], 'tee_launch' => ['sku' => '51862', 'print_template_id' => 59]]],
                        ['options' => ['banana_cream', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '51863', 'print_template_id' => 59], 'tee_launch' => ['sku' => '51863', 'print_template_id' => 59]]],
                        ['options' => ['banana_cream', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '51864', 'print_template_id' => 59], 'tee_launch' => ['sku' => '51864', 'print_template_id' => 59]]],
                        ['options' => ['banana_cream', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '51865', 'print_template_id' => 59], 'tee_launch' => ['sku' => '51865', 'print_template_id' => 59]]],
                        ['options' => ['banana_cream', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '51866', 'print_template_id' => 59], 'tee_launch' => ['sku' => '51866', 'print_template_id' => 59]]],
                        ['options' => ['maroon', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '57292', 'print_template_id' => 59], 'tee_launch' => ['sku' => '57292', 'print_template_id' => 59]]],
                        ['options' => ['maroon', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '57293', 'print_template_id' => 59], 'tee_launch' => ['sku' => '57293', 'print_template_id' => 59]]],
                        ['options' => ['maroon', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '57294', 'print_template_id' => 59], 'tee_launch' => ['sku' => '57294', 'print_template_id' => 59]]],
                        ['options' => ['maroon', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '57295', 'print_template_id' => 59], 'tee_launch' => ['sku' => '57295', 'print_template_id' => 59]]],
                        ['options' => ['maroon', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '57296', 'print_template_id' => 59], 'tee_launch' => ['sku' => '57296', 'print_template_id' => 59]]],
                        ['options' => ['purple_rush', 'xs'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39479', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39479', 'print_template_id' => 59]]],
                        ['options' => ['purple_rush', 's'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39480', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39480', 'print_template_id' => 59]]],
                        ['options' => ['purple_rush', 'm'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39481', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39481', 'print_template_id' => 59]]],
                        ['options' => ['purple_rush', 'l'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39482', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39482', 'print_template_id' => 59]]],
                        ['options' => ['purple_rush', 'xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39483', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39483', 'print_template_id' => 59]]],
                        ['options' => ['purple_rush', '2xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39484', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39484', 'print_template_id' => 59]]],
                        ['options' => ['purple_rush', '3xl'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '39485', 'print_template_id' => 59], 'tee_launch' => ['sku' => '39485', 'print_template_id' => 59]]],
                    ]
                ],
                [
                    'tab' => 'Drinkware',
                    'group' => 'Mug',
                    'key' => 'ceramic_mug',
                    'title' => 'Ceramic Mug',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Mug',
                        'photo' => 'Personalized Photo Mug',
                        'image' => 'Mug'
                    ],
                    'image' => 'catalog/campaign/type/icon/mug/11oz.jpg',
                    'options' => [
                        [
                            'key' => 'mug_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']],
                                ['key' => 'black', 'title' => 'Black', 'meta_data' => ['hex' => '#000']],
                                ['key' => 'blue', 'title' => 'Blue', 'meta_data' => ['hex' => '#4198CD']],
                                ['key' => 'red', 'title' => 'Red', 'meta_data' => ['hex' => '#D30000']],
                                ['key' => 'navy', 'title' => 'Navy', 'meta_data' => ['hex' => '#000080']],
                                ['key' => 'pink', 'title' => 'Pink', 'meta_data' => ['hex' => '#E2AAAD']],
                            ]
                        ],
                        [
                            'key' => 'mug_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '10oz', 'title' => '10 oz'],
                                ['key' => '11oz', 'title' => '11 oz'],
                                ['key' => '12oz', 'title' => '12 oz'],
                                ['key' => '15oz', 'title' => '15 oz']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '11oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1122', 'print_template_id' => 1], 'harrier' => ['sku' => '11OZWHITE', 'print_template_id' => 1], 'prima' => ['sku' => 'SF20704', 'print_template_id' => 55, 'meta_data' => ['format' => 'pdf']], 'cw' => ['sku' => 'N0601015', 'print_template_id' => 1], 'print_geek' => ['sku' => 'mug', 'print_template_id' => 1]]],
                        ['options' => ['white', '15oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '5050', 'print_template_id' => 2], 'harrier' => ['sku' => '15OZMUG', 'print_template_id' => 57], 'prima' => ['sku' => 'PR000001', 'print_template_id' => 2], 'cw' => ['sku' => 'N0601017', 'print_template_id' => 2]]],
                    ]
                ],
                [
                    'tab' => 'Drinkware',
                    'group' => 'Mug',
                    'key' => 'two_tone_mug',
                    'title' => 'Two Tone Mug',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Mug',
                        'photo' => 'Photo Mug',
                        'image' => 'Mug'
                    ],
                    'image' => 'catalog/campaign/type/icon/mug/twoTone.jpg',
                    'options' => [
                        [
                            'key' => 'mug_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']],
                                ['key' => 'black', 'title' => 'Black', 'meta_data' => ['hex' => '#000']],
                                ['key' => 'blue', 'title' => 'Blue', 'meta_data' => ['hex' => '#4198CD']],
                                ['key' => 'red', 'title' => 'Red', 'meta_data' => ['hex' => '#D30000']],
                                ['key' => 'navy', 'title' => 'Navy', 'meta_data' => ['hex' => '#000080']],
                                ['key' => 'pink', 'title' => 'Pink', 'meta_data' => ['hex' => '#E2AAAD']],
                            ]
                        ],
                        [
                            'key' => 'mug_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '10oz', 'title' => '10 oz'],
                                ['key' => '11oz', 'title' => '11 oz'],
                                ['key' => '12oz', 'title' => '12 oz'],
                                ['key' => '15oz', 'title' => '15 oz']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['black', '11oz'], 'suppliers' =>
                            [
                                'dpi' => ['sku' => '5055', 'print_template_id' => 3],
                                'harrier' => ['sku' => '11OZBLACK', 'print_template_id' => 3],
                                'prima' => ['sku' => 'CommerceProduct_96206', 'print_template_id' => 56]
                            ]
                        ],
                        ['options' => ['blue', '11oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '5085', 'print_template_id' => 3], 'harrier' => ['sku' => '11OZBLUE', 'print_template_id' => 3], 'prima' => ['sku' => 'CommerceProduct_96206', 'print_template_id' => 56]]],
                        ['options' => ['red', '11oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '5086', 'print_template_id' => 3], 'harrier' => ['sku' => '11OZRED', 'print_template_id' => 3], 'prima' => ['sku' => 'CommerceProduct_96206', 'print_template_id' => 56]]],
                        ['options' => ['navy', '11oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '5052', 'print_template_id' => 3]]],
                        ['options' => ['pink', '11oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '5080', 'print_template_id' => 3], 'harrier' => ['sku' => '11OZPINK', 'print_template_id' => 3], 'prima' => ['sku' => 'CommerceProduct_96206', 'print_template_id' => 56]]],
                    ]
                ],
                [
                    'tab' => 'Drinkware',
                    'group' => 'Mug',
                    'key' => 'enamel_campfire_mug',
                    'title' => 'Enamel Campfire Mug',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Mug',
                        'photo' => 'Photo Mug',
                        'image' => 'Mug'
                    ],
                    'image' => 'catalog/campaign/type/icon/mug/enamelCampfire.jpg',
                    'options' => [
                        [
                            'key' => 'mug_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']],
                                ['key' => 'black', 'title' => 'Black', 'meta_data' => ['hex' => '#000']],
                                ['key' => 'blue', 'title' => 'Blue', 'meta_data' => ['hex' => '#4198CD']],
                                ['key' => 'red', 'title' => 'Red', 'meta_data' => ['hex' => '#D30000']],
                                ['key' => 'navy', 'title' => 'Navy', 'meta_data' => ['hex' => '#000080']],
                                ['key' => 'pink', 'title' => 'Pink', 'meta_data' => ['hex' => '#E2AAAD']],
                            ]
                        ],
                        [
                            'key' => 'mug_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '10oz', 'title' => '10 oz'],
                                ['key' => '11oz', 'title' => '11 oz'],
                                ['key' => '12oz', 'title' => '12 oz'],
                                ['key' => '15oz', 'title' => '15 oz']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '10oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1161', 'print_template_id' => 5]]],
                    ]
                ],
                [
                    'tab' => 'Drinkware',
                    'group' => 'Mug',
                    'key' => 'insulated_coffee_mug',
                    'title' => 'Insulated Coffee Mug',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Mug',
                        'photo' => 'Photo Mug',
                        'image' => 'Mug'
                    ],
                    'image' => 'catalog/campaign/type/icon/mug/insulatedCoffee.jpg',
                    'options' => [
                        [
                            'key' => 'mug_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']],
                                ['key' => 'black', 'title' => 'Black', 'meta_data' => ['hex' => '#000']],
                                ['key' => 'blue', 'title' => 'Blue', 'meta_data' => ['hex' => '#4198CD']],
                                ['key' => 'red', 'title' => 'Red', 'meta_data' => ['hex' => '#D30000']],
                                ['key' => 'navy', 'title' => 'Navy', 'meta_data' => ['hex' => '#000080']],
                                ['key' => 'pink', 'title' => 'Pink', 'meta_data' => ['hex' => '#E2AAAD']],
                            ]
                        ],
                        [
                            'key' => 'mug_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '10oz', 'title' => '10 oz'],
                                ['key' => '11oz', 'title' => '11 oz'],
                                ['key' => '12oz', 'title' => '12 oz'],
                                ['key' => '15oz', 'title' => '15 oz']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '12oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1160', 'print_template_id' => 4]]]
                    ]
                ],
                [
                    'tab' => 'Bedroom',
                    'group' => 'Blanket',
                    'key' => 'fleece_blanket',
                    'title' => 'Fleece Blanket',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Blanket',
                        'photo' => 'Photo Blanket',
                        'image' => 'Fleece Blanket'
                    ],
                    'image' => 'catalog/campaign/type/icon/fleeceBlanket/30x40.png',
                    'options' => [
                        [
                            'key' => 'fleece_blanket_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
                        [
                            'key' => 'blanket_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '30x40', 'title' => '30x40'],
                                ['key' => '50x60', 'title' => '50x60'],
                                ['key' => '60x80', 'title' => '60x80']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '30x40'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1511', 'print_template_id' => 32, 'meta_data' => ['format' => 'jpg']], 'prima' => ['sku' => 'CommerceProduct_265394', 'print_template_id' => 32], 'cw' => ['sku' => 'N0601004-S', 'print_template_id' => 32]]],
                        ['options' => ['white', '50x60'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1332', 'print_template_id' => 33, 'meta_data' => ['format' => 'jpg']], 'harrier' => ['sku' => 'MINKBLNKT', 'print_template_id' => 33], 'cw' => ['sku' => 'N0601004-M', 'print_template_id' => 33]]],
                        ['options' => ['white', '60x80'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1333', 'print_template_id' => 34, 'meta_data' => ['format' => 'jpg']], 'cw' => ['sku' => 'N0601004-L', 'print_template_id' => 34]]],
                    ]
                ],
                [
                    'tab' => 'Bedroom',
                    'group' => 'Blanket',
                    'key' => 'sherpa_flannel_blanket',
                    'title' => 'Sherpa Flannel Blanket',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Blanket',
                        'photo' => 'Photo Blanket',
                        'image' => 'Blanket'
                    ],
                    'image' => 'catalog/campaign/type/icon/fleeceBlanket/50x60.png',
                    'options' => [
                        [
                            'key' => 'sherpa_flannel_blanket_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
                        [
                            'key' => 'blanket_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '50x60', 'title' => '50x60']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '50x60'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['harrier' => ['sku' => 'SHERPABLNKT', 'print_template_id' => 33]]],
                    ]
                ],
                [
                    'tab' => 'Bedroom',
                    'group' => 'Pillow',
                    'key' => 'pillow',
                    'title' => 'Pillow',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Pillow',
                        'photo' => 'Photo Pillow',
                        'image' => 'Pillow'
                    ],
                    'image' => 'catalog/campaign/type/icon/pillow/16x16.jpg',
                    'options' => [
                        [
                            'key' => 'pillow_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
                        [
                            'key' => 'pillow_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '16x16', 'title' => '16x16'],
                                ['key' => '18x18', 'title' => '18x18']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '16x16'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1594', 'print_template_id' => 38], 'cw' => ['sku' => 'C0601006-S-F', 'print_template_id' => 54]]],
                        ['options' => ['white', '18x18'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1587', 'print_template_id' => 37], 'harrier' => ['sku' => '1818CUSH', 'print_template_id' => 0], 'cw' => ['sku' => 'C0601006-M-F', 'print_template_id' => 37]]],
                    ]
                ],
                [
                    'tab' => 'Bathroom',
                    'group' => 'Towel',
                    'key' => 'tea_towel',
                    'title' => 'Tea Towel',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Towel',
                        'photo' => 'Photo Towel',
                        'image' => 'Towel'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'tea_towel_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
                        [
                            'key' => 'towel_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '16x25', 'title' => '16x25']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '16x25'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => '1441', 'prima' => 'CommerceProduct_265395', 'harrier' => ['sku' => '1625TEATOWEL', 'print_template_id' => 0]]]
                    ]
                ],
                [
                    'tab' => 'Bathroom',
                    'group' => 'Towel',
                    'key' => 'beach_towel',
                    'title' => 'Beach Towel',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Towel',
                        'photo' => 'Photo Towel',
                        'image' => 'Towel'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'beach_towel_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
                        [
                            'key' => 'towel_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '35x60', 'title' => '35x60']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '35x60'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1416', 'print_template_id' => 0], 'harrier' => ['sku' => '916BEACHTOWEL', 'print_template_id' => 0]]]
                    ]
                ],
                [
                    'tab' => 'Bathroom',
                    'group' => 'Towel',
                    'key' => 'kid_towel',
                    'title' => 'Kid Towel',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Towel',
                        'photo' => 'Photo Towel',
                        'image' => 'Towel'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'kid_towel_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
                        [
                            'key' => 'towel_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '22x42', 'title' => '22x42']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '22x42'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'CommerceProduct_265396', 'print_template_id' => 0]]]
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Ornament',
                    'key' => 'ornament_square',
                    'title' => 'Ornament Square',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Ornament',
                        'photo' => 'Photo Ornament',
                        'image' => 'Ornament'
                    ],
                    'image' => 'catalog/campaign/type/icon/ornament/aluminiumSquare/background.png',
                    'options' => [
                        [
                            'key' => 'ornament_material',
                            'type' => 'button',
                            'title' => 'Material',
                            'values' => [
                                ['key' => 'aluminium', 'title' => 'Aluminium'],
                                ['key' => 'mdf_plastic', 'title' => 'MDF/Plastic'],
                                ['key' => 'ceramic', 'title' => 'Ceramic']
                            ]
                        ],
                        [
                            'key' => 'ornament_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '3.2x3.2', 'title' => '3.2x3.2'],
                                ['key' => '2.75x4', 'title' => '2.75x4'],
                                ['key' => '4x2.75', 'title' => '4x2.75'],
                                ['key' => '3_inches_tall', 'title' => '3 inches tall'],
                                ['key' => '3.25_inches_tall', 'title' => '3.25 inches tall']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['aluminium', '3.2x3.2'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'CommerceProduct_152717', 'print_template_id' => 62], 'harrier' => ['sku' => 'ALUMINIUM SQUARE ORNAMENT', 'print_template_id' => 62], 'dpi' => ['sku' => '1164', 'print_template_id' => 62]]],
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Ornament',
                    'key' => 'ornament_medallion',
                    'title' => 'Ornament Medallion',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Ornament',
                        'photo' => 'Photo Ornament',
                        'image' => 'Ornament'
                    ],
                    'image' => 'catalog/campaign/type/icon/ornament/aluminiumMedallion/background.png',
                    'options' => [
                        [
                            'key' => 'ornament_material',
                            'type' => 'button',
                            'title' => 'Material',
                            'values' => [
                                ['key' => 'aluminium', 'title' => 'Aluminium'],
                                ['key' => 'mdf_plastic', 'title' => 'MDF/Plastic'],
                                ['key' => 'ceramic', 'title' => 'Ceramic']
                            ]
                        ],
                        [
                            'key' => 'ornament_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '3.2x3.2', 'title' => '3.2x3.2'],
                                ['key' => '2.75x4', 'title' => '2.75x4'],
                                ['key' => '4x2.75', 'title' => '4x2.75'],
                                ['key' => '3_inches_tall', 'title' => '3 inches tall'],
                                ['key' => '3.25_inches_tall', 'title' => '3.25 inches tall']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['aluminium', '2.75x4'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'CommerceProduct_152719', 'print_template_id' => 60], 'harrier' => ['sku' => 'ALUMINIUM MEDALLION ORNAMENT', 'print_template_id' => 60], 'dpi' => ['sku' => '1166', 'print_template_id' => 60]]],
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Ornament',
                    'key' => 'ornament_scalloped',
                    'title' => 'Ornament Scalloped',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Ornament',
                        'photo' => 'Photo Ornament',
                        'image' => 'Ornament'
                    ],
                    'image' => 'catalog/campaign/type/icon/ornament/aluminiumScalloped/background.png',
                    'options' => [
                        [
                            'key' => 'ornament_material',
                            'type' => 'button',
                            'title' => 'Material',
                            'values' => [
                                ['key' => 'aluminium', 'title' => 'Aluminium'],
                                ['key' => 'mdf_plastic', 'title' => 'MDF/Plastic'],
                                ['key' => 'ceramic', 'title' => 'Ceramic']
                            ]
                        ],
                        [
                            'key' => 'ornament_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '3.2x3.2', 'title' => '3.2x3.2'],
                                ['key' => '2.75x4', 'title' => '2.75x4'],
                                ['key' => '4x2.75', 'title' => '4x2.75'],
                                ['key' => '3_inches_tall', 'title' => '3 inches tall'],
                                ['key' => '3.25_inches_tall', 'title' => '3.25 inches tall']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['aluminium', '4x2.75'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['prima' => ['sku' => 'CommerceProduct_152718', 'print_template_id' => 61], 'dpi' => ['sku' => '1165', 'print_template_id' => 61]]],
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Ornament',
                    'key' => 'ornament_circle',
                    'title' => 'Ornament Circle',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Ornament',
                        'photo' => 'Photo Ornament',
                        'image' => 'Ornament'
                    ],
                    'image' => 'catalog/campaign/type/icon/ornament/circle/background.png',
                    'options' => [
                        [
                            'key' => 'ornament_material',
                            'type' => 'button',
                            'title' => 'Material',
                            'values' => [
                                ['key' => 'aluminium', 'title' => 'Aluminium'],
                                ['key' => 'mdf_plastic', 'title' => 'MDF/Plastic'],
                                ['key' => 'ceramic', 'title' => 'Ceramic']
                            ]
                        ],
                        [
                            'key' => 'ornament_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '3.2x3.2', 'title' => '3.2x3.2'],
                                ['key' => '2.75x4', 'title' => '2.75x4'],
                                ['key' => '4x2.75', 'title' => '4x2.75'],
                                ['key' => '3_inches_tall', 'title' => '3 inches tall'],
                                ['key' => '3.25_inches_tall', 'title' => '3.25 inches tall']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['mdf_plastic', '3_inches_tall'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54723', 'print_template_id' => 67]]],
                        ['options' => ['ceramic', '3_inches_tall'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['tee_launch' => ['sku' => 'RORN', 'print_template_id' => 67]]],
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Ornament',
                    'key' => 'ornament_heart',
                    'title' => 'Ornament Heart',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Ornament',
                        'photo' => 'Photo Ornament',
                        'image' => 'Ornament'
                    ],
                    'image' => 'catalog/campaign/type/icon/ornament/heart/background.png',
                    'options' => [
                        [
                            'key' => 'ornament_material',
                            'type' => 'button',
                            'title' => 'Material',
                            'values' => [
                                ['key' => 'aluminium', 'title' => 'Aluminium'],
                                ['key' => 'mdf_plastic', 'title' => 'MDF/Plastic'],
                                ['key' => 'ceramic', 'title' => 'Ceramic']
                            ]
                        ],
                        [
                            'key' => 'ornament_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '3.2x3.2', 'title' => '3.2x3.2'],
                                ['key' => '2.75x4', 'title' => '2.75x4'],
                                ['key' => '4x2.75', 'title' => '4x2.75'],
                                ['key' => '3_inches_tall', 'title' => '3 inches tall'],
                                ['key' => '3.25_inches_tall', 'title' => '3.25 inches tall']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['mdf_plastic', '3_inches_tall'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54726', 'print_template_id' => 68]]],
                        ['options' => ['ceramic', '3_inches_tall'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['tee_launch' => ['sku' => 'HRTORN', 'print_template_id' => 68]]]
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Ornament',
                    'key' => 'ornament_oval',
                    'title' => 'Ornament Oval',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Ornament',
                        'photo' => 'Photo Ornament',
                        'image' => 'Ornament'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'ornament_material',
                            'type' => 'button',
                            'title' => 'Material',
                            'values' => [
                                ['key' => 'aluminium', 'title' => 'Aluminium'],
                                ['key' => 'mdf_plastic', 'title' => 'MDF/Plastic'],
                                ['key' => 'ceramic', 'title' => 'Ceramic']
                            ]
                        ],
                        [
                            'key' => 'ornament_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '3.2x3.2', 'title' => '3.2x3.2'],
                                ['key' => '2.75x4', 'title' => '2.75x4'],
                                ['key' => '4x2.75', 'title' => '4x2.75'],
                                ['key' => '3_inches_tall', 'title' => '3 inches tall'],
                                ['key' => '3.25_inches_tall', 'title' => '3.25 inches tall']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['mdf_plastic', '3.25_inches_tall'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54724', 'print_template_id' => 0]]],
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Ornament',
                    'key' => 'ornament_star',
                    'title' => 'Ornament Star',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Ornament',
                        'photo' => 'Photo Ornament',
                        'image' => 'Ornament'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'ornament_material',
                            'type' => 'button',
                            'title' => 'Material',
                            'values' => [
                                ['key' => 'aluminium', 'title' => 'Aluminium'],
                                ['key' => 'mdf_plastic', 'title' => 'MDF/Plastic'],
                                ['key' => 'ceramic', 'title' => 'Ceramic']
                            ]
                        ],
                        [
                            'key' => 'ornament_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '3.2x3.2', 'title' => '3.2x3.2'],
                                ['key' => '2.75x4', 'title' => '2.75x4'],
                                ['key' => '4x2.75', 'title' => '4x2.75'],
                                ['key' => '3_inches_tall', 'title' => '3 inches tall'],
                                ['key' => '3.25_inches_tall', 'title' => '3.25 inches tall']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['mdf_plastic', '3.25_inches_tall'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['custom_cat' => ['sku' => '54725', 'print_template_id' => 0]]],
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Mouse Pad',
                    'key' => 'mouse_pad',
                    'title' => 'Mouse Pad',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Mouse Pad',
                        'photo' => 'Photo Mouse Pad',
                        'image' => 'Mouse Pad'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'mouse_pad_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '8x9', 'title' => '8x9']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['8x9'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '5069', 'print_template_id' => 0], 'harrier' => ['sku' => 'DS MOUSEMAT', 'print_template_id' => 0], 'prima' => ['sku' => 'SF12200', 'print_template_id' => 0]]]
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Coaster',
                    'key' => 'coaster_set_4',
                    'title' => 'Coaster set 4',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Coaster',
                        'photo' => 'Photo Coaster',
                        'image' => 'Coaster'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'coaster_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '4x4', 'title' => '4x4']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['4x4'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1396', 'print_template_id' => 0], 'harrier' => ['sku' => 'DSCOASTER4MULTI', 'print_template_id' => 0], 'prima' => ['sku' => 'CommerceProduct_87608', 'print_template_id' => 0]]]
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Place Mat',
                    'key' => 'place_mat',
                    'title' => 'Place Mat',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Place Mat',
                        'photo' => 'Photo Place Mat',
                        'image' => 'Place Mat'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'place_mat_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '8x9.5', 'title' => '8x9.5']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['8x9.5'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['harrier' => ['sku' => 'DS PLACEMAT', 'print_template_id' => 0], 'prima' => ['sku' => 'CommerceProduct_130274', 'print_template_id' => 0]]]
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Puzzle',
                    'key' => 'puzzle',
                    'title' => 'Puzzle',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Puzzle',
                        'photo' => 'Photo Puzzle',
                        'image' => 'Puzzle'
                    ],
                    'image' => 'catalog/campaign/type/icon/puzzles/10x14.png',
                    'options' => [
                        [
                            'key' => 'puzzle_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
                        [
                            'key' => 'puzzle_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '10x14', 'title' => '10x14'],
                                ['key' => '14x10', 'title' => '14x10'],
                                ['key' => '16x20', 'title' => '16x20'],
                                ['key' => '20x16', 'title' => '20x16']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '10x14'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '12370', 'print_template_id' => 35]]],
                        ['options' => ['white', '14x10'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '12370', 'print_template_id' => 36, 'meta_data' => ['rotate' => 90]]]],
                        ['options' => ['white', '16x20'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '12331', 'print_template_id' => 0]]],
                        ['options' => ['white', '20x16'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '12331', 'print_template_id' => 0]]]
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Desktop Plaque',
                    'key' => 'desktop_plaque',
                    'title' => 'Desktop Plaque',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Desktop',
                        'photo' => 'Photo Desktop',
                        'image' => 'Desktop'
                    ],
                    'image' => 'catalog/campaign/type/icon/desktopPlaque/7x5.png',
                    'options' => [
                        [
                            'key' => 'desktop_plaque_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '5x7', 'title' => '5x7'],
                                ['key' => '8x10', 'title' => '8x10'],
                                ['key' => '7x5', 'title' => '7x5'],
                                ['key' => '10x8', 'title' => '10x8']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['5x7'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1690', 'print_template_id' => 0], 'harrier' => ['sku' => '57DESKPAN', 'print_template_id' => 0], 'prima' => ['sku' => 'CommerceProduct_121683', 'print_template_id' => 0]]],
                        ['options' => ['8x10'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1691', 'print_template_id' => 0], 'harrier' => ['sku' => '810DESKPAN', 'print_template_id' => 0], 'prima' => ['sku' => 'CommerceProduct_121684', 'print_template_id' => 0]]],
                        ['options' => ['7x5'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1690', 'print_template_id' => 30], 'harrier' => ['sku' => '57DESKPAN', 'print_template_id' => 30], 'prima' => ['sku' => 'CommerceProduct_121683', 'print_template_id' => 30]]],
                        ['options' => ['10x8'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1691', 'print_template_id' => 31], 'harrier' => ['sku' => '810DESKPAN', 'print_template_id' => 31], 'prima' => ['sku' => 'CommerceProduct_121684', 'print_template_id' => 31]]],
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Notebook',
                    'key' => 'wiro_notebook',
                    'title' => 'Wiro Notebook',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Notebook',
                        'photo' => 'Photo Notebook',
                        'image' => 'Notebook'
                    ],
                    'image' => 'catalog/campaign/type/icon/notebook/5x7.png',
                    'options' => [
                        [
                            'key' => 'wiro_notebook_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
                        [
                            'key' => 'wiro_notebook_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '5x7', 'title' => '5x7'],
                                ['key' => '5.8x8.27', 'title' => '5.8x8.27'],
                                ['key' => '8.27x11.69', 'title' => '8.27x11.69']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '5x7'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '11500', 'print_template_id' => 39]]],
                        ['options' => ['white', '5.8x8.27'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['harrier' => ['sku' => '148210WIL', 'print_template_id' => 0]]],
                        ['options' => ['white', '8.27x11.69'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['harrier' => ['sku' => '21297WIL', 'print_template_id' => 0]]]
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Facemask',
                    'key' => 'facemask_with_filter',
                    'title' => 'Facemask with filter',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Facemask',
                        'photo' => 'Photo Facemask',
                        'image' => 'Facemask'
                    ],
                    'image' => 'catalog/campaign/type/icon/facemask.png',
                    'options' => [
                        [
                            'key' => 'facemask_with_filter_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
                        [
                            'key' => 'facemask_with_filter_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '7.25x5.1', 'title' => '7.25x5.1'],
                                ['key' => '6.39x3.81', 'title' => '6.39x3.81']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '7.25x5.1'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1598', 'print_template_id' => 41, 'meta_data' => ['format' => 'jpg']], 'harrier' => ['sku' => 'facemask', 'print_template_id' => 41], 'prima' => ['sku' => 'CommerceProduct_240119', 'print_template_id' => 41]]],
                        ['options' => ['white', '6.39x3.81'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1599', 'print_template_id' => 63, 'meta_data' => ['format' => 'jpg']]]]
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Facemask',
                    'key' => 'facemask_without_filter',
                    'title' => 'Facemask without filter',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Facemask',
                        'photo' => 'Photo Facemask',
                        'image' => 'Facemask'
                    ],
                    'image' => 'catalog/campaign/type/icon/facemask-white.png',
                    'options' => [
                        [
                            'key' => 'facemask_without_filter_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
                        [
                            'key' => 'facemask_without_filter_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '7.28x4.53', 'title' => '7.28x4.53']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '7.28x4.53'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['cw' => ['sku' => 'N0701008', 'print_template_id' => 40]]]
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Phonecase',
                    'key' => 'phonecase',
                    'title' => 'Phonecase',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Phone Case',
                        'photo' => 'Photo Phone Case',
                        'image' => 'Phone Case'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'phonecase_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
                        [
                            'key' => 'phonecase_shape',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => 'iphone_11', 'title' => 'iPhone 11'],
                                ['key' => 'iphone_11pro', 'title' => 'iPhone 11 Pro']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', 'iphone_11'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['cw' => ['sku' => 'N0801002-11-WH', 'print_template_id' => 0]]],
                        ['options' => ['white', 'iphone_11pro'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['cw' => ['sku' => 'N0801002-11P-WH', 'print_template_id' => 0]]]
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Stock',
                    'key' => 'stock',
                    'title' => 'Stock',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Stock',
                        'photo' => 'Photo Stock',
                        'image' => 'Stock'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'stock_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
//                            [
//                                'key' => 'phonecase_shape',
//                                'type' => 'button',
//                                'title' => 'Size',
//                                'values' => [
//                                    ['key' => 'iphone_11', 'title' => 'iPhone 11'],
//                                    ['key' => 'iphone_11pro', 'title' => 'iPhone 11 Pro']
//                                ]
//                            ]
                    ],
                    'variants' => [
                        ['options' => ['white'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['tee_launch' => ['sku' => 'STKING', 'print_template_id' => 0]]],
                    ]
                ],
                [
                    'tab' => 'Drinkware',
                    'group' => 'Mug',
                    'key' => 'fullPrints_ceramic_mug',
                    'title' => 'FullPrints Ceramic Mug',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Mug',
                        'photo' => 'Photo Mug',
                        'image' => 'Phone Mug'
                    ],
                    'image' => 'catalog/campaign/type/icon/mug/11oz.jpg',
                    'options' => [
                        [
                            'key' => 'mug_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']],
                                ['key' => 'black', 'title' => 'Black', 'meta_data' => ['hex' => '#000']],
                                ['key' => 'blue', 'title' => 'Blue', 'meta_data' => ['hex' => '#4198CD']],
                                ['key' => 'red', 'title' => 'Red', 'meta_data' => ['hex' => '#D30000']],
                                ['key' => 'navy', 'title' => 'Navy', 'meta_data' => ['hex' => '#000080']],
                                ['key' => 'pink', 'title' => 'Pink', 'meta_data' => ['hex' => '#E2AAAD']],
                            ]
                        ],
                        [
                            'key' => 'mug_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '10oz', 'title' => '10 oz'],
                                ['key' => '11oz', 'title' => '11 oz'],
                                ['key' => '12oz', 'title' => '12 oz'],
                                ['key' => '15oz', 'title' => '15 oz']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '11oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1122', 'print_template_id' => 6], 'harrier' => ['sku' => '11OZWHITE', 'print_template_id' => 6], 'prima' => ['sku' => 'SF20704', 'print_template_id' => 64, 'meta_data' => ['format' => 'pdf']], 'cw' => ['sku' => 'N0601015', 'print_template_id' => 6], 'print_geek' => ['sku' => 'mug', 'print_template_id' => 6]]],
                        ['options' => ['white', '15oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '5050', 'print_template_id' => 7], 'harrier' => ['sku' => '15OZMUG', 'print_template_id' => 66], 'prima' => ['sku' => 'PR000001', 'print_template_id' => 7], 'cw' => ['sku' => 'N0601017', 'print_template_id' => 7]]],
                    ]
                ],
                [
                    'tab' => 'Drinkware',
                    'group' => 'Mug',
                    'key' => 'fullPrints_two_tone_mug',
                    'title' => 'FullPrints Two Tone Mug',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Mug',
                        'photo' => 'Photo Mug',
                        'image' => 'Phone Mug'
                    ],
                    'image' => 'catalog/campaign/type/icon/mug/twoTone.jpg',
                    'options' => [
                        [
                            'key' => 'mug_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']],
                                ['key' => 'black', 'title' => 'Black', 'meta_data' => ['hex' => '#000']],
                                ['key' => 'blue', 'title' => 'Blue', 'meta_data' => ['hex' => '#4198CD']],
                                ['key' => 'red', 'title' => 'Red', 'meta_data' => ['hex' => '#D30000']],
                                ['key' => 'navy', 'title' => 'Navy', 'meta_data' => ['hex' => '#000080']],
                                ['key' => 'pink', 'title' => 'Pink', 'meta_data' => ['hex' => '#E2AAAD']],
                            ]
                        ],
                        [
                            'key' => 'mug_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '10oz', 'title' => '10 oz'],
                                ['key' => '11oz', 'title' => '11 oz'],
                                ['key' => '12oz', 'title' => '12 oz'],
                                ['key' => '15oz', 'title' => '15 oz']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['black', '11oz'], 'suppliers' =>
                            [
                                'dpi' => ['sku' => '5055', 'print_template_id' => 8],
                                'harrier' => ['sku' => '11OZBLACK', 'print_template_id' => 8],
                                'prima' => ['sku' => 'CommerceProduct_96206', 'print_template_id' => 65]
                            ]
                        ],
                        ['options' => ['blue', '11oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '5085', 'print_template_id' => 8], 'harrier' => ['sku' => '11OZBLUE', 'print_template_id' => 8], 'prima' => ['sku' => 'CommerceProduct_96206', 'print_template_id' => 65]]],
                        ['options' => ['red', '11oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '5086', 'print_template_id' => 8], 'harrier' => ['sku' => '11OZRED', 'print_template_id' => 8], 'prima' => ['sku' => 'CommerceProduct_96206', 'print_template_id' => 65]]],
                        ['options' => ['navy', '11oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '5052', 'print_template_id' => 8]]],
                        ['options' => ['pink', '11oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '5080', 'print_template_id' => 8], 'harrier' => ['sku' => '11OZPINK', 'print_template_id' => 8], 'prima' => ['sku' => 'CommerceProduct_96206', 'print_template_id' => 65]]],
                    ]
                ],
                [
                    'tab' => 'Drinkware',
                    'group' => 'Mug',
                    'key' => 'fullPrints_enamel_campfire_mug',
                    'title' => 'FullPrints Enamel Campfire Mug',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Mug',
                        'photo' => 'Photo Mug',
                        'image' => 'Phone Mug'
                    ],
                    'image' => 'catalog/campaign/type/icon/mug/enamelCampfire.jpg',
                    'options' => [
                        [
                            'key' => 'mug_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']],
                                ['key' => 'black', 'title' => 'Black', 'meta_data' => ['hex' => '#000']],
                                ['key' => 'blue', 'title' => 'Blue', 'meta_data' => ['hex' => '#4198CD']],
                                ['key' => 'red', 'title' => 'Red', 'meta_data' => ['hex' => '#D30000']],
                                ['key' => 'navy', 'title' => 'Navy', 'meta_data' => ['hex' => '#000080']],
                                ['key' => 'pink', 'title' => 'Pink', 'meta_data' => ['hex' => '#E2AAAD']],
                            ]
                        ],
                        [
                            'key' => 'mug_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '10oz', 'title' => '10 oz'],
                                ['key' => '11oz', 'title' => '11 oz'],
                                ['key' => '12oz', 'title' => '12 oz'],
                                ['key' => '15oz', 'title' => '15 oz']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '10oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1161', 'print_template_id' => 10]]],
                    ]
                ],
                [
                    'tab' => 'Drinkware',
                    'group' => 'Mug',
                    'key' => 'fullPrints_insulated_coffee_mug',
                    'title' => 'FullPrints Insulated Coffee Mug',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Mug',
                        'photo' => 'Photo Mug',
                        'image' => 'Phone Mug'
                    ],
                    'image' => 'catalog/campaign/type/icon/mug/insulatedCoffee.jpg',
                    'options' => [
                        [
                            'key' => 'mug_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']],
                                ['key' => 'black', 'title' => 'Black', 'meta_data' => ['hex' => '#000']],
                                ['key' => 'blue', 'title' => 'Blue', 'meta_data' => ['hex' => '#4198CD']],
                                ['key' => 'red', 'title' => 'Red', 'meta_data' => ['hex' => '#D30000']],
                                ['key' => 'navy', 'title' => 'Navy', 'meta_data' => ['hex' => '#000080']],
                                ['key' => 'pink', 'title' => 'Pink', 'meta_data' => ['hex' => '#E2AAAD']],
                            ]
                        ],
                        [
                            'key' => 'mug_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '10oz', 'title' => '10 oz'],
                                ['key' => '11oz', 'title' => '11 oz'],
                                ['key' => '12oz', 'title' => '12 oz'],
                                ['key' => '15oz', 'title' => '15 oz']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '12oz'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '1160', 'print_template_id' => 9]]]
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Yard Sign',
                    'key' => 'yard_sign',
                    'title' => 'Yard Sign',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Yard Sign',
                        'photo' => 'Photo Yard Sign',
                        'image' => 'Yard Sign'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'yard_sign_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
                        [
                            'key' => 'yard_sign_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '22x15', 'title' => '22x15'],
                                ['key' => '24x18', 'title' => '24x18'],
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '22x15'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['dpi' => ['sku' => '7083', 'print_template_id' => 0]]],
                        ['options' => ['white', '24x18'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['tee_launch' => ['sku' => 'SIGN1824', 'print_template_id' => 0]]]
                    ]
                ],
                [
                    'tab' => 'Accessories',
                    'group' => 'Garden Flag',
                    'key' => 'garden_flag',
                    'title' => 'Garden Flag',
                    'short_title' => '',
                    'identifier' => [
                        'personalized' => 'Personalized Garden Flag',
                        'photo' => 'Photo Garden Flag',
                        'image' => 'Garden Flag'
                    ],
                    'image' => '',
                    'options' => [
                        [
                            'key' => 'garden_flag_color',
                            'type' => 'color',
                            'title' => 'Color',
                            'values' => [
                                ['key' => 'white', 'title' => 'White', 'meta_data' => ['hex' => '#FFF']]
                            ]
                        ],
                        [
                            'key' => 'garden_flag_size',
                            'type' => 'button',
                            'title' => 'Size',
                            'values' => [
                                ['key' => '12.5x8', 'title' => '12.5x8']
                            ]
                        ]
                    ],
                    'variants' => [
                        ['options' => ['white', '12.5x8'], 'prices' => ['price' => 0, 'compare_at_price' => 0], 'suppliers' => ['tee_launch' => ['sku' => 'GFLAG', 'print_template_id' => 0]]],
                    ]
                ]
            ]
        ];
		
		$suppliers = [];
		$options = [];
		
		$DB = OSC::core('database');
		
		foreach($data['suppliers'] as $supplier_data) {
			if(isset($suppliers[$supplier_data['key']])) {
				continue;
			}

			$DB->insert('supplier', [
				'ukey' => $supplier_data['key'],
				'title' => $supplier_data['title'],
//				'short_title' => $supplier_data['title'],
				'description' => '',
				'status' => 1,
				'added_timestamp' => time(),
				'modified_timestamp' => time()
			]);
		
			$supplier_id = $DB->getInsertedId();
			
			$suppliers[$supplier_data['key']] = $supplier_id;
		}
		
		foreach($data['product_types'] as $product_type_data) {
			$option_map = [];
			$product_type_option_ids = [];
			
			foreach($product_type_data['options'] as $option_data) {				
				if(! isset($options[$option_data['key']])) {
					$DB->insert('product_type_option', [
						'ukey' => $option_data['key'],
						'title' => $option_data['title'],
						'type' => $option_data['type'],
						'description' => '',
						'status' => 1,
						'added_timestamp' => time(),
						'modified_timestamp' => time()
					]);
		
					$option_id = $DB->getInsertedId();
			
					$options[$option_data['key']] = ['id' => $option_id, 'values' => []];
				}

				$option_map[] = $option_data['key'];
				$product_type_option_ids[] = $options[$option_data['key']]['id'];
				
				foreach($option_data['values'] as $option_value_data) {					
					if(! isset($options[$option_data['key']]['values'][$option_value_data['key']])) {
						$DB->insert('product_type_option_value', [
							'ukey' => $option_data['key'] . '/' . $option_value_data['key'],
							'product_type_option_id' => $options[$option_data['key']]['id'],
							'title' => $option_value_data['title'],
							'meta_data' => OSC::encode(isset($option_value_data['meta_data']) ? $option_value_data['meta_data'] : []),
							'status' => 1,
							'added_timestamp' => time(),
							'modified_timestamp' => time()
						]);
		
						$option_value_id = $DB->getInsertedId();
			
						$options[$option_data['key']]['values'][$option_value_data['key']] = ['id' => $option_value_id, 'title' => $option_value_data['title']];
					}
				}
			}
			
			$product_type_option_ids = implode(',', $product_type_option_ids);
			
			$DB->insert('product_type', [
				'ukey' => $product_type_data['key'],
				'title' => $product_type_data['title'],
				'short_title' => $product_type_data['short_title'],
				'identifier' => OSC::encode($product_type_data['identifier']),
				'group_name' => $product_type_data['group'],
				'tab_name' => $product_type_data['tab'],
				'product_type_option_ids' => $product_type_option_ids,
				'image' => $product_type_data['image'],
				'description' => '',
				'status' => 1,
				'added_timestamp' => time(),
				'modified_timestamp' => time()
			]);

			$product_type_id = $DB->getInsertedId();
			
			foreach($product_type_data['variants'] as $variant_data) {
				$variant_ukey = [];
				$variant_title = [$product_type_data['title']];
				
				foreach($option_map as $idx => $option_key) {
					if(! isset($variant_data['options'][$idx])) {
						echo 'ERROR: missing option value in variant';
						die;
					}

					if($variant_data['options'][$idx] == '') {
						$option_value_id = 0;
					} else {
						if(! isset($options[$option_key]['values'][$variant_data['options'][$idx]])) {
							echo 'ERROR: option value is not exists: ' . $product_type_data['key'] . '/' . $option_key . '/' . $variant_data['options'][$idx];
							die;
						}
						
						$variant_title[] = $options[$option_key]['values'][$variant_data['options'][$idx]]['title'];

						$option_value_id = $options[$option_key]['values'][$variant_data['options'][$idx]]['id'];
					}
					
					$variant_ukey[$options[$option_key]['id']] = $options[$option_key]['id'] . ':' . $option_value_id;
				}

				ksort($variant_ukey);

				$variant_title = implode(' ', $variant_title);
				$variant_ukey = $product_type_id . '/' . implode('_', $variant_ukey);

				$price = 0;
				$compare_at_price = 0;

                if (isset($variant_data['prices'])) {
                    $price = intval($variant_data['prices']['price']);
                    $compare_at_price = intval($variant_data['prices']['compare_at_price']);
                }
				
				$DB->insert('product_type_variant', [
					'ukey' => $variant_ukey,
					'product_type_id' => $product_type_id,
					'title' => $variant_title,
					'price' => $price,
					'compare_at_price' => $compare_at_price,
					'status' => 1,
					'added_timestamp' => time(),
					'modified_timestamp' => time()
				]);

				$product_type_variant_id = $DB->getInsertedId();
				
				foreach($variant_data['suppliers'] as $supplier_key => $supplier_variant_sku) {
					if(! isset($suppliers[$supplier_key])) {
						echo 'ERROR: Supplier key is not exists ' . $supplier_key;
						die;
					}

                    $meta_data = [];

					if (is_array($supplier_variant_sku)){
                        $sku = $supplier_variant_sku['sku'];
                        $print_template_id = $supplier_variant_sku['print_template_id'];

                        if (isset($supplier_variant_sku['meta_data'])) {
                            $meta_data = $supplier_variant_sku['meta_data'];
                        }
                    }else{
                        $sku = $supplier_variant_sku;
                        $print_template_id= 0;
                    }


					$DB->insert('supplier_variant_rel', [
						'product_type_variant_id' => $product_type_variant_id,
						'supplier_id' => $suppliers[$supplier_key],
						'supplier_variant_sku' => $sku,
						'print_template_id' => $print_template_id,
						'meta_data' => OSC::encode($meta_data),
						'added_timestamp' => time(),
						'modified_timestamp' => time()
					]);
				}
			}
		}
    }

    public function actionAtc() {
        Helper_Core_AntiCrawler::update();
    }

    public function actionCrawlShopifyOrderEmail() {
        try {
            OSC::core('cron')->appendCron('utils/crawlOrderFromEmail', array('username' => 'admin@deal3s.com', 'password' => '11021989@Ku', 'file' => OSC_VAR_PATH . '/order__admin.deal3s.com.xlsx'));
        } catch (Exception $_ex) {
            
        }

        try {
            OSC::core('cron')->appendCron('utils/crawlOrderFromEmail', array('username' => 'admin@deal3s.net', 'password' => '11021989@Ku', 'file' => OSC_VAR_PATH . '/order__admin.deal3s.net.xlsx'));
        } catch (Exception $_ex) {
            
        }

        try {
            OSC::core('cron')->appendCron('utils/crawlOrderFromEmail', array('username' => 'jessicadecor.com', 'password' => 'Soledad1908@Ku', 'file' => OSC_VAR_PATH . '/order__admin.jessicadecor.com.xlsx'));
        } catch (Exception $_ex) {
            
        }

        echo 'OK';
    }

    public function actionSetLanguage() {
        try {
            OSC::core('language')->languageSwitchTo($this->_request->get('lang'));
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        static::redirect(OSC_FRONTEND_BASE_URL);
    }

    public function actionEditorUploadImage() {
        if (OSC::helper('user/authentication')->getMember()->getId() < 1) {
            $this->_ajaxError(OSC::core('language')->get('core.err_no_permission'));
        }

        try {
            $uploader = new OSC_Uploader();

            $tmp_filename = OSC::helper('user/authentication')->getMember()->getId() . '.' . OSC::makeUniqid() . '.' . $uploader->getExtension();

            try {
                $uploader->save(OSC::getTmpDir() . '/' . $tmp_filename, true);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }

            @chmod(OSC::getTmpDir() . '/' . $tmp_filename, 0644);

            $file_header_data = file_get_contents(OSC::getTmpDir() . '/' . $tmp_filename, null, null, 0, 50);

            if ($file_header_data === false) {
                throw new Exception(OSC::core('language')->get('core.err_tmp_read_failed'), 500);
            }

            try {
                $extension = OSC_File::verifyImageByData($file_header_data);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }

            $file_name = $uploader->getName();

            $file_path = OSC::getTmpDir() . '/' . $tmp_filename;
        } catch (Exception $ex) {
            if ($ex->getCode() == 500) {
                $this->_ajaxError($ex->getMessage());
            }

            $image_url = trim(strval($this->_request->decodeValue($this->_request->get('image_url'))));

            if (!$image_url) {
                throw new Exception(OSC::core('language')->get('core.err_data_incorrect'));
            }

            $image_hash = md5($image_url);

            if (!file_exists(OSC::getTmpDir() . '/' . $image_hash)) {
                try {
                    $url_info = OSC::core('network')->curl($image_url, array('browser'));
                } catch (Exception $ex) {
                    $this->_ajaxError($ex->getMessage());
                }

                if (!$url_info['content']) {
                    throw new Exception(OSC::core('language')->get('core.err_data_incorrect'));
                }

                try {
                    $extension = OSC_File::verifyImageByData($url_info['content']);
                } catch (Exception $ex) {
                    $this->_ajaxError($ex->getMessage());
                }

                if (OSC::writeToFile(OSC::getTmpDir() . '/' . $image_hash, $url_info['content']) === false) {
                    throw new Exception(OSC::core('language')->get('core.err_tmp_save_failed'));
                }

                @chmod(OSC::getTmpDir() . '/' . $image_hash, 0644);
            } else {
                $file_header_data = file_get_contents(OSC::getTmpDir() . '/' . $image_hash, null, null, 0, 50);

                if ($file_header_data === false) {
                    throw new Exception(OSC::core('language')->get('core.err_tmp_read_failed'));
                }

                try {
                    $extension = OSC_File::verifyImageByData($file_header_data);
                } catch (Exception $ex) {
                    $this->_ajaxError($ex->getMessage());
                }
            }

            $file_name = preg_replace('/^.+\/+([^\/]+)(\.[a-zA-Z0-9]+)?$/i', '\\1', $image_url);
            $file_name = ($file_name ? $file_name : 'unknow') . '.' . $extension;

            $file_path = OSC::getTmpDir() . '/' . $image_hash;
        }

        try {
            $img_processor = new OSC_Image();
            $img_processor->setJpgQuality(100)->setImage($file_path)->resize(800);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $file_name = 'editor/' . $width . 'x' . $height . '.' . OSC::helper('user/authentication')->getMember()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

            $tmp_url = OSC::core('aws_s3')->tmpSaveFile($img_processor, $file_name);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $tmp_url = $tmp_url ?? OSC_Storage::tmpGetFileUrl($file_name);

        $this->_ajaxResponse(array(
            'url' => $tmp_url,
            'width' => $width,
            'height' => $height
        ));
    }

    public function actionBrowseCountry() {
        $countries = [];

        $counter = 0;

        foreach (OSC::helper('core/country')->getCountries() as $country_code => $country_title) {
            $counter ++;
            $countries[] = ['id' => $country_code, 'title' => $country_title];
        }

        $this->_ajaxResponse(array(
            'keywords' => [],
            'total' => $counter,
            'offset' => 0,
            'current_page' => 1,
            'page_size' => $counter,
            'items' => $countries
        ));
    }

    public function actionBrowseCountryProvince() {
        $provinces = [];

        $counter = 0;

        if ($this->_request->get('country')) {
            foreach (OSC::helper('core/country')->getProvinces($this->_request->get('country')) as $province_code => $province_title) {
                $counter ++;
                $provinces[] = ['id' => $province_code, 'title' => $province_title];
            }
        } else {
            foreach (OSC::helper('core/country')->getProvinces() as $country_code => $group_province) {
                $counter ++;

                $provinces[$country_code] = [];

                foreach ($group_province as $province_code => $province_title) {
                    $provinces[$country_code][] = ['id' => $province_code, 'title' => $province_title];
                }
            }
        }

        $this->_ajaxResponse(array(
            'keywords' => [],
            'total' => $counter,
            'offset' => 0,
            'current_page' => 1,
            'page_size' => $counter,
            'items' => $provinces
                ), ['cache' => true]);
    }

    public function actionGetCountriesTags() {
        $countries = OSC::helper('core/country')->getCountries();

        if (!is_array($countries) || count($countries) == 0 ){
            $this->_ajaxError('Can\'t load countries' );
        }

        $this->_ajaxResponse(array_values($countries));
    }

    /**
     * Method $_POST
     * If upload image: input name is "file". Ex: <input type="file" name="file" ... />
     * If save from url: input name is "image_url". Ex: <input type="text" name="image_url" ... />
     * module: such as review, photo_upload...
     */
    public function actionUploadImage() {
        $module = $this->_request->get('module');

        switch ($module) {
            case 'review':
                $sub_dir = 'catalog_review.';
                break;
            default:
                $sub_dir = '';
                break;
        }

        try {
            $uploader = new OSC_Uploader();

            $tmp_file_path = OSC::getTmpDir() . '/' . $sub_dir . OSC::makeUniqid() . '.' . $uploader->getExtension();

            try {
                $uploader->save($tmp_file_path, true);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }

            try {
                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }
        } catch (Exception $ex) {
            if ($ex->getCode() == 500) {
                $this->_ajaxError($ex->getMessage());
            }

            $image_url = trim(strval($this->_request->decodeValue($this->_request->get('image_url'))));

            try {
                if (!$image_url) {
                    throw new Exception($this->_('core.err_data_incorrect'));
                }

                $tmp_file_path = OSC::getTmpDir() . '/' . md5($image_url);

                if (!file_exists($tmp_file_path)) {
                    $url_info = OSC::core('network')->curl($image_url, array('browser'));

                    if (!$url_info['content']) {
                        throw new Exception($this->_('core.err_data_incorrect'));
                    }

                    if (OSC::writeToFile($tmp_file_path, $url_info['content']) === false) {
                        throw new Exception($this->_('core.err_tmp_save_failed'));
                    }
                }

                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }
        }

        try {
            $img_processor = new OSC_Image();
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path)->resize(1920);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $file_name = $sub_dir . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

            $tmp_url = OSC::core('aws_s3')->tmpSaveFile($img_processor, $file_name);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $tmp_url = $tmp_url ?? OSC_Storage::tmpGetFileUrl($file_name);

        $this->_ajaxResponse([
            'file' => $file_name,
            'url' => $tmp_url,
            'width' => $width,
            'height' => $height
        ]);
    }

    public function actionGetCountryFromLocation()
    {
        $location = $this->_request->get('location');
        if (!$location) {
            $this->_ajaxError('Can\'t load location' );
        }
        $data_parse = array_values(OSC::helper('core/country')->getCountryCodeByLocation([$location]));
        $countries = [];
        foreach ($data_parse as $country_code) {
            $country_title = OSC::helper('core/country')->getCountryTitle($country_code);
            if ($country_title) {
                $countries[] = $country_title;
            }
        }
        $this->_ajaxResponse($countries);
    }

}
