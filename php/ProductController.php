<?php

namespace Realmdigital\Web\Controller;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;

/**
 * @SLX\Controller(prefix="product/")
 */
class ProductController {

    /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri="/{id}")
     * )
     * @param Application $app
     * @param $name
     * @return
     */
    public function GET_ProductById(Application $app, $id) {
        $options = [
            CURLOPT_URL => 'http://192.168.0.241/eanlist?type=Web',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => ['id' => $id],
            CURLOPT_RETURNTRANSFER => 1
        ];
        $response = requestData($options);        
        /*TODO Important note, bug in old code:
        currency != curreny, 
        old api had spelling error and returned it in response, 
        has to be changed for consistency on API that uses this function. */ 
        $result = processPrices($result, $response);
        return $app->render('products/product.detail.twig', $result);
    }

    /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri="/search/{name}")
     * )
     * @param Application $app
     * @param $name
     * @return
     */
    public function GET_ProductByName(Application $app, $name){
        $options = [
            CURLOPT_URL => 'http://192.168.0.241/eanlist?type=Web',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => ['names' => $name],
            CURLOPT_RETURNTRANSFER => 1
        ];
        $response = requestData($options);
        $result = processPrices($response);
        return $app->render('products/products.twig', $result);
    }

    private function processPrices($priceList) {
        $result = [];
        foreach ($response as $responseLine) {
            $prod = [];
            $prod['ean'] = $responseLine['barcode'];
            $prod['name'] = $responseLine['itemName'];
            $prod['prices'] = array();
            
            foreach ($responseLine['prices'] as $price) {
                if ($price['currencyCode'] != 'ZAR') {
                    $prod['prices'][] = [
                        'price' => $price['sellingPrice'],
                        'curreny' => $price['currencyCode']
                    ];
                }
            }
            $result[] = $prod;
        }
        return $result;
    }

    private function requestData($curlOptions) {
        $curl = curl_init();        
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);
        return $response;
    }
}
