db.getCollection("ads_tracking_analytic").drop();
db.createCollection("ads_tracking_analytic");
db.getCollection("ads_tracking_analytic").createIndex({
    "campaign_id": NumberInt("1"),
    "adset_id": NumberInt("1"),
    "ad_id": NumberInt("1"),
    "sref_id": NumberInt("1"),
    "added_timestamp": NumberInt("-1")
}, {
    name: "unique_record_analytic",
        unique: true
});