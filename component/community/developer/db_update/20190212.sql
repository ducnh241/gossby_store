INSERT INTO osc_permission_masks (`title`, `permission_data`, `added_timestamp`, `modified_timestamp`) VALUES ( 'Manage Purchase', 'backend,catalog,catalog/product,catalog/order,catalog/order/edit,catalog/order/edit/locked,catalog/order/fulfill,catalog/order/fulfill/locked,catalog/order/fulfill/bulk,catalog/order/fulfill/edit,catalog/order/fulfill/edit/locked,catalog/order/unfulfill,catalog/order/unfulfill/locked,catalog/order/unfulfill/bulk,catalog/order/comment,catalog/order/export,catalog/order/get_tracking_code,report', '1575280159', '0');
INSERT INTO osc_permission_masks (`title`, `permission_data`, `added_timestamp`, `modified_timestamp`) VALUES ('Purchase1', 'backend,catalog,catalog/product,catalog/order,catalog/order/edit,catalog/order/edit/locked,catalog/order/fulfill,catalog/order/fulfill/locked,catalog/order/fulfill/edit,catalog/order/fulfill/edit/locked,catalog/order/unfulfill,catalog/order/unfulfill/locked,catalog/order/comment,catalog/order/export,catalog/order/get_tracking_code', '1575280342', '0');
INSERT INTO osc_permission_masks (`title`, `permission_data`, `added_timestamp`, `modified_timestamp`) VALUES ('Manage Support', 'backend,catalog,catalog/product,catalog/order,catalog/order/edit,catalog/order/edit/locked,catalog/order/capture,catalog/order/capture/bulk,catalog/order/capture/locked,catalog/order/cancel,catalog/order/cancel/locked,catalog/order/refund,catalog/order/refund/locked,catalog/order/fulfill,catalog/order/fulfill/locked,catalog/order/unfulfill,catalog/order/unfulfill/locked,catalog/order/resend_email,catalog/order/send_email,catalog/order/export,catalog/discount,catalog/discount/add,catalog/discount/edit,catalog/discount/delete,page,page/add,page/edit,page/delete', '1575280778', '0');
INSERT INTO osc_permission_masks (`title`, `permission_data`, `added_timestamp`, `modified_timestamp`) VALUES ('Support1', 'backend,catalog,catalog/product,catalog/order,catalog/order/edit,catalog/order/edit/locked,catalog/order/capture,catalog/order/cancel,catalog/order/refund,catalog/order/refund/locked,catalog/order/unfulfill,catalog/order/unfulfill/locked,catalog/order/comment,catalog/order/resend_email,catalog/order/send_email,catalog/discount,catalog/discount/add,catalog/discount/edit', '1575281284', '0');
INSERT INTO osc_permission_masks (`title`, `permission_data`, `added_timestamp`, `modified_timestamp`) VALUES ('Support-Freelancer', 'backend,catalog,catalog/product,catalog/order,catalog/order/edit,catalog/order/edit/locked,catalog/order/capture,catalog/order/unfulfill,catalog/order/unfulfill/locked,catalog/order/comment,catalog/discount', '1575281450', '1575281747');
INSERT INTO osc_permission_masks (`title`, `permission_data`, `added_timestamp`, `modified_timestamp`) VALUES ('Marketing', 'personalized_design,personalized_design/add,personalized_design/edit,personalized_design/delete,personalized_design/view_report,personalized_design/export,catalog,catalog/product,catalog/product/add,catalog/product/edit,catalog/product/delete,catalog/product/tab,catalog/product/tab/add,catalog/product/tab/edit,catalog/product/tab/delete,catalog/product/sizingChart,catalog/product/sizingChart/add,catalog/product/sizingChart/edit,catalog/product/sizingChart/delete,catalog/collection,catalog/collection/add,catalog/collection/edit,catalog/collection/delete,catalog/review,catalog/review/add,catalog/review/edit,catalog/review/delete,catalog/review/approve,catalog/discount,catalog/discount/edit,navigation,navigation/edit,report', '1575281640', '0');
INSERT INTO osc_permission_masks (`title`, `permission_data`, `added_timestamp`, `modified_timestamp`) VALUES ('Tài Chính', 'catalog,catalog/order,catalog/order/export,report', '1575281938', '0');

INSERT INTO osc_groups (`group_id`, `lock_flag`, `title`, `perm_mask_ids`, `added_timestamp`, `modified_timestamp`) VALUES ('0', 'Manage Purchase', '11', '1575337819', '0');
INSERT INTO osc_groups (`group_id`, `lock_flag`, `title`, `perm_mask_ids`, `added_timestamp`, `modified_timestamp`) VALUES ('0', 'Purchase1', '12', '1575337838', '0');
INSERT INTO osc_groups (`group_id`, `lock_flag`, `title`, `perm_mask_ids`, `added_timestamp`, `modified_timestamp`) VALUES ('0', 'Manage Support', '', '1575337847', '0');
INSERT INTO osc_groups (`group_id`, `lock_flag`, `title`, `perm_mask_ids`, `added_timestamp`, `modified_timestamp`) VALUES ('0', 'Support1', '14', '1575337861', '0');
INSERT INTO osc_groups (`group_id`, `lock_flag`, `title`, `perm_mask_ids`, `added_timestamp`, `modified_timestamp`) VALUES ('0', 'Support-Freelancer', '', '1575337871', '0');
INSERT INTO osc_groups (`group_id`, `lock_flag`, `title`, `perm_mask_ids`, `added_timestamp`, `modified_timestamp`) VALUES ('0', 'Marketing', '16', '1575337879', '1575337890');
INSERT INTO osc_groups (`group_id`, `lock_flag`, `title`, `perm_mask_ids`, `added_timestamp`, `modified_timestamp`) VALUES ('0', 'Tài Chính', '', '1575337912', '0');