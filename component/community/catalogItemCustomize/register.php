<?php

OSC_Controller::registerBind('catalogItemCustomize', 'catalogItemCustomize');

//OSC_Observer::registerObserver('catalog/cart_lineItem_customize', ['Observer_CatalogItemCustomize_Frontend', 'validate']);

OSC_Observer::registerObserver('catalog/product/postFrmRender', ['Observer_CatalogItemCustomize_Backend', 'productPostFrmRender']);

OSC_Observer::registerObserver('catalog/product/postFrmSaveData', ['Observer_CatalogItemCustomize_Backend', 'productPostFrmSaveData']);

OSC_Observer::registerObserver('catalog/order_collect_design', ['Observer_CatalogItemCustomize_Backend', 'orderCollectDesign']);