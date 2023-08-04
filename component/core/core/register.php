<?php

OSC_Controller::registerBind('core', 'core');

OSC::addOverride('osc_date', 'helper_core_date');
OSC::addOverride('osc_string', 'helper_core_string');

OSC::systemRegister('root_group', [
    'guest' => 1,
    'member' => 2,
    'admin' => 3
]);

OSC_Cron::registerScheduler('storage_cleanTmp', null, '@daily', ['estimate_time' => 60*15]);

OSC_Template::registerComponent('search', array('type' => 'js', 'data' => '[core]core/search.js'));
OSC_Template::registerComponent(array('key' => 'itemBrowser', 'depends' => 'search'), array('type' => 'js', 'data' => '[core]core/browser.js'), array('type' => 'css', 'data' => array('[core]core/browser.scss')));
OSC_Template::registerComponent(array('key' => 'itemSelector', 'depends' => 'itemBrowser'), array('type' => 'js', 'data' => '[core]core/selector.js'), array('type' => 'css', 'data' => array('[core]core/selector.scss')));
OSC_Template::registerComponent(array('key' => 'autoCompletePopover', 'depends' => 'itemBrowser'), array('type' => 'js', 'data' => '[core]core/autoCompletePopover.js'), array('type' => 'css', 'data' => array('[core]core/autoCompletePopover.scss')));
OSC_Template::registerComponent('datePicker', array('type' => 'js', 'data' => '[core]core/datePicker.js'), array('type' => 'css', 'data' => array('[core]core/datePicker.scss')));
OSC_Template::registerComponent('timePicker', array('type' => 'js', 'data' => '[core]core/timePicker.js'), array('type' => 'css', 'data' => array('[core]core/timePicker.scss')));

OSC_Template::registerComponent('datedropper', array('type' => 'js', 'data' => '/script/community/jquery/plugin/datedropper.js'), array('type' => 'css', 'data' => array('/template/core/style/UI/datedropper.css')));
OSC_Template::registerComponent('timedropper', array('type' => 'js', 'data' => '/script/community/jquery/plugin/timedropper.js'), array('type' => 'css', 'data' => array('/template/core/style/UI/timedropper.css')));

OSC_Template::registerComponent(
        ['key' => 'core', 'default' => true], ['type' => 'js', 'data' => ['[core]common.js', '[core]community/ResizeSensor.js', '[core]core/dynamicUrl.js']], ['type' => 'css', 'data' => ['[core]common.scss']]
);
OSC_Template::registerComponent('md5', array('type' => 'js', 'data' => '/script/core/core/encode/md5.js'));
OSC_Template::registerComponent('base64', array('type' => 'js', 'data' => '/script/core/core/encode/base64.js'));
OSC_Template::registerComponent(array('key' => 'encode', 'depends' => array('md5', 'base64'), 'default' => true));
OSC_Template::registerComponent('togglemenu', array('type' => 'js', 'data' => '/script/core/core/toggleMenu.js'), array('type' => 'css', 'data' => array('/template/core/style/toggleMenu.css')));
OSC_Template::registerComponent(array('key' => 'tabsystem', 'default' => true), array('type' => 'js', 'data' => array('/script/core/core/UI/tabSystem.js')));

OSC_Template::registerComponent('dragger', ['type' => 'js', 'data' => '[core]core/dragger.js']);
OSC_Template::registerComponent('resizer', ['type' => 'js', 'data' => '[core]core/resizer.js'], ['type' => 'css', 'data' => '[core]core/resizer.scss']);
OSC_Template::registerComponent(['key' => 'cropper', 'depends' => ['dragger', 'resizer']], ['type' => 'js', 'data' => '[core]core/cropper.js'], ['type' => 'css', 'data' => '[core]core/cropper.scss']);

OSC_Template::registerComponent('uploader', array('type' => 'js', 'data' => '[core]uploader.js'), array('type' => 'css', 'data' => '[core]uploader.scss'));
OSC_Template::registerComponent(array('key' => 'colorPicker', 'depends' => 'togglemenu'), array('type' => 'js', 'data' => '[core]colorPicker.js'), array('type' => 'css', 'data' => '[core]colorPicker.scss'));

OSC_Template::registerComponent('daterangepicker', ['type' => 'css', 'data' => '[core]community/daterangepicker.css'], ['type' => 'js', 'data' => ['[core]community/moment.min.js', '[core]community/moment.timezone.js', '[core]community/daterangepicker.js']]);

