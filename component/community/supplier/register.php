<?php

OSC_Controller::registerBind('supplier', 'supplier');

OSC_Observer::registerObserver('supplier/syncTracking', ['Observer_Supplier_Tracking', 'syncTracking']);

OSC_Observer::registerObserver('masterSync:supplier/syncTracking', ['Observer_Supplier_MasterSync', 'syncTracking']);