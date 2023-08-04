db.getCollection("product_product_type_rel").drop();
db.createCollection("product_product_type_rel");
db.getCollection("product_product_type_rel").createIndex({
    "product_id": NumberInt("1"),
    "product_type_id": NumberInt("1")
}, {
    name: "product_id_1_product_type_id_1",
    unique: true
});

db.getCollection("product_product_type_variant_rel").drop();
db.createCollection("product_product_type_variant_rel");
db.getCollection("product_product_type_variant_rel").createIndex({
    "product_id": NumberInt("1"),
    "product_type_variant_id": NumberInt("1")
}, {
    name: "product_id_1_product_type_variant_id_1",
    unique: true
});

db.getCollection("product_product_type_option_rel").drop();
db.createCollection("product_product_type_option_rel");
db.getCollection("product_product_type_option_rel").createIndex({
    "product_id": NumberInt("1"),
    "product_type_option_id": NumberInt("1")
}, {
    name: "product_id_1_product_type_option_id_1",
    unique: true
});

db.getCollection("product_product_type_option_value_rel").drop();
db.createCollection("product_product_type_option_value_rel");
db.getCollection("product_product_type_option_value_rel").createIndex({
    "product_id": NumberInt("1"),
    "product_type_option_value_id": NumberInt("1")
}, {
    name: "product_id_1_product_type_option_value_id_1",
    unique: true
});

db.getCollection("product_description_rel").drop();
db.createCollection("product_description_rel");
db.getCollection("product_description_rel").createIndex({
    "product_id": NumberInt("1"),
    "description_id": NumberInt("1")
}, {
    name: "product_id_1_description_id_1",
    unique: true
});

db.getCollection("product_print_template_rel").drop();
db.createCollection("product_print_template_rel");
db.getCollection("product_print_template_rel").createIndex({
    "product_id": NumberInt("1"),
    "print_template_id": NumberInt("1")
}, {
    name: "product_id_1_print_template_id_1",
    unique: true
});

db.getCollection("product_product_pack_rel").drop();
db.createCollection("product_product_pack_rel");
db.getCollection("product_product_pack_rel").createIndex({
    "product_id": NumberInt("1"),
    "product_pack_id": NumberInt("1")
}, {
    name: "product_id_1_product_pack_id_1",
    unique: true
});

db.getCollection("reset_cache_queue").drop();
db.createCollection("reset_cache_queue");
db.getCollection("reset_cache_queue").createIndex({
    "type": 1,
    "id": 1,
    "status": 1
}, {
    unique: true
});