// ----------------------------
// Collection structure for ads_tracking_add_to_cart
// ----------------------------
db.getCollection("ads_tracking_add_to_cart").drop();
db.createCollection("ads_tracking_add_to_cart");
db.getCollection("ads_tracking_add_to_cart").createIndex({
    "campaign_id": NumberInt("1")
}, {
    name: "campaign_id_1"
});
db.getCollection("ads_tracking_add_to_cart").createIndex({
    "adset_id": NumberInt("1")
}, {
    name: "adset_id_1"
});
db.getCollection("ads_tracking_add_to_cart").createIndex({
    "ad_id": NumberInt("1")
}, {
    name: "ad_id_1"
});
db.getCollection("ads_tracking_add_to_cart").createIndex({
    "added_timestamp": NumberInt("-1")
}, {
    name: "added_timestamp_-1"
});
db.getCollection("ads_tracking_add_to_cart").createIndex({
    "session_id": NumberInt("1")
}, {
    name: "session_id_1"
});
db.getCollection("ads_tracking_add_to_cart").createIndex({
    "sref_id": NumberInt("-1")
}, {
    name: "sref_id_-1"
});
db.getCollection("ads_tracking_add_to_cart").createIndex({
    "campaign_id": NumberInt("1"),
    "adset_id": NumberInt("1"),
    "ad_id": NumberInt("1"),
    "sref_id": NumberInt("1"),
    "track_ukey": NumberInt("1"),
    "session_id": NumberInt("1"),
    "product_id": NumberInt("1")
}, {
    name: "unique_record",
    unique: true
});

// ----------------------------
// Collection structure for ads_tracking_checkout_initialize
// ----------------------------
db.getCollection("ads_tracking_checkout_initialize").drop();
db.createCollection("ads_tracking_checkout_initialize");
db.getCollection("ads_tracking_checkout_initialize").createIndex({
    "campaign_id": NumberInt("1")
}, {
    name: "campaign_id_1"
});
db.getCollection("ads_tracking_checkout_initialize").createIndex({
    "adset_id": NumberInt("1")
}, {
    name: "adset_id_1"
});
db.getCollection("ads_tracking_checkout_initialize").createIndex({
    "ad_id": NumberInt("1")
}, {
    name: "ad_id_1"
});
db.getCollection("ads_tracking_checkout_initialize").createIndex({
    "added_timestamp": NumberInt("-1")
}, {
    name: "added_timestamp_-1"
});
db.getCollection("ads_tracking_checkout_initialize").createIndex({
    "session_id": NumberInt("1")
}, {
    name: "session_id_1"
});
db.getCollection("ads_tracking_checkout_initialize").createIndex({
    "sref_id": NumberInt("-1")
}, {
    name: "sref_id_-1"
});
db.getCollection("ads_tracking_checkout_initialize").createIndex({
    "campaign_id": NumberInt("1"),
    "adset_id": NumberInt("1"),
    "ad_id": NumberInt("1"),
    "sref_id": NumberInt("1"),
    "track_ukey": NumberInt("1"),
    "session_id": NumberInt("1"),
    "cart_id": NumberInt("1")
}, {
    name: "unique_record",
    unique: true
});

// ----------------------------
// Collection structure for ads_tracking_product_view
// ----------------------------
db.getCollection("ads_tracking_product_view").drop();
db.createCollection("ads_tracking_product_view");
db.getCollection("ads_tracking_product_view").createIndex({
    "campaign_id": NumberInt("1")
}, {
    name: "campaign_id_1"
});
db.getCollection("ads_tracking_product_view").createIndex({
    "adset_id": NumberInt("1")
}, {
    name: "adset_id_1"
});
db.getCollection("ads_tracking_product_view").createIndex({
    "ad_id": NumberInt("1")
}, {
    name: "ad_id_1"
});
db.getCollection("ads_tracking_product_view").createIndex({
    "added_timestamp": NumberInt("-1")
}, {
    name: "added_timestamp_-1"
});
db.getCollection("ads_tracking_product_view").createIndex({
    "session_id": NumberInt("1")
}, {
    name: "session_id_1"
});
db.getCollection("ads_tracking_product_view").createIndex({
    "sref_id": NumberInt("-1")
}, {
    name: "sref_id_-1"
});
db.getCollection("ads_tracking_product_view").createIndex({
    "campaign_id": NumberInt("1"),
    "adset_id": NumberInt("1"),
    "ad_id": NumberInt("1"),
    "sref_id": NumberInt("1"),
    "track_ukey": NumberInt("1"),
    "session_id": NumberInt("1"),
    "product_id": NumberInt("1")
}, {
    name: "unique_record",
    unique: true
});

// ----------------------------
// Collection structure for ads_tracking_purchase
// ----------------------------
db.getCollection("ads_tracking_purchase").drop();
db.createCollection("ads_tracking_purchase");
db.getCollection("ads_tracking_purchase").createIndex({
    "added_timestamp": NumberInt("-1")
}, {
    name: "added_timestamp_-1"
});
db.getCollection("ads_tracking_purchase").createIndex({
    "campaign_id": NumberInt("1")
}, {
    name: "campaign_id_1"
});
db.getCollection("ads_tracking_purchase").createIndex({
    "adset_id": NumberInt("1")
}, {
    name: "adset_id_1"
});
db.getCollection("ads_tracking_purchase").createIndex({
    "ad_id": NumberInt("1")
}, {
    name: "ad_id_1"
});
db.getCollection("ads_tracking_purchase").createIndex({
    "session_id": NumberInt("1")
}, {
    name: "session_id_1"
});
db.getCollection("ads_tracking_purchase").createIndex({
    "sref_id": NumberInt("-1")
}, {
    name: "sref_id_-1"
});
db.getCollection("ads_tracking_purchase").createIndex({
    "order_id": NumberInt("1")
}, {
    name: "unique_record",
    unique: true
});
