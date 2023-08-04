--  Start run store and master
ALTER TABLE `osc_product_type` ADD COLUMN `amazon_status` TINYINT(1) NULL DEFAULT '0' AFTER `status`;

UPDATE `osc_product_type` SET `amazon_status` = 1 WHERE `id` IN (7,8,11,18,49);
-- End run store and master

-- Run only on store
ALTER TABLE osc_catalog_product_image
ADD COLUMN `is_upload_mockup_amazon` SMALLINT(3) NOT NULL DEFAULT 0 AFTER `is_static_mockup`,
ADD COLUMN `is_show_product_type_variant_image` SMALLINT(3) NOT NULL DEFAULT 0 AFTER `is_upload_mockup_amazon`;

CREATE TABLE `osc_product_type_variant_amazon_map`  (
  `id` int  NOT NULL AUTO_INCREMENT,
  `product_type_variant_id` int  NOT NULL,
  `title` varchar(45)  NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
);


-- Records of osc_product_type_variant_amazon_map
-- ----------------------------
INSERT INTO `osc_product_type_variant_amazon_map` VALUES (1, 404, 'Medallion Alu. One-Sided');
INSERT INTO `osc_product_type_variant_amazon_map` VALUES (2, 577, 'Medallion Alu. Double-Sided');
INSERT INTO `osc_product_type_variant_amazon_map` VALUES (3, 385, 'Ceramic Mug White 11 oz');
INSERT INTO `osc_product_type_variant_amazon_map` VALUES (4, 386, 'Ceramic Mug White 15 oz');
INSERT INTO `osc_product_type_variant_amazon_map` VALUES (5, 387, 'Two Tone Mug Black 11 oz');
INSERT INTO `osc_product_type_variant_amazon_map` VALUES (6, 388, 'Two Tone Mug Blue 11 oz');
INSERT INTO `osc_product_type_variant_amazon_map` VALUES (7, 389, 'Two Tone Mug Red 11 oz');
INSERT INTO `osc_product_type_variant_amazon_map` VALUES (8, 390, 'Two Tone Mug Navy 11 oz');
INSERT INTO `osc_product_type_variant_amazon_map` VALUES (9, 391, 'Two Tone Mug Pink 11 oz');
INSERT INTO `osc_product_type_variant_amazon_map` VALUES (10, 394, 'Fleece Blanket White 30x40');
INSERT INTO `osc_product_type_variant_amazon_map` VALUES (11, 395, 'Fleece Blanket White 50x60');
INSERT INTO `osc_product_type_variant_amazon_map` VALUES (12, 396, 'Fleece Blanket White 60x80');

-- update record product amazon description