OSC_Template::registerComponent(
        array('key' => 'editor_common', 'default' => true), array(
    'type' => 'css',
    'data' => array(
        '[core]editor/editor.css',
        '[core]editor/style.css',
        '[core]editor/plugin/block_image/style.css',
        '[core]editor/plugin/textbox/style.css',
        '[core]editor/plugin/autosize_textbox/style.css',
        '[core]editor/plugin/embed_block/style.css'
    )
        )
);

OSC_Template::registerComponent(
        ['key' => 'editor', 'depends' => 'colorPicker,uploader,editor_common,dragger'], [
    'type' => 'template',
    'data' => [
        '[core]core/editor'
    ]
        ]
);

OSC_Template::registerComponent('nodeTextEditor', array('type' => 'js', 'data' => '[core]nodeTextEditor.js'));

OSC_Observer::registerObserver('initialize', ['Observer_Core_Common', 'initialize']);
OSC_Observer::registerObserver('backend/collect_menu', ['Observer_Core_Backend', 'collectMenu']);

OSC_Observer::registerObserver('collect_setting_section', ['Observer_Core_Backend', 'collectSettingSection']);
OSC_Observer::registerObserver('collect_setting_item', ['Observer_Core_Backend', 'collectSettingItem']);
OSC_Observer::registerObserver('collect_setting_type', ['Observer_Core_Backend', 'collectSettingType']);
OSC_Observer::registerObserver('parse_redirect_url', array('Observer_Core_Common', 'parseRedirectUrl'));
OSC_Observer::registerObserver('user/permmask/collect_keys', array('Observer_Core_Backend', 'collectPermKey'));
OSC_Observer::registerObserver('log_model', array('Observer_Core_Common', 'logModelMongoDB'));

OSC_Cron::registerScheduler('core/generateSitemap', null, '@daily', ['estimate_time' => 60*60]);
OSC_Cron::registerScheduler('core/estimateTimeRunCron', null, '@hourly');
OSC_Cron::registerScheduler('core/pushNotify', null, '* * * * *', ['estimate_time' => 60]);


//OSC_Template::registerComponent(
//        array('key' => 'editor', 'depends' => 'editor_display,uploader'), array('type' => 'js', 'data' => 'core/core/UI/form/editor.js'), array('type' => 'css', 'data' => array('file' => 'style/core/UI/form/editor/form.css', 'getter' => 'osc_template'))
//);
/*
  OSC_Template::registerComponent('tree', array('type' => 'css', 'data' => 'core/UI/tree.css'));
  OSC_Template::registerComponent('autoexpandform', array('type' => 'js', 'data' => 'core/core/autoExpandForm.js'));
  OSC_Template::registerComponent('editarea', array('type' => 'js', 'data' => 'community/editArea/edit_area_full.js'));
  OSC_Template::registerComponent('tooltip', array('type' => 'js', 'data' => 'core/core/tooltip.js'), array('type' => 'css', 'data' => 'core/tooltip.css'));

  OSC_Template::registerComponent(
  'friendSuggest',
  array('type' => 'js', 'data' => 'core/user/friendSuggest.js'),
  array('type' => 'css', 'data' => 'user/friendSuggest.css')
  );
  OSC_Template::registerComponent(
  'editor',
  array('type' => 'js', 'data' => 'core/form/editor.js'),
  array('type' => 'css', 'data' => array('file' => 'core/form/editor.css', 'getter' => 'template'))
  );
  OSC_Template::registerComponent(
  array(
  'key' => 'core_frontend_UI_form_comment',
  'depends' => array('editor', 'friendSuggest', 'uploader')
  ),
  array('type' => 'template', 'data' => 'core/UI/form/comment/js')
  ); */


OSC_Template::registerComponent('location_group', ['type' => 'css', 'data' => ['/template/backend/style/elements/select2_custom_location.scss', '/template/backend/style/vendor/flag-icon/css/flag-icon.min.css']], ['type' => 'js', 'data' => '/template/backend/script/core/location.js'], ['type' => 'template', 'data' => '/country/group/addForm']);

OSC_Template::registerComponent('select2', ['type' => 'css', 'data' => '/template/backend/style/vendor/select2/select2.min.css'], ['type' => 'js', 'data' => '/template/backend/script/vendor/select2/select2.min.js']);


OSC_Observer::registerObserver('beforeOutput', ['Observer_Core_Image', 'collectOptimizedImages']);

OSC_Observer::registerObserver('user/permmask/collect_keys', ['Observer_Core_Backend', 'collectPermKey']);
