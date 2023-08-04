<?php

class Cron_Catalog_Product_GetCurrencyRate extends OSC_Cron_Abstract {
    const CRON_TIMER = '0 8 * * *';
//    const CRON_SCHEDULER_FLAG = 0;
    public function process($params, $queue_added_timestamp) {
        $logs = [];

        if (OSC_ENV == 'production'){
            $url = "https://currencyapi.net/api/v1/rates?key=5f1d08148e8ad77c426ab2c8e2ec6597ff76&base=USD";
        }else{
            $url = "https://currencyapi.net/api/v1/rates?key=demo";
        }

        $response = OSC::core('network')->curl($url);

        if (!isset($response['content']['rates']['AED'])){
            $this->_log(OSC::encode($response['content']));
            return;
        }

        $renderSymbolCurrency = OSC::helper('catalog/common')->renderSymbolCurrency();

        $a = $response['content']['rates'];

        foreach ($a as $key => $value) {
            try{
                $model = OSC::model('catalog/product_priceExchangeRate');

                $model->loadByUKey($key);

                $model->setData([
                    'exchange_rate' => floatval($value),
                    'update_timestamp' => time()
                ])->save();

            }catch (Exception $ex){
                if ($ex->getCode() == 404){
                    try{
                        $model->setData([
                            'currency_code' => $key,
                            'exchange_rate' => floatval($value),
                            'symbol' =>  $renderSymbolCurrency[$key],
                            'added_timestamp' => time(),
                            'update_timestamp' => time()
                        ])->save();
                    }catch (Exception $ex){

                    }
                }else{
                    $logs[] = $ex->getMessage();
                }
            }
        }

        if (count($logs) > 0) {
            $this->_log($logs);
        }


    }




}
