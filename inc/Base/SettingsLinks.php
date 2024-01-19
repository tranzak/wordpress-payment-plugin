<?php 
/*
*@package  Tranzak_Payment_Gateway
*/

namespace Inc\Base;

class SettingsLinks extends BaseController{

  function register(){

    add_filter('plugin_action_links_' . $this->plugin , array($this, 'settings_link'));
  }
  public function settings_link( $links ){
    $settings_link = '<a href="admin.php?page='.$this->pluginPageUrl.'">Settings</a>';
    array_push($links, $settings_link);
    return $links;
  }
}