UPDATE `osc_catalog_product_description_by_amazon` SET `description` = 'Standing out from the crowd, the Gossby [Quote]  customized Christmas Ornament is a must-have item for the festive holiday ahead. With a touch of personalization, this Christmas ornament will not only be a unique Christmas decoration but a heartfelt gift expressing love to mothers or daughters. The base designs and messages printed on the personalized ornament can be changed, offering a great chance to add your own personal touches.\r\nWhether you\'re looking for mother Christmas ornaments or daughter Christmas ornaments, Gossby customizable ornament will be a perfect option.', `key_product_features` = '{\"key_product_features1\":\"Personalized: Customizable mother-daughter Christmas ornament with creative designs and a heartfelt quote. Add your personal touches to speak the unspoken, dedicated to your special lady.\",\"key_product_features2\":\"Unique: One-of-a-kind Christmas gift for loving mom/daughter decorated with precious memories and heartfelt stories. A true representation of love that cannot be duplicated.\",\"key_product_features3\":\"Durable: Customized Christmas ornaments made of enduring, top-quality materials (Aluminium, MDF/ Plastic). Lightweight, non-magnetic and non-sparking.\",\"key_product_features4\":\"Festive: A decorative item that brings the joyous Christmas vibe to your home. Fill the living space with merry colors and stunning designs.\",\"key_product_features5\":\"Guaranteed: 100% MONEY BACK GUARANTEE. Get a 100% refund if you dislike our product for whatever reason.\"}', `keywords` = 'personalized Christmas ornaments customized Christmas ornaments custom Christmas ornaments daughter Christmas ornament mother Christmas ornaments mother daughter Christmas ornaments mother daughter Christmas gifts Christmas gifts mom Christmas gifts daughter' WHERE `id` = 1;
UPDATE `osc_catalog_product_description_by_amazon` SET `description` = 'Get ready for this holiday season with our new personalized Christmas ornament for best friends! The [Quote] ornament will be a wonderful piece to finish the Christmas tree decorations. It comes with the option to add up to 9 names below the quote, perfect for any group size or your besties. The base designs and messages printed on the personalized Christmas ornament can be changed, offering a great choice of a unique gift or keepsake.', `key_product_features` = '{\"key_product_features1\":\"Personalized: Customizable Best Friend Christmas ornament with creative designs and a heartfelt quote. Add your personal touches to speak the unspoken, dedicated to your special lady.\",\"key_product_features2\":\"Unique: One-of-a-kind Christmas gift for loving best friend decorated with precious memories and heartfelt stories. A true representation of love that cannot be duplicated.\",\"key_product_features3\":\"Durable: Customized Christmas ornaments made of enduring, top-quality materials (Aluminium, MDF/ Plastic). Lightweight, non-magnetic and non-sparking.\",\"key_product_features4\":\"Festive: A decorative item that brings the joyous Christmas vibe to your home. Fill the living space with merry colors and stunning designs.\",\"key_product_features5\":\"Guaranteed: 100% MONEY BACK GUARANTEE. Get a 100% refund if you dislike our product for whatever reason.\"}', `keywords` = 'personalized christmas ornaments gifts for friends customized diy ornaments xmas decorations customized ornaments best christmas gifts for friends christmas gifts for best friends girls Christmas gifts for her gift for best friend female' WHERE `id` = 2;
UPDATE `osc_catalog_product_description_by_amazon` SET `description` = 'MERRY CHRISTMAS 2021! Let such a meaningful gift accompany you through a wonderful Christmas. Gossby personalized Christmas ornament is the perfect Christmas gift for your sisters. Our products feature impeccable designs and a wide selection of heartwarming quotes for you to indulge yourself. Hanging the [Quote]  personalized ornament on your Christmas tree or sending it to sisters, families, and friends as a holiday gift, will be a perfect way to create lasting, loving memories.', `key_product_features` = '{\"key_product_features1\":\"Personalized: Customizable Sister Christmas ornament with creative designs and a heartfelt quote. Add your personal touches to speak the unspoken, dedicated to your special lady.\",\"key_product_features2\":\"Unique: One-of-a-kind Christmas gift for loving sister decorated with precious memories and heartfelt stories. A true representation of love that cannot be duplicated.\",\"key_product_features3\":\"Durable: Customized Christmas ornaments made of enduring, top-quality materials (Aluminium, MDF/ Plastic). Lightweight, non-magnetic and non-sparking.\",\"key_product_features4\":\"Festive: A decorative item that brings the joyous Christmas vibe to your home. Fill the living space with merry colors and stunning designs.\",\"key_product_features5\":\"Guaranteed: 100% MONEY BACK GUARANTEE. Get a 100% refund if you dislike our product for whatever reason.\"}', `keywords` = 'Personalized Christmas Ornaments 2021 customized Christmas ornaments custom Xmas ornaments Christmas tree decoration sister ornament sister christmas ornaments personalized sister ornaments Christmas gifts for sister DIY sister gifts\"' WHERE `id` = 3;
UPDATE `osc_catalog_product_description_by_amazon` SET `description` = 'When sisters stand shoulder to shoulder, who stands a chance against us? This personalized sister mug with names and text, [Quote], will express to your besties how special they are to you. It comes with a beautiful, glossy finish and easy-grip handle. Each BFF mug has printed on both sides that can never come off, adding an aesthetic feel to your coffee drinking game. Gift the mugs to your coffee-loving friends on birthdays, Graduation day, Christmas, & other occasions.', `key_product_features` = '{\"key_product_features1\":\"Personalized: Customize-easy best friend Christmas mug (both sides). Incorporate joyous memories to create the most special cup, dedicated to only your loving besties.\",\"key_product_features2\":\"Durable: High-quality ceramic Christmas mug for everyone. Dishwasher-friendly, microwave-safe with toxin-free printing.\",\"key_product_features3\":\"Unique: Original artworks by Gossby. Add personal touches through the options available to create a one-of-a-kind design, copy-proof. \",\"key_product_features4\":\"Stain Resistant: Heavy-duty protective coating to minimize coffee or tea stains. Easy cleaning by hand or dishwasher.\",\"key_product_features5\":\"Guarantee: 100% MONEY BACK GUARANTEE. Get a 100% refund if you dislike our product for whatever reason.\"}', `keywords` = 'xmas gifts for friends best friends mugs for women personalized mug for friends presents for best friends gifts for friends friendship gifts for her birthday gifts for women christmas gifts for friends customized gifts for best friend' WHERE `id` = 4;
UPDATE `osc_catalog_product_description_by_amazon` SET `description` = 'Chances are, special times bring special memories with your cherished people. This Christmas, it\'s a great time to tell the world how much you care about them by giving this personalized Christmas coffee mug for your mother, daughter.\r\nSometimes the best gifts come from the heart and that’s when a mother-daughter mug can come in handy. With a touch of customization, the [Quote] Christmas mug is a wonderful way to remember all the good times in life with your mother and daughter.', `key_product_features` = '{\"key_product_features1\":\"Personalized: Customize-easy mother-daughter Christmas mug (both sides). Incorporate joyous memories to create the most special cup, dedicated to only your loving mother/daughter.\",\"key_product_features2\":\"Durable: High-quality ceramic Christmas mug for everyone. Dishwasher-friendly, microwave-safe with toxin-free printing.\",\"key_product_features3\":\"Unique: Original artworks by Gossby. Add personal touches through the options available to create a one-of-a-kind design, copy-proof.\",\"key_product_features4\":\"Stain Resistant: Heavy-duty protective coating to minimize coffee or tea stains. Easy cleaning by hand or dishwasher.\",\"key_product_features5\":\"Guarantee: 100% MONEY BACK GUARANTEE. Get a 100% refund if you dislike our product for whatever reason.\"}', `keywords` = 'Christmas mugs Christmas coffee mugs personalized Christmas mugs custom Christmas mugs customized Christmas mugs customizable mugs Christmas mugs for mom mother daughter mugs mother daughter coffee mugs mother daughter Christmas gifts Christmas gifts for mom' WHERE `id` = 5;
UPDATE `osc_catalog_product_description_by_amazon` SET `description` = 'What would be better than giving your cherished sisters our custom sister mugs to celebrate your sisterhood? This coffee mug [Quote] with personal touches will be treasured keepsakes, reminding you of beautiful relationships.  It’s got great sentimental value while still being practical, so you can be sure that she’ll use it every morning. Just imagine her sipping coffee out of this while at work and smiling at the memory of you. This mug is perfect for birthdays, holidays, and any other special occasion!', `key_product_features` = '{\"key_product_features1\":\"Personalized: Customize-easy sister Christmas mug (both sides). Incorporate joyous memories to create the most special cup, dedicated to only your loving sisters.\",\"key_product_features2\":\"Durable: High-quality ceramic Christmas mug for everyone. Dishwasher-friendly, microwave-safe with toxin-free printing.\",\"key_product_features3\":\"Unique: Original artworks by Gossby. Add personal touches through the options available to create a one-of-a-kind design, copy-proof.\",\"key_product_features4\":\"Stain Resistant: Heavy-duty protective coating to minimize coffee or tea stains. Easy cleaning by hand or dishwasher.\",\"key_product_features5\":\"Guarantee: 100% MONEY BACK GUARANTEE. Get a 100% refund if you dislike our product for whatever reason.\"}', `keywords` = 'custom coffee mugs for women christmas mugs personalized christmas mugs personalized sister mugs christmas gifts for sisters customized christmas mugs DIY gifts for sisters Xmas sister gifts' WHERE `id` = 6;

