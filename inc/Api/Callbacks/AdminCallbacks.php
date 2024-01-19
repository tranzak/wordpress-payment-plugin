<?php
/*
*@package  Tranzak_Payment_Gateway
*/

namespace Inc\Api\Callbacks;
use Inc\Base\BaseController;

class AdminCallbacks extends BaseController{
  public function adminSettingsPage(){
    return require_once($this->pluginPath.'templates/AdminSettings.php');
  }
  public function adminDonations(){
    return require_once($this->pluginPath.'templates/AdminDonations.php');
  }
  public function adminTransactions(){
    return require_once($this->pluginPath.'templates/AdminTransactions.php');
  }
  public function AdminAddDonation(){
    return require_once($this->pluginPath.'templates/AdminAddDonation.php');
  }

  public function tzOptionsGroup($input){
    var_dump($input);
    return $input;
  }

  public function tzAdminSection(){
    echo "";
  }

  public function appIdField($args){
    $optionName = $args['option_name'];
    $tzPaymentGateway =  get_option($optionName);
    $appId = $tzPaymentGateway ? $tzPaymentGateway['app_id'] ?? '': '';
    echo '<input type="text" name="'. $optionName . '[app_id]" id="tz_app_id" class="regular-text" required value="'.$appId.'" placeholder="Enter App ID for this app (required)" />
    </br><label for="tz_app_id" class="tz-admin-danger">Required</label><br>
    ';
  }
  public function apiKey($args){
    $optionName = $args['option_name'];
    $tzPaymentGateway =  get_option($optionName);
    $apiKey = $tzPaymentGateway ? $tzPaymentGateway['api_key'] ?? '': '';
    echo '<input type="text" name="' . $optionName . '[api_key]" id="tz_api_key" class="regular-text" required value="' . $apiKey . '" placeholder="Enter API Key for this app (required)" />
    </br><label for="tz_api_key" class="tz-admin-danger">Required</label><br>
    ';
  }
  public function authKey($args){
    $optionName = $args['option_name'];
    $tzPaymentGateway =  get_option($optionName);
    $authKey = $tzPaymentGateway ? $tzPaymentGateway['auth_key'] ?? '': '';
    echo '<input type="text" name="' . $optionName . '[auth_key]" id="tz_auth_key" class="regular-text" required value="' . $authKey . '" placeholder="Enter Auth Key for this app (required)" />
    </br><label for="tz_api_key" class="tz-admin-danger">Required</label><br>
    <p>

      <h3>To get your App ID,  API Key, set a webhook & Auth key, do the following</h3>

      <ol>
        <li>Create a Tranzak merchant account from the web (<a  target="_blank" href="https://biz.tranzak.me/sign-up">https://biz.tranzak.me/sign-up</a>). For users who already have the mobile app, upgrade to a business account </li>
        <li>Login to your account on the web (<a target="_blank"  href="https://biz.tranzak.me/login">https://biz.tranzak.me/login</a>)</li>
        <li>Go to <code>Payment options</code> on the <code>Settings</code> tab (<a target="_blank"  href="https://biz.tranzak.me/settings/payment-options">https://biz.tranzak.me/settings/payment-options</a>) and make sure the following settings are toggled on <code> Enable mobile wallet direct charge</code> and <code>Enable in-person payment</code></li>
      </ol>
      <h4>App ID</h4>
      <ol start="4">
        <li>Open the <code>Developer</code> tab (<a target="_blank"  href="https://biz.tranzak.me/developer">https://biz.tranzak.me/developer</a>)</li>
        <li>Click the <code>+ Add App</code> button, fill the required information and click the<code>Add Mini App</code> button</li>
        <li>Switch mode from <code>Sandbox</code> to <code>Production</code>, then select the <code>API Keys</code> tab, choose your newly created App from the list of Apps below and you\'ll see your <code>App name</code>, <code>Display name</code> and <code>App ID</code>. </li>
        <li><code>Copy</code> your <code>App ID</code>(it begins with <code>ap...</code>) and paste in the App ID box above on this page.</li>
      </ol>
      <h4>API Key</h4>
      <ol start="8">
        <li>Click the <code>Generate key</code> button get get your <code>API Keys</code> for this app, toggle on then collections button (<code>Collection: Used for receiving money</code>) and click the <code>Confirm</code> button to generate key. Copy the key and paste in the <code>API Key</code> box on this page.</li>
        <li>Click the <code>Generate key</code> button get get your <code>API Keys</code> for this app, toggle on then collections button (<code>Collection: Used for receiving money</code>) and click the <code>Confirm</code> button to generate key. Copy the key and paste in the <code>API Key</code> box on this page.</li>
      </ol>
      <h4>Webhook and Auth Key</h4>
      <ol start="10">
        <li>Open the <code>Webhooks</code> tab, click the <code>Add webhook</code> button, then create a webhook for all events by selecting the first event (<code>All EVENTS</code>), copy the url above on the this page (<code>Webhook)</code> and paste in te <code>URL</code> box, Select your app from the <code>Match App</code> box. Click the <code>Generate auth key</code> button to automatically generate an auth key or enter some random alphanumeric characters which are not easy to guess. Copy the generated auth key and paste on the <code>Auth key</code> above on this page before clicking then <code>Generate webhook</code> button. </li>

        </ol>
      <!--<div class="tz-style-pt pt-1 p-1">
        There is a detailed Youtube video to walk you around this if you are having a hard time with the above instructions. <a href="https://www.youtube.com/@tranzak790" target="_blank">Open video on YouTube</a>
      </div> -->

    </p>
    <style>
      .tz-admin-danger{
        color: #b00;
      }
    </style>
    ';
  }

  public function orgIdField($args){
    $optionName = $args['option_name'];
    $tzPaymentGateway =  get_option($optionName);
    $orgId = $tzPaymentGateway ? $tzPaymentGateway['org_id'] ?? '': '';
    // $orgId = esc_attr( get_option('tz_org_id'));
    echo '<input type="text" name="'. $optionName . '[org_id]" id="tz_org_id" class="regular-text" value="'.$orgId.'" placeholder="Enter Org ID (Tranzak ID of the owner of this app)" />
          <div class="tz-style-pt pt-1 p-1">
            <a href="https://www.youtube.com/@tranzak790" target="_blank">How to get your Org ID</a>
          </div>';
  }

  public function webhookField($args){
    $optionName = $args['option_name'];
    $webhook = get_site_url() . '/wp-json/tranzak-payment-gateway/v1/webhook';
    // $orgId = esc_attr( get_option('tz_org_id'));
    echo ' <code>'.$webhook.'</code>';
  }
  public function currencyCodeField($args){

    $currencies = $this->currencies;


    $optionName = $args['option_name'];
    $tzPaymentGateway =  get_option($optionName);
    $currency = $tzPaymentGateway ? $tzPaymentGateway['currency'] ?? 'XAF': 'XAF';
    // $orgId = esc_attr( get_option('tz_org_id'));
    echo '<select name="'. $optionName . '[currency]" id="tz_currency" class="regular-text" value="'.$currency.'">';

    for($i=0; $i<count($currencies); $i++){
      echo '<option value="'.$currencies[$i].'"'. ($currencies[$i] == $currency? 'selected': '') .'>'.$currencies[$i].'</option>';
    }
    echo '</select>
          <div class="tz-style-pt pt-1 p-1">
            For wooCommerce orders, payments will be made in the default currency specified for wooCommerce
          </div>';
  }
  public function envField($args){

    $environments = [
      "production",
      "sandbox",
    ];


    $optionName = $args['option_name'] ?? 'env';

    $tzPaymentGateway =  get_option($optionName);
    // print_r($tzPaymentGateway);
    $env = $tzPaymentGateway ? $tzPaymentGateway['env'] ?? 'production': 'production';
    // $orgId = esc_attr( get_option('tz_org_id'));
    echo '<select name="'. $optionName . '[env]" id="tz_env" class="regular-text" value="'.$env.'">';

    for($i=0; $i<count($environments); $i++){
      echo '<option value="'.$environments[$i].'"'. ($environments[$i] == $env? 'selected': '') .'>'.ucfirst($environments[$i]).'</option>';
    }
    if($env !== 'production'){
      // echo '</select>
      //       <div class="tz-style-pt pt-1 p-1 tz-admin-danger">
      //         Don\'t forget to set this to "Production" when you want to start receiving payments
      //       </div>';
    }
  }

  public function returnUrl($args){
    $optionName = $args['option_name'];
    $tzPaymentGateway =  get_option($optionName);
    $returnUrl = $tzPaymentGateway && isset($tzPaymentGateway['return_url ']) ? $tzPaymentGateway['return_url'] : $this->returnUrl;
    echo '<input type="text" name="' . $optionName . '[return_url]" id="tz_return_url" class="large-text" required value="' . $returnUrl . '" placeholder="URL to redirect to after a transaction is completed (required)" />
    <br />
    <p>
      All transacts will redirect to this page for verification.  If you wish to create your own dedicated page for this, create a page, replace the return URL above with the link to your page and add the following shortcode to your page
      <code>['.$this->transactionShortCode.']</code>
    </p>

    <div class="tz-style-pt pt-1 p-1">
      <h3>
        For any difficulties or assistance, don\'t hesitate to reach out to us
      </h3>
      <div><strong>FAQs / Community</strong>:  <a href="https://community.tranzak.net/" target="_blank">community.tranzak.net</a> </div>
      <div><strong>Email</strong>:  <a href="mailto:support@tranzak.net" target="_blank">support@tranzak.net</a> </div>
      <div><strong>WhatsApp</strong>:  <a href="https://wa.me/237674460261" target="_blank">+237 674 460 261</a> </div>
      <div><strong>Website</strong>:  <a href="https://www.tranzak.net" target="_blank">www.tranzak.net</a> </div>
      </div>
    ';
  }
}