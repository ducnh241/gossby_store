SET SQL_SAFE_UPDATES = 0;
UPDATE osc_page SET page_key = 'term_of_service' WHERE (slug = 'Terms_of_Service');
UPDATE osc_page SET page_key = 'privacy_policy' WHERE (slug = 'Privacy_Policy');
SET SQL_SAFE_UPDATES = 1;