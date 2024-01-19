<?php 
/*
*@package  Tranzak_Payment_Gateway
*/

namespace Inc\Base;

class Templates extends BaseController{
  public function __construct(){
    parent::__construct();
    $this->failedImage = $this->pluginUrl.'assets/img/danger.jpg';
    $this->successImage = $this->pluginUrl.'assets/img/successful.jpg';
    $this->warningImage = $this->pluginUrl.'assets/img/warning.jpg';
  }

  public $failedImage;
  public $successImage;
  public $warningImage;
  public $tranzak_templates = array();

  public function addTemplate($pageTemplates, $theme, $post){
    foreach($this->tranzak_templates as  $key => $value){
      $pageTemplates[$key] = $value;
    }
    
    return $pageTemplates;
  }


  public function register(){
    $this->tranzak_templates[$this->paymentTemplateSlug] = 'Tranzak Payment Gateway';

    add_shortcode( $this->transactionShortCode,  array($this,'myShortCode'));
    // add_shortcode( 'tranzak_payment_verification',  array($this,'myShortCode'));

    /**
     * registering default page for transaction acknowledgement
     */
    // $this->createDefaultPage();
  }

  function myShortCode(){

    $style = "
    </div>
    <style>
      #TPG-container{
        color: #222;
        margin: auto;
        margin-bottom: 20px;
        margin-top: 20px;
        width: 100%;
        padding: 30px;
        border-radius: 5px;
        border: 1px solid #dedede;
        max-width: 600px;
        background: #fff;
        text-align: center !important;
      }
      #TPG-container img{
        display: inline;
      }
    
      #TPG-container .tz-order-detail-button{
        background-color: #222;
        border-radius: 5px;
        padding: 8px;
        cursor: pointer;
        color: #fff;
        transition: all 0.2s ease-in-out;
      }
      #TPG-container .tz-order-detail-button:hover{
        background-color: transparent;
        color: #000;
      }
      
    </style>
    ";

    $content = '<div id="TPG-container" class="text-center">';
    $txn = [];
    $modes = [
      'invalid' => 1,
      'failed' => 2,
      'successful' => 3,
      'unsuccessful' => 4,
    ];

    $mode = 1;
    $wpTransaction = false;

    $transactionId = $_GET['transactionId'] ?? '';
    $requestId = $_GET['requestId'] ?? '';
    $template = new Templates();
    if ($transactionId || $requestId) {

      $transaction = new Transactions();

      if(!$transactionId){
        $txn = $transaction->getMapiRequest($requestId);
        error_log(json_encode($txn));
      }else{
        $txn = $transaction->getMapiTransaction($transactionId);
      }


      $wpTransactionId = explode(' ',$txn['mchTransactionRef'])[0];

      if ($txn && $txn['status'] == 'SUCCESSFUL') {

        
        $wpTransaction = $transaction->getTransaction($wpTransactionId);
        
        /**
         * check if wp transaction is still in pending status so it can be updated to paid status
         */

        if ($wpTransaction) {
          if ($wpTransaction->status != 2) {
            $updatedTransaction = $transaction->updateTransaction($wpTransactionId, 2, $txn['transactionId'], $txn['amount'], $txn['currencyCode']);
            if ($updatedTransaction) {
              $mode = $modes['successful'];
              $wpTransaction = $updatedTransaction;
            }
          }else{
            $mode = $modes['successful'];
          }

          if ($wpTransaction->origin == 1) {
            $order = $transaction->updateOrder($wpTransaction->reference);
          }
        }

        // $order = $transaction->updateOrder(1);

      } else  if($txn && $txn['status'] == 'FAILED'){
        $wpTransaction = $transaction->getTransaction($wpTransactionId);

        if ($wpTransaction->status == 1) {
          $wpTransaction = $transaction->updateTransaction($wpTransactionId, -1, $txn['transactionId'], $txn['amount'], $txn['currencyCode']);
        }
        $mode = $modes['unsuccessful'];
      }else{
        $mode = $modes['failed'];
      }
    }

    if ($mode == $modes['failed']) {
      $message = 'Sorry we were unable to load your transaction information. Please try reloading this page';
      if($txn && $txn['status'] == 'PENDING' && $txn['requestId']){
        $rid = $txn['requestId'];
        $message = 'Sorry, your transaction has not been paid yet. Click <a href="https://pay.tranzak.me?rid='.$rid.'">here</a> to proceed';
      }
      $content .= '
      <img src="'.$template->warningImage.'" alt="">
      <h3>
        Error
      </h3>
      <div>'.$message.'.</div>
      <p>
        <span class="button" onclick="location.reload()">Reload</span>
      </p>
      ';
    }else if($mode == $modes['successful']){
      $content .= '
        <img src="'.$template->successImage.'" alt="">
        <h3>
          Payment successful
        </h3>
        <div>You payment of <h3 style="display: inline; font-weight: bolder"><stong>'.number_format($txn['amount'], gettype($txn['amount']) == 'integer' ? 0 : 2).'</stong></h3><small>'.$txn['currencyCode'].'</small> was successful.</div>
        <div style="margin-top: 10px;">
      ';
      if($wpTransaction && $wpTransaction->origin == 1){
        $content.= '
        <a href="'.$wpTransaction->url.'">
          <button class="button">
            See order detail
          </button>
        </a>
        ';
      }
      if($wpTransaction && $wpTransaction->origin == 2){
        $model = new Donation();
        $donation = $model->getDonation($wpTransaction->reference);
        if($donation){
          $content.= '
          <h4 style="color: #000; font-weight: bold">'.$donation->success_message.'</h4>
          ';
        }
      }
    }else if($mode == $modes['unsuccessful']){
      $content .= '
        <img src="'.$template->failedImage.'" alt="">
        <h3>
          Payment unsuccessful
        </h3>
        <div>You payment of <h3 style="display: inline; font-weight: bolder"><stong>'.$txn['amount'].'</stong></h3><small>'.$txn['currencyCode'].'</small> was unsuccessful.</div>
      ';
    }else{
      $content .= '
      <img src="'.$template->warningImage.'" alt="">
      <h3>
        Error
      </h3>
      <div>Sorry we could not load your payment information because invalid parameters were provided.</div>
        ';
    }


    return $content.$style;
  }
  
  
  
}