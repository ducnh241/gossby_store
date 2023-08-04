<?php

OSC_Controller::registerBind('crossSell', 'crossSell');


OSC_Observer::registerObserver('catalog/render/checkout_summary/item', ['Observer_CrossSell_Common', 'checkoutSummaryItem']);
OSC_Observer::registerObserver('catalog/orderVerifyLineItemToCreate', ['Observer_CrossSell_Common', 'orderVerifyLineItemToCreate']);