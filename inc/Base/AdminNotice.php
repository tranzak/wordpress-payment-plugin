<?php
/*
  @package Tranzak_Payment_Gateway
*/

namespace Inc\Base;

class AdminNotice extends BaseController{
  public function register(){
    if( isset($this->pluginOptions['env']) && $this->pluginOptions['env'] == 'sandbox'){
      add_action('admin_notices', array($this, 'sandBoxNotice'));
    }
  }
  public function sandBoxNotice(){
    echo '<div class="tz-pg-admin-notice notice notice-error is-dismissible">
            <div class="">
              <h3 class="tz-theme-color">Tranzak Payment Gateway</h3>
              <p>
                You are currently running on sandbox (test environment). In other for real transactions to go through, you need to switch <strong>Environment</strong> to production and generate new <strong>API Key</strong> for production environment.
              </p>
              <div class="tz-pg-spacer">
                <a class="button" href="'.admin_url( '/admin.php?page=tranzak_payment_gateway').'">
                  <span>Switch to production</span>
                </a>
                <a class="button" href="https://biz.tranzak.me/developer" target="_blank" data-action="cancel">
                  <span>Generate new keys</span>
                </a>
              </div>

            </div>
        </div>';
  }
}
