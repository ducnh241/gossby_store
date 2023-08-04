<?php
define('OSC_ENV_DEBUG_DB', '');
define('OSC_ENV_FORCE_SSL', '');
define('OSC_REDIRECT_SSL_BY_PHP', '');


//REACTJS - BEGIN
define('OSC_PROTOCOL', 'http');
//define('OSC_HOST', '');
define('OSC_HOST', '');
define('OSC_PORT', '');
define('REDIS_HOST', '');
define('REDIS_PORT', '');

define('OSC_PRIMARY_STORE', 1); // keep some function if OSC_PRIMARY_STORE = 1
define('OSC_IS_DEVELOPER_KEY', 'is_developer'); // param key on url

define('SETTING_PERSONALIZE_SYNC_REQUEUE', 5); // setting_personalize_sync/requeue
define('SETTING_PERSONALIZE_SYNC_NEXT_TIME', 60); // setting_personalize_sync/next_time
define('BOX_TELEGRAM_TELEGRAM_GROUP_ID', '-286728077'); /// box_telegram/telegram_group_id
define('PRODUCT_HIDDEN_TELEGRAM_GROUP_ID', ''); // product_hidden/telegram_group_id

define('OSC_SMTP_HOST', 'email-smtp.us-west-2.amazonaws.com');
define('OSC_SMTP_PORT', 587);
define('OSC_SMTP_SECURE', 'tls');
define('OSC_SMTP_USERNAME', 'AKIAJEP7CHKNPCS4BPKA');
define('OSC_SMTP_PASSWORD', 'AmUtEGRPgU6b0K9pio7ww1ePhlmcf7f30u68xqEJX1ju');
define('OSC_SMTP_SENDER_EMAIL', 'sangletuan@dlsinc.com');
define('OSC_SMTP_SENDER_NAME', 'OSECORE');

define('S3_REGION', '');
define('S3_BUCKET', '');
define('S3_CREDENTIALS_KEY', '');
define('S3_CREDENTIALS_SECRET', '');

define('FRESHDESK_DOMAIN', '');
define('FRESHDESK_API_KEY', '');
define('FRESHDESK_API_PASSWORD', '');
define('FRESHDESK_RESPONDER_ID', 0);

define('CROSS_SELL_KEY', '');
define('CROSS_SELL_SECRET', '');

define('CRM_KEY', '');
define('CRM_SECRET', '');

define('URL_AMZ_D3_ID', 0);
define('URL_AMZ_D3_SERVICE', 'http://store.amazon.com');
define('URL_AMZ_D3_SECRET_KEY', '50e0b6eba237c2a850211204d1f0c000');


/* Env Airtable*/
define('OSC_AIRTABLE_DOMAIN', '');
define('OSC_AIRTABLE_API_KEY', '');
define('OSC_AIRTABLE_DATABASE_KEY', '');
define('OSC_AIRTABLE_ORDER_LINE_TABLE', '');
define('OSC_AIRTABLE_QUEUE_SYNC_DESIGN_TABLE', '');
define('OSC_AIRTABLE_LOG_SYNC_DESIGN_TABLE', '');
define('OSC_AIRTABLE_USER_PROFILE_TABLE', '');

/* ENV CMS, STORE URL */
define('OSC_FRONTEND_DOMAIN', 'store.com');
define('OSC_FRONTEND_BASE_URL', 'https://store.com');
define('OSC_CMS_BASE_URL', 'https://cms.store.com');

/* ENV Sentry */
define('IS_ENABLE_SENTRY', 0);
define('SENTRY_DSN', 'https://xxxxxx@sentry.9prints.com/0');

// BASE URL của D2 Flow
define('D2_FLOW_BASE_URL', 'https://d2-dev.9prints.com');

// Algolia

define('ALGOLIA_ID', '');
define('ALGOLIA_API_KEY', '');
define('ALGOLIA_PRODUCT_INDEX', '');
define('ALGOLIA_REPLICAS_VIRTUAL_NEWEST', '');
define('ALGOLIA_REPLICAS_VIRTUAL_BEST_SELL', '');
define('ALGOLIA_DEFAULT_SHORT_TERM', 14);
