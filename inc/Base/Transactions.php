<?php
/*
*@package  Tranzak_Payment_Gateway
*/

namespace Inc\Base;

use Inc\Base\BaseController;
use Inc\Base\Encryption;

use WC_Order;


class Transactions extends BaseController{

  public $options;
  public $tokenOptions;
  public $encryption;
  public $headers;
  public function __construct(){
    global $wpdb;
    parent::__construct();

    $this->encryption = new Encryption();

    $this->headers = [
      'headers' => [
        'Content-Type' => 'application/json',
        'X-App-Env' => $this->env,
      ]
    ];

    $this->options = get_option($this->pluginPageUrl);

    $this->tokenOptions = get_option($this->pluginTokenKey);
  }

  public function updateOrderStatus(string $status, $order){

  }

  public function getOrder($orderId){

  }

  /**
   * Summary of createTransaction
   * @param float $amount
   * @param string $currency
   * @param string $description
   * @param int $origin
   * @param string $reference
   * @param string $url
   * @return mixed
   */
  public function createTransaction($amount, $currency, $description, $origin = 1, $reference = '', $url = '' ){
    if(!isset($this->options['app_id'])){
      return false;
    }

    if(!isset($this->options['api_key'])){
      return false;
    }

    global $wpdb;
    $table = $wpdb->prefix.$this->wpTransactionsTable;
    $data = array('origin' => $origin, 'status' => 1, 'currency' => $currency, 'amount' => $amount, 'reference' => $reference, 'description' => $description, 'url' => $url);
    $format = array('%d','%d', '%s', '%f', '%s', '%s', '%s');
    $result = $wpdb->insert($table,$data,$format);
    if($result){

      return $wpdb->insert_id;
    }
    return false;
  }

  /**
   * Used to make HTTP requests to remote servers
   * @param string $url
   * @param boolean $checkToken is used to enforce token validity before making a request. 
   */
  public function makeRequest(String $url, $data = [], $type = 'POST', $checkToken = true){

    try{
      if(!isset($this->options['api_key']) || !isset($this->options['app_id'])){
        return ["success" => false, 'errorMsg' => "Enter both App ID & API Key on your Tranzak payment gateway configuration page."];
      }

      if($checkToken){
        $tokenData = $this->getToken();

        if(!$tokenData){
          return ["success" => false, 'errorMsg' => "Please check your configuration for Tranzak Payment Gateway and ensure you entered the right keys for the appropriate environment"];
        }
        $this->headers['headers']['Authorization'] = 'Bearer '. $tokenData['token'];
      }



      $this->headers['headers']['X-App-Key'] = $this->options['api_key'];
      $options = array_merge([
        'timeout' => 120,
        'body' => json_encode($data),
      ], $this->headers);


      $response = wp_remote_post( $url, $options );

      if($response && !is_wp_error($response)){
        $body = json_decode($response['body'], true);

        if($body && isset($body['success']) && $body['success'] == true){
          return ["success" => true, "data" => $body['data']];
        }else if($body && isset($body['errorCode']) && ($body['errorCode'] == 40001 || $body['errorCode'] == 40002 || $body['errorCode'] == 40003)){
          do_action('reset_tranzak_token');
        }
        if($body && isset($body['errorMsg'])){
          return ["success" => false, "errorMsg" => $body['errorMsg']];
        }
      }

    }catch(\Exception $e){
      // return ["success" => false];
    }
    return ["success" => false];
  }

  public function updateTransaction($transactionId, $status = 2, $externalTxnId = '', $amount = '', $currency = ''){
    global $wpdb;
    $externalTxnId = $externalTxnId ?? '';
    $filter = array('ID' => $transactionId);
    if($amount){
      $filter['amount'] = $amount;
    }
    if($currency){
      $filter['currency'] = $currency;
    }
    $transaction = $wpdb->update($wpdb->prefix.$this->wpTransactionsTable, array('external_transaction_id' => $externalTxnId,  'status' => $status), $filter);
    if($transaction){
      return $this->getTransaction($transactionId);
    }
    return false;
  }

  public function updateOrder($orderId){
    if(class_exists('WC_Order')){

      $order = new WC_Order($orderId);

      if (!empty($order)) {
        if($order->get_status() == 'pending'){
          $updatedOrder = $order->update_status("wc-completed", 'Completed', TRUE);
          if(!$updatedOrder){
            return false;
          }
        }
        return true;
      }

      return false;
    }
    return ['notActive' => 1];
  }

  public function createToken(){


    if(!$this->checkParams()){
      return false;
    }

    $this->headers['headers']['X-App-ID'] = $this->options['app_id'];
    // $this->headers['headers']['X-App-Env'] = $this->env;
    $options = array_merge([
      'timeout' => 120,
      'body' => json_encode(array(
        'appId' => $this->options['app_id'],
        'appKey' => $this->options['api_key'],
      ))
    ], $this->headers);

    $response = wp_remote_post( $this->genTokenUrl, $options );
    if(!is_wp_error($response)){
      $body = json_decode($response['body'], true);
      if($body && isset($body['success'])){
        return $body['data'];
      }
    }

    return false;
  }

  protected function encryptToken($token){
    return $this->encryption->encrypt($token);
  }
  protected function decryptToken($token){
    return $this->encryption->decrypt($token);
  }

