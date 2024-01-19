<?php 
/*
  @package Tranzak_Payment_Gateway
*/

namespace Inc\Base;

class Enqueue extends BaseController{
  public function register(){
    add_action('admin_enqueue_scripts', array($this, 'enqueue'));
  }
  public function enqueue(){
    wp_enqueue_style('tz_payment_gateway_admin_style', $this->pluginUrl.'assets/style/admin-style.css');
    wp_enqueue_style('tz_payment_gateway_style', $this->pluginUrl.'assets/style/style.css');
    wp_enqueue_script('tz_payment_gateway_script', $this->pluginUrl.'assets/script/main.js');
  }
}
