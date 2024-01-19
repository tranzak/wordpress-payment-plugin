<?php 
/*
*@package  Tranzak_Payment_Gateway
*/

namespace Inc\Base;

use Inc\Base\Donation;

class DonationsTemplate extends BaseController{
  public function __construct(){
    parent::__construct();
  }

  public $model;
  public $tranzak_templates = array();

  public function addTemplate($pageTemplates, $theme, $post){
    foreach($this->tranzak_templates as  $key => $value){
      $pageTemplates[$key] = $value;
    }
    
    return $pageTemplates;
  }

  public function register(){
    $this->tranzak_templates[$this->paymentTemplateSlug] = 'Tranzak Payment Gateway';
    

    add_shortcode( $this->donationShortCode,  array($this,'myShortCode'));
    add_shortcode( $this->donationProgressShortCode,  array($this,'progressBarShortCode'));
    // add_shortcode( 'tranzak_payment_verification',  array($this,'myShortCode'));

    /**
     * registering default page for transaction acknowledgement
     */
    // $this->createDefaultPage();
  }

  function progressBarShortCode($params){


    if(!isset($params['id']) || !$params['id']){
      return 'ID NOT PROVIDED';
    }

    $model = new Donation();
    $donation = $model->getDonation($params['id']);

    if(!$donation){
      return;
    }

    $target = $donation->target;
    if($target <= 0){
      return;
    }


   
    wp_enqueue_script('tz_payment_gateway_donations_progress_script', $this->pluginUrl . 'assets/script/donations-progress.js', array('jquery'));

    wp_localize_script( 'tz_payment_gateway_donations_progress_script', 'tzProgress',
      array( 
        'ajaxUrl' => admin_url( 'admin-ajax.php'),
        'siteUrl' => get_site_url(),
        'pluginUrl' => $this->pluginUrl
      ) 
    );


    
    wp_enqueue_style('tz_payment_gateway_style', $this->pluginUrl . 'assets/style/checkout-style.css');



    $content = '<div style="width: 100%; padding: 2px" class="tz_pg_progress_bar_container" data-id="'.$params['id'].'"> </div>';



    return $content;
  }
  function myShortCode($params){


    if(!isset($params['id']) || !$params['id']){
      return 'ID NOT PROVIDED';
    }

    $model = new Donation();
    $donation = $model->getDonation($params['id']);

    if(!$donation){
      return '';
    }

    wp_enqueue_script('tz_payment_gateway_donations_script', $this->pluginUrl . 'assets/script/donations-page-payment.js', array('jquery'));
    /**
     * Adding global object for javascript
     */
    wp_localize_script( 'tz_payment_gateway_donations_script', 'tzDonation'.$donation->ID,
      array( 
        'ajaxUrl' => admin_url( 'admin-ajax.php'),
        'siteUrl' => get_site_url(),
        'pluginUrl' => $this->pluginUrl,
        'code' => '*126#',
        'title' => $donation->title,
        'amount' => $donation->amount,
        'currency' => $donation->currency,
        // 'userDefined' => $donation->amount == 0? true: false,
        'userId' => get_current_user_id(),
        'errorKey' => 'tz-donation-errors',
        'key' => 'tz-donation',
        'defaultLogo' => $this->websiteLogs && count($this->websiteLogs) >0? $this->websiteLogs[0]: null
      ) 
    );
    wp_enqueue_style('tz_payment_gateway_style', $this->pluginUrl . 'assets/style/checkout-style.css');



    $content = '<div> <button class="button" onclick="tzTriggerDonation('.$donation->ID.')">'.$donation->button_text.'</button></div>';



    return $content;
  }
  
  
  
}