  private function checkParams(){
    if(!isset($this->options['api_key']) || isset($this->options['api_key']) && strlen($this->options['api_key']) < 5 ||   !isset($this->options['app_id']) || strlen($this->options['app_id']) < 5){
      return false;
    }
    return true;
  }

  public function setToken($token, $expiresIn){
    $now = new \DateTime();
    $expiresIn = $now->format('U') + (($expiresIn / 10) * 7.5);

    $data = array(
      'token' => $this->encryptToken($token),
      'expiresIn' => $expiresIn
    );

    update_option('tranzak_payment_gateway_token', $data);

    return $data;
  }
  public function getToken(){
    if(!$this->checkParams()){
      return false;
    }

    $token = null;
    $expiresIn = null;

    $tokenOption = get_option('tranzak_payment_gateway_token');

    if($tokenOption && isset($tokenOption['token']) && isset($tokenOption['expiresIn'])){


      $token = $tokenOption['token'];
      $expiresIn = $tokenOption['expiresIn'];
      $date = new \DateTime();
      // $date->setTimestamp($expiresIn);
      $now = new \DateTime();

      $expired = ($expiresIn - $now->format('U')) <= 0 ? true: false;

      if($expired){
        $token = null;
        $expiresIn = null;
      }
    }

    if($token && $expiresIn){
      return array(
        'token' => $this->decryptToken($token),
        'expiresIn' => $expiresIn
      );
    }else{
      $data = $this->createToken();
      if($data && isset($data['token'])){
        $token = $data['token'];
        $expiresIn = $data['expiresIn'];

        $savedToken = $this->setToken($token, $expiresIn);

        return array(
          'token' =>  $this->decryptToken($savedToken['token']),
          'expiresIn' => $savedToken['expiresIn']
        );
      }
    }


    return false;
  }

  public function getTransaction($transactionId){
    global $wpdb;

    $result = $wpdb->get_row("SELECT * FROM $wpdb->prefix$this->wpTransactionsTable WHERE ID = $transactionId");
    if($result){
      return $result;
    }

    return false;
  }
  public function getTransactionsByOrigin($origin, $reference, $status){
    global $wpdb;
    $sql = "SELECT * FROM $wpdb->prefix$this->wpTransactionsTable WHERE origin = $origin AND reference = $reference";
    if(!empty($status)){
      $sql = "SELECT * FROM $wpdb->prefix$this->wpTransactionsTable WHERE origin = $origin AND reference = $reference AND status = $status";
    }
    $result = $wpdb->get_results($sql);
    if($result){
      return $result;
    }
    return false;
  }
  public function getMapiTransaction($transactionId){

    if(!isset($this->options['api_key']) || !isset($this->options['app_id'])){
      return false;
    }

    $tokenData = $this->getToken();

    if(!$tokenData){
      return false;
    }

    $this->headers['headers']['X-App-Key'] = $this->options['api_key'];
    $this->headers['headers']['Authorization'] = 'Bearer '. $tokenData['token'];


    $request = $this->makeRequest($this->dSApi . $this->dSApiCode . '/v1/transaction/verify?transactionId=' . $transactionId);
    if($request['success'] == true){
      return $request['data'];
    }
    return false;

  }
  public function registerPluginData($appId){

    $data = [
      'appId' => $appId,
      'url' =>  get_site_url()
    ];

    $request =  $this->makeRequest($this->api . $this->mpCode. '/v1/wordpress-app', $data, 'POST', false);
    if($request['success'] == true){
      return $request['data'];
    }
    return false;

  }
  public function getMapiRequest($requestId){

    $request =  $this->makeRequest($this->getRequestUrl . $requestId);
    if($request['success'] == true){
      return $request['data'];
    }
    return false;

  }


  private function tokenValid($tokenOption){

    if($tokenOption && isset($tokenOption['token']) && isset($tokenOption['expiresIn'])){
      $expiresIn = $tokenOption['expiresIn'];
      $date = new \DateTime($expiresIn);
      $now = new \DateTime();
      return ($date->format('U') - $now->format('U')) <= 0 ? false: true;
    }
    return false;
  }


  /**
   * Summary of createMapiTransaction
   * @param float $amount amount to be paid
   * @param string $currency
   * @param string $description
   * @param string $reference
   * @return mixed
   */
  public function createMapiTransaction(float $amount, string $currency, string $description, string $reference = '', $url = '', $extraParams = array(), $setPaymentExpirationTime = true ){
    $url = $url ?$url: $this->mapiUrl;

    if(!$this->options && !$this->options['return_url']){
      $activation = new Activation();
      $activation->createDefaultPage();
    }

    $salt = mt_rand(1, 100);

    $data = array_merge([
      'currencyCode' => $currency,
      'amount' => $amount,
      'hostAppId' => $this->options['app_id'],
      'description' => $description,
      'mchTransactionRef' => $reference.' '.$salt,
      'returnUrl' => $this->options['return_url'] ?? $this->returnUrl
    ], $extraParams);

    if($setPaymentExpirationTime){
      $data = array_merge(
        $data,
        [
          'expiresAfterSeconds' => 3300
        ]
      );
    }

    $request =  $this->makeRequest($url, $data);

    if($request['success'] == true){
      return ["success" => true, "data" => $request['data']];
    }
    return ["success" => false, "errorMsg" => $request['errorMsg']];
  }

}