INSERT INTO `osc_catalog_product_description_by_amazon`(`id`, `product_type_ids`, `niche_id`, `niche_title`, `description`, `key_product_features`, `keywords`, `added_timestamp`, `modified_timestamp`) VALUES (7, '11,12', 7, 'Mother & Daughter Blanket', 'Surprise your mom or your daughter with a special custom blanket that features their images, made just for them! This [Quote] mother-daughter blanket is a comfort item leaving them feeling hugged without the hassle of having to be with a person. If you\'re searching for a soft and delicate blanket that\'s still toasty warm for your loved ones, this customizable blanket for mom and daughter is ideal.', '{\"key_product_features1\":\"Personalized: Mom - daughter blankets are customizable on the front and the back is solid cream. Add your own personal touches (images, names...) to create special custom blanket for mom and daughter.\",\"key_product_features2\":\"Durable: Cozy plush fleece personalized blanket for mom and daughter, made of 100% polyester fleece. Machine washable with cold water and tumble dry on low heat. Don\'t dry clean or iron, press with heat.\",\"key_product_features3\":\"Unique: All customizable options are originated by Gossby. Freely add your own personal touches via personalized section to create on-of-a-kind customized to surprise your mom and daughter.\",\"key_product_features4\":\"Functional: Perfect to be a decorative bed blanket, fleece blanket for couch, or using as a tapestry by hanging on the wall. It would be best photo blanket customized using your own photos to surprise your mom and daugher.\",\"key_product_features5\":\"Guarantee: 100% MONEY BACK GUARANTEE. Get a 100% refund if you dislike our product for whatever reason.\"}', 'custom blanket personalized blanket customized blanket mom blanket daughter blanket mom and daughter picture blanket personalized customizable blanket photo blanket customized custom blanket with picture', 1638464356, 1638464356);

