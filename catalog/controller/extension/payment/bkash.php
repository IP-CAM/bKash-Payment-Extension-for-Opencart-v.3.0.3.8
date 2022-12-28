<?php

class ControllerExtensionPaymentBkash extends Controller {

    public function index() { 
        $this->language->load('payment/bkash');
        $this->load->model('checkout/order');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading'] = $this->language->get('text_loading');


        $bkashscripturl = $this->config->get('payment_bkash_bkashscripturl');
        //$this->document->addScript($bkashscripturl);

        $order_id = $this->session->data['order_id'];
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']);
        $data['amount'] = $order_info['total'];

//		  $placeholder=array('{orderId}','{orderTotal}');
//		  $replacer=array($order_id,$amount);
//		  $bkash_instruction=str_replace($placeholder,$replacer,$bkash_instruction);


        $data['bkashscripturl'] = $bkashscripturl;
        $data['continue'] = $this->url->link('checkout/success');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/bkash')) {
            return $this->load->view($this->config->get('config_template') . '/template/extension/payment/bkash', $data);
        } else {
            return $this->load->view('default/template/extension/payment/bkash', $data);
        }
    }

    public function createPayment() {
        // we need it to get any order detailes

        $order_id = $this->session->data['order_id'];
        $this->load->model('checkout/order'); // call this only if this model is not yet instantiated!
        $order = $this->model_checkout_order->getOrder($order_id);
       // echo $amount = $this->currency->format($order['total'], $order['currency_code'], $order['currency_value'], false);exit;

        $info = array(
            'amount' => round($order['total'],2),
            'currency' => 'BDT',
            'merchantInvoiceNumber' => $order['order_id'],
            'intent' => 'sale'
        );
        
        

        $username = $this->config->get('payment_bkash_username');
        $password = $this->config->get('payment_bkash_password');
        $app_key = $this->config->get('payment_bkash_app_key');
        $app_secret = $this->config->get('payment_bkash_app_secret');
        $createURL = $this->config->get('payment_bkash_createCheckoutUrl');

        $token = $this->getTokenFrombKash();
        //print_r($token);exit;
        $header = array(
            'Content-Type:application/json',
            'authorization:' . $token,
            'x-app-key:' . $app_key
        );

        $postReq = $this->sendPOST($createURL, $info, $header, "API Title: Create Payment \n");
        $createInfo = json_decode($postReq);
        //print_r($createInfo);exit;
        if ($createInfo) {
            $createInfo->paymentID;
            if (isset($createInfo->paymentID)) {
                
                //$this->queryPayment($createInfo->paymentID);
                
                echo json_encode(array(
                    'result' => 'success',
                    'redirect' => false,
                    'data' => $createInfo,
                    'order_id' => $order_id
                ));
            } else {
                // Cannot parse token from response
                echo json_encode([
                    'status' => 'fail',
                    'data' => '',
                    'result' => 'failure',
                    'messages' => '<ul class="error""><li>Unable to create payment, try again</li></ul>',
                    'refresh' => true,
                    'reload' => false
                ]);
            }
        } else {
            // Cannot get token or issue here
            echo json_encode([
                'status' => 'fail',
                'data' => '',
                'result' => 'failure',
                'messages' => '<ul class="error""><li>Unable to create payment due to session, try again</li></ul>',
                'refresh' => true,
                'reload' => false
            ]);
        }
    }

    public function executePayment() {
        $order_id = $this->session->data['order_id'];
        $this->load->model('checkout/order'); // call this only if this model is not yet instantiated!
        $order = $this->model_checkout_order->getOrder($order_id);
        
        $username = $this->config->get('payment_bkash_username');
        $password = $this->config->get('payment_bkash_password');
        $app_key = $this->config->get('payment_bkash_app_key');
        $app_secret = $this->config->get('payment_bkash_app_secret');
        $executeURL = $this->config->get('payment_bkash_executeCheckoutUrl');
        

        $token = $this->getTokenFrombKash();
        //print_r($_REQUEST);exit;
       $paymentID = $_REQUEST['paymentID'];
        //$order_id = $_REQUEST['order_id'];
        if ($paymentID && $order) {

            $header = array(
                'Content-Type:application/json',
                'authorization:' . $token,
                'x-app-key:' . $app_key
            );
            $postReq = $this->sendPOST($executeURL.$paymentID, [], $header, "API Title: Execute Payment \n");
            $tokenInfo = json_decode($postReq);
            //print_r($tokenInfo);exit;
            if ($tokenInfo) {
                if (isset($tokenInfo->transactionStatus) && $tokenInfo->transactionStatus == 'Completed') {

                    $trxid = isset($tokenInfo->trxID) ? $tokenInfo->trxID : '';
//                    $this->queryPayment($tokenInfo->paymentID);
//                    $this->searchPayment($trxid);
//                    $info = array(
//                    'amount' =>10,
//                    'paymentID' => $tokenInfo->paymentID,
//                    'trxID' => $trxid,
//                    'sku' => 'ABC',
//                    'reason' => 'TEST'
//                    );
//                    $executeURL = str_replace('execute', 'refund', $this->config->get('bkash_executeCheckoutUrl'));
//                    $postReq = $this->sendPOST($executeURL, $info, $header, "API Title: Refund \n");
//                    
                    $infopayment = array(
                    'paymentID' => 'paymentID:'.$tokenInfo->paymentID,
                    'trxID' => 'trxID:'.$trxid
                    );
//                    $executeURL = str_replace('execute', 'refund', $this->config->get('bkash_executeCheckoutUrl'));
//                    $postReq = $this->sendPOST($executeURL, $info, $header, "API Title: Refund  Status\n");
                    // Redirect to the thank you page
                    //$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('bkash_order_status_id'),json_encode($tokenInfo), true);
                    $link=$this->url->link('extension/payment/bkash/confirm', '', true);
                    //'index.php?route=checkout/success'
                    echo json_encode(array(
                        'result' => 'success',
                        'redirect' => 'index.php?route=checkout/success',
                    ));
                 $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('bkash_order_status_id'),http_build_query($tokenInfo,'',', '), true);
                } else {
                    echo json_encode([
                        'status' => 'fail',
                        'data' => '',
                        'result' => 'failure',
                        'messages' => '<ul class="woocommerce-error""><li>Payment not completed, try again</li></ul>',
                        'refresh' => true,
                        'reload' => false
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'data' => '',
                    'result' => 'failure',
                    'messages' => '<ul class="woocommerce-error""><li>No response, try again</li></ul>',
                    'refresh' => true,
                    'reload' => false
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'fail',
                'data' => '',
                'result' => 'failure',
                'messages' => '<ul class="woocommerce-error""><li>Payment or order id missing, try again</li></ul>',
                'refresh' => true,
                'reload' => false
            ]);
        }
    }

    protected function getTokenFrombKash($getToken = false) {
        $username = $this->config->get('payment_bkash_username');
        $password = $this->config->get('payment_bkash_password');
        $app_key = $this->config->get('payment_bkash_app_key');
        $app_secret = $this->config->get('payment_bkash_app_secret');
        $tokenURL = $this->config->get('payment_bkash_grantTokenUrl');

        $header = array(
            'Content-Type:application/json',
            'password:' . $password,
            'username:' . $username
        );
        $info = array(
            'app_key' => $app_key,
            'app_secret' => $app_secret
        );
        $postReq = $this->sendPOST($tokenURL, $info, $header, "API Title: Grant Token \n");
        $tokenInfo = json_decode($postReq);
        // print_r($tokenInfo);
        if ($tokenInfo) {
            if (isset($tokenInfo->id_token)) {
                // Got token
                $data = array(
                    'token' => $tokenInfo->id_token,
                    'expires' => time() + $tokenInfo->expires_in
                );

                return $tokenInfo->id_token;
            } else {
                // Cannot parse token from response
                return null;
            }
        } else {
            // Cannot get token or issue here
            return null;
        }
        return null;
    }

    public function sendPOST($post_url, $info = [], $header = [], $api_title = '', $proxy = '') {
        $log = $api_title;
        
        
        $url = curl_init($post_url);

        $createpaybodyx = json_encode($info);

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $createpaybodyx);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt($url, CURLOPT_PROXY, $proxy);
        // curl_setopt($url, CURLOPT_VERBOSE, true);
        //$verbose = fopen('php://temp', 'w+');
        //curl_setopt($url, CURLOPT_STDERR, $verbose);

        $resultdata = curl_exec($url);

        $log .= "API URL: $post_url \n";
        $log .= "Request Body : \n";
        $log .= "\t headers: \n";
        $log .= "\t\t " . json_encode($header) . " \n";
        $log .= "\t body params: \n";
        $log .= "\t\t " . json_encode($info) . " \n";
        $log .= "API Response: \n";
        $log .= "\t " . json_encode($resultdata) . " \n";
        $logwrite = new Log('bkash.log');
	$logwrite->write('API Log Of Bkash = ' . $log);


        if ($resultdata === FALSE) {
            // printf("cUrl error (#%d): %s<br>\n", curl_errno($url),
            // htmlspecialchars(curl_error($url)));
        }

        
        curl_close($url);
        // var_dump($resultdata);
        return $resultdata;
    }

    public function sendGET($post_url, $header = [], $api_title, $proxy = '') {
        $log = $api_title;
        // var_dump($header);
        // var_dump($info);
        $url = curl_init($post_url);

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt($url, CURLOPT_PROXY, $proxy);

        

        $resultdata = curl_exec($url);

        $log .= "API URL: @$post_url \n";
        $log .= "Request Body : \n";
        $log .= "\t headers: \n";
        $log .= "\t\t " . json_encode(@$header) . " \n";
        $log .= "API Response: \n";
        $log .= "\t " . json_encode(@$resultdata) . " \n";
        $logwrite = new Log('bkash.log');
	$logwrite->write('API Log Of Bkash = ' .$log);


        if ($resultdata === FALSE) {
            // printf("cUrl error (#%d): %s<br>\n", curl_errno($url),
            // htmlspecialchars(curl_error($url)));
        }

        

        // echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
        curl_close($url);
        // var_dump($resultdata);
        return $resultdata;
    }

    public function queryPayment($paymentID) {

        $username = $this->config->get('payment_bkash_username');
        $password = $this->config->get('payment_bkash_password');
        $app_key = $this->config->get('payment_bkash_app_key');
        $app_secret = $this->config->get('payment_bkash_app_secret');
        $executeURL = str_replace('execute', 'query', $this->config->get('payment_bkash_executeCheckoutUrl'));
                //'https://checkout.sandbox.bka.sh/v1.2.0-beta/checkout/payment/query/';

        // we need it to get any order detailes "transactionStatus":""transactionStatus":"Completed",",Initiated
        $token = $this->getTokenFrombKash();

        if ($paymentID) {

            $header = array(
                'Content-Type:application/json',
                'authorization:' . $token,
                'x-app-key:' . $app_key
            );
            $postReq = $this->sendGET($executeURL. $paymentID, $header, "API Title: Query Payment \n");
//            $searchInfo = json_decode($postReq);
//           // print_r($searchInfo);exit;
//            if ($searchInfo) {
//                if (isset($searchInfo->transactionStatus) && $searchInfo->transactionStatus == 'Completed') {
//
//                    // Redirect to the thank you page
//                    return json_encode(array(
//                        'result' => 'success',
//                        'data' => $searchInfo
//                    ));
//                } else {
//                    return json_encode([
//                        'status' => 'fail',
//                        'data' => '',
//                        'result' => 'failure',
//                        'messages' => '<ul class="error""><li>Payment not completed, try again</li></ul>',
//                        'refresh' => true,
//                        'reload' => false
//                    ]);
//                }
//            } else {
//                return json_encode([
//                    'status' => 'fail',
//                    'data' => '',
//                    'result' => 'failure',
//                    'messages' => '<ul class="error""><li>No response, try again</li></ul>',
//                    'refresh' => true,
//                    'reload' => false
//                ]);
//            }
        } else {
            return json_encode([
                'status' => 'fail',
                'data' => '',
                'result' => 'failure',
                'messages' => '<ul class="error""><li>Payment or order id missing, try again</li></ul>',
                'refresh' => true,
                'reload' => false
            ]);
        }
    }
    
    
    
    public function searchPayment($trxID) {

        $username = $this->config->get('payment_bkash_username');
        $password = $this->config->get('payment_bkash_password');
        $app_key = $this->config->get('payment_bkash_app_key');
        $app_secret = $this->config->get('payment_bkash_app_secret');
        $executeURL = str_replace('execute', 'search', $this->config->get('payment_bkash_executeCheckoutUrl'));
                //'https://checkout.sandbox.bka.sh/v1.2.0-beta/checkout/payment/query/';

        // we need it to get any order detailes "transactionStatus":""transactionStatus":"Completed",",Initiated
        $token = $this->getTokenFrombKash();

        if ($trxID) {

            $header = array(
                'Content-Type:application/json',
                'authorization:' . $token,
                'x-app-key:' . $app_key
            );
            $postReq = $this->sendGET($executeURL. $trxID, $header, "API Title: Search Transaction \n");
//            $searchInfo = json_decode($postReq);
//           // print_r($searchInfo);exit;
//            if ($searchInfo) {
//                if (isset($searchInfo->transactionStatus) && $searchInfo->transactionStatus == 'Completed') {
//
//                    // Redirect to the thank you page
//                    return json_encode(array(
//                        'result' => 'success',
//                        'data' => $searchInfo
//                    ));
//                } else {
//                    return json_encode([
//                        'status' => 'fail',
//                        'data' => '',
//                        'result' => 'failure',
//                        'messages' => '<ul class="error""><li>Payment not completed, try again</li></ul>',
//                        'refresh' => true,
//                        'reload' => false
//                    ]);
//                }
//            } else {
//                return json_encode([
//                    'status' => 'fail',
//                    'data' => '',
//                    'result' => 'failure',
//                    'messages' => '<ul class="error""><li>No response, try again</li></ul>',
//                    'refresh' => true,
//                    'reload' => false
//                ]);
//            }
        } else {
            return json_encode([
                'status' => 'fail',
                'data' => '',
                'result' => 'failure',
                'messages' => '<ul class="error""><li>Payment or order id missing, try again</li></ul>',
                'refresh' => true,
                'reload' => false
            ]);
        }
    }

    public function confirm() {

        if ($this->session->data['payment_method']['code'] == 'bkash') {

            $this->load->language('payment/bkash');

            $this->load->model('checkout/order');

            $comment = $this->language->get('text_payment');
            //Print_r($this->config->get('bkash_order_status_id'));
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('bkash_order_status_id'));
            //'index.php?route=checkout/success'
            //$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('bkash_order_status_id'), $comment, true);
        }
    }

}

?>
