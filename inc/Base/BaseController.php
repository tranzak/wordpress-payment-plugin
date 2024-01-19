<?php
/*
*@package  Tranzak_Payment_Gateway
*/

namespace Inc\Base;

class BaseController{
  public $pluginPath;
  public $transactionStatuses;
  public $pluginUrl;
  public $plugin;
  public $mapiUrl;
  public $returnUrl;
  public $genTokenUrl;
  public $env;
  public $api;
  public $dSApi;
  public $wpTransactionsTable;
  public $paymentTemplateSlug;
  public $paymentTemplateTitle;
  public $paymentTemplateName;
  public $pluginPageUrl;
  public $pluginTokenKey;
  public $pluginTitle;
  public $wpDonationsTable;
  public $settingsPageTemplate;
  public $pluginOptions;
  public $siteUrl;
  public $transSlug;
  public $getRequestUrl;
  public $dSApiCode;
  public $mpCode;
  public $websiteLogs;
  public $currencies;
  public $mapiMobileWalletUrl;
  public $transactionShortCode;
  public $donationShortCode;
  public $donationProgressShortCode;
  public function __construct(){
    /**
     * this would move 2 levels back from the current directory;
     *
     **/

    $this->transactionStatuses = ['successful' => 2, 'pending' => 1, 'failed' => -1];
    $this->websiteLogs = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ) );

    $this->transactionShortCode = 'tranzak_payment_verification';
    $this->donationShortCode = 'tz_pg_donations';
    $this->donationProgressShortCode = 'tz_pg_progress';

    $this->currencies = array("AUD","CAD","CNY","EUR","GBP","GHS","JPY","KES","NGN","RWF","SAR","TZS","USD","VND","XAF","XOF","ZAR","ZMW");

    $this->wpTransactionsTable = "tranzak_pg_transactions";
    $this->wpDonationsTable = "tranzak_pg_donations";
    $this->paymentTemplateSlug = "tranzak-payment-gateway";
    $this->paymentTemplateName = "tz-payment-verification";
    $this->pluginTitle = 'Payment Verification';
    $this->transSlug = 'tz-mtn-momo-woo-gateway';
    $this->paymentTemplateTitle = 'Payment Verification';
    $this->siteUrl = get_site_url();
    $this->env = 'production';
    $this->dSApiCode = '/xp021';
    $this->mpCode = '/aa038';

    $this->returnUrl = get_site_url() . "/tz-payment-verification";

    $this->api = "https://api.tranzak.me";
    $this->dSApi = "https://dsapi.tranzak.me";

    $this->pluginPath = plugin_dir_path( dirname(__FILE__, 2) );
    $this->pluginUrl = plugin_dir_url( dirname(__FILE__, 2) );
    $this->plugin = plugin_basename( dirname(__FILE__, 3) ).'/tranzak-payment-gateway.php';
    $this->pluginPageUrl = 'tranzak_payment_gateway';
    $this->pluginTokenKey = 'tranzak_payment_gateway_token';

    $this->pluginOptions = get_option($this->pluginPageUrl);

    if($this->pluginOptions && isset($this->pluginOptions['env']) && $this->pluginOptions['env'] == 'sandbox'){
      $this->api = "https://sandbox.api.tranzak.me";
      $this->dSApi = "https://sandbox.dsapi.tranzak.me";
      $this->env = 'sandbox';
    }


    $this->genTokenUrl = "$this->dSApi/auth/token";
    $this->getRequestUrl = "$this->dSApi/xp021/v1/request/details?requestId=";
    $this->mapiUrl = "$this->dSApi/xp021/v1/request/create";
    $this->mapiMobileWalletUrl = "$this->dSApi/xp021/v1/request/create-mobile-wallet-charge";

    if($this->pluginOptions && isset($this->pluginOptions['app_id']) && isset($this->pluginOptions['api_key'])  && isset($this->pluginOptions['auth_key'])){
      $this->settingsPageTemplate = "
        <p style=\"color: #00aa00;\">General settings OK!!!</p>
      ";
    }else{
      $this->settingsPageTemplate = "
        <p>
          <p style=\"color: #aa0000;\">Before enabling this payment method for woocommerce, make sure your have configured everything in the Tranzak settings tab, else payments would not go through. This message would disappear once all required fields are entered </p><br>
          <p>
          Click <a href=\"$this->siteUrl/wp-admin/admin.php?page=$this->pluginPageUrl\"> here</a> to configure the Tranzak payment gateway plugin
          </p>
        </p>
      ";
    }

  }

  public function formatCurrency($amount, $currency = 'XAF'){
    setlocale(LC_MONETARY,"en_US");
    return money_format("The price is %i", $number);
  }

  public function failedResponse($msg, $extra = array()){
    return array_merge(array(
      'success' => false,
      'errorMsg' => $msg,
      'data' => []
    ), $extra);
  }
  public function successResponse($data, $extra = array()){
    return array_merge(array(
      'success' => true,
      'errorMsg' => '',
      'data' => $data
    ), $extra);
  }
}

// define('PLUGIN_PATH', plugin_dir_path( __FILE__ ));
// define('PLUGIN_URL', plugin_dir_url( __FILE__ ));
// define('PLUGIN', plugin_basename( __FILE__ ));
// define('PLUGIN_PAGE_URL', 'tranzak_payment_gateway');