INSERT INTO `osc_catalog_product_description_by_amazon`(`id`, `product_type_ids`, `niche_id`, `niche_title`, `description`, `key_product_features`, `keywords`, `added_timestamp`, `modified_timestamp`) VALUES (8, '11,12', 8, 'Best Friends Blanket', 'If you\'re on the hunt for the best way to show your BFF how much you love them, this [Quote] personalized best friend blanket is one of the perfect ideas to show your friendship is forever. The Best Friend blanket is customizable with your own personal touches (names and pictures). More than just an item for snuggling up on the couch watching TV, this custom friend blanket is truly a keepsake, reminding your BFF of an unbreakable friendship.', '{\"key_product_features1\":\"Personalized: Friend blankets are customizable on the front and the back is solid cream. Add your own personal touches (images, names...) to create special best friend blankets.\",\"key_product_features2\":\"Durable: Cozy plush fleece blanket for best friends, made of 100% polyester fleece. Machine washable with cold water and tumble dry on low heat. Don\'t dry clean or iron, press with heat.\",\"key_product_features3\":\"Unique: All customizable options are originated by Gossby. Freely add your own personal touches via personalized section to create on-of-a-kind friendship blankets to surprise your BFF.\",\"key_product_features4\":\"Functional: Perfect to be a decorative bed blanket, fleece blanket for couch, or using as a tapestry by hanging on the wall. It would be best friend blanket gifts for women.\",\"key_product_features5\":\"Guarantee: 100% MONEY BACK GUARANTEE. Get a 100% refund if you dislike our product for whatever reason.\"}', 'friends blanket best friend blanket friends throw blanket friendship blanket friend blanket gifts for women best friend blanket personalized best friend gifts for women blanket friends fleece blanket best friends blanket best friend throw blanket', 1638464356, 1638464356);

INSERT INTO `osc_catalog_product_description_by_amazon`(`id`, `product_type_ids`, `niche_id`, `niche_title`, `description`, `key_product_features`, `keywords`, `added_timestamp`, `modified_timestamp`) VALUES (9, '11,12', 9, 'Sisters Blanket', 'Whether you\'re looking for an item to keep your sister warm or a perfect gift to show how much you love her, this [Quote] custom sister blanket will be what you need. This personalized sister blanket is ideal for picnics in the park, outdoor gatherings, and comforting winter snuggles since it\'s delicate, soft, and colorful.', '{\"key_product_features1\":\"Personalized: Sister blankets are customizable on the front and the back is solid cream. Add your own personal touches (images, names...) to create special blankets for sisters.\",\"key_product_features2\":\"Durable: Cozy plush fleece blanket for sister, made of 100% polyester fleece. Machine washable with cold water and tumble dry on low heat. Don\'t dry clean or iron, press with heat.\",\"key_product_features3\":\"Unique: All customizable options are originated by Gossby. Freely add your own personal touches via personalized section to create on-of-a-kind friendship blankets to surprise your sister.\",\"key_product_features4\":\"Functional: Perfect to be a decorative bed blanket, fleece blanket for couch, or using as a tapestry by hanging on the wall. It would be best sister blanket gifts.\",\"key_product_features5\":\"Guarantee: 100% MONEY BACK GUARANTEE. Get a 100% refund if you dislike our product for whatever reason. \"}', 'sister blanket sisters throw blanket sister throw blanket blanket for sister to my sister blanket sisters gifts from sister blanket', 1638464356, 1638464356);

-- end run only on store