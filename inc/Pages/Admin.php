<?php 
/*
  @package Tranzak_Payment_Gateway
*/

namespace Inc\Pages;
use Inc\Api\Callbacks\AdminCallbacks;
use Inc\Api\SettingsApi;
use Inc\Base\BaseController;

class Admin extends BaseController{

  public $settings;
  public $callbacks;
  public $pages = array();
  public $subPages = array();
  function __construct(){
    parent::__construct();
    $this->settings = new SettingsApi();
    $this->callbacks = new AdminCallbacks();
  }
  public function register(){
    $this->setPages();
    $this->setSubPages();

    $this->setSettings();
    $this->setSections();
    $this->setFields();

    $this->settings->addPages($this->pages)->withSubPage('Settings')->addSubPage($this->subPages)->register();
    // add_action('admin_menu', array($this, 'add_admin_pages'));
  }

  public function setSubPages(){
    $this->subPages = [
      [
        'parent_slug' => 'tranzak_payment_gateway',
        'page_title' => 'Create donation',
        'menu_title' => 'Create Donation',
        'capability' => 'manage_options',
        'menu_slug' => 'tranzak_payment_gateway_donations_add',
        'callback' => [$this->callbacks,'AdminAddDonation'],
      ],
      [
        'parent_slug' => 'tranzak_payment_gateway',
        'page_title' => 'List donations',
        'menu_title' => 'List donations',
        'capability' => 'manage_options',
        'menu_slug' => 'tranzak_payment_gateway_donations',
        'callback' => [$this->callbacks,'AdminDonations'],
      ],
      [
        'parent_slug' => 'tranzak_payment_gateway',
        'page_title' => 'Transactions',
        'menu_title' => 'Transactions',
        'capability' => 'manage_options',
        'menu_slug' => 'tranzak_payment_gateway_transactions',
        'callback' => [$this->callbacks,'AdminTransactions'],
      ]
    ];
  }
  public function setPages(){
    $this->pages = [
      [
        'page_title' => 'Tranzak Payment Gateway',
        'menu_title' => 'Tranzak',
        'capability' => 'manage_options',
        'menu_slug' => 'tranzak_payment_gateway',
        'callback' => [$this->callbacks,'adminSettingsPage'],
        'icon_url' => $this->pluginUrl.'assets/img/icon.png',
        'position' => 110,
      ]
    ];
  }
  public function admin_index(){
    
  }

  public function setSettings(){
    $args = [ 
      [
        'option_group' => 'tz_payment_gateway_general_settings_group',
        'option_name' => 'tranzak_payment_gateway ',
        'callback' => [$this->callbacks, 'tzOptionsGroup']
      ],
      [
        'option_group' => 'tz_payment_gateway_general_settings_group',
        'option_name' => 'tz_org_id ',
        'callback' => [$this->callbacks, 'tzOptionsGroup']
      ],
    ];

    $this->settings->setSettings($args);
  }

  public function setSections(){
    $args = [
      [ 'id' => 'tz_admin_index',
        'title' => 'General Setting ',
        'callback' => [$this->callbacks, 'tzAdminSection'],
        'page' => 'tranzak_payment_gateway'
      ]
    ];
    $this->settings->setSections($args);
  }

  public function setFields(){
    $args = [
      [ 'id' => 'tz_app_id',
        'title' => 'App ID ',
        'callback' => [$this->callbacks, 'appIdField'],
        'page' => 'tranzak_payment_gateway',
        'section' => 'tz_admin_index',
        'args' =>  [
          'option_name' => 'tranzak_payment_gateway',
          'label_for' => 'tz_app_id',
          'class' => 'tz-admin-page'
        ]
      ],
      [ 'id' => 'tz_api_key',
        'title' => 'API Key',
        'callback' => [$this->callbacks, 'apiKey'],
        'page' => 'tranzak_payment_gateway',
        'section' => 'tz_admin_index',
        'args' =>  [
          'option_name' => 'tranzak_payment_gateway',
          'label_for' => 'tz_api_key',
          'class' => 'tz-admin-page'
        ]
      ],
      [ 'id' => 'tz_env',
        'title' => 'Environment',
        'callback' => [$this->callbacks, 'envField'],
        'page' => 'tranzak_payment_gateway',
        'section' => 'tz_admin_index',
        'args' =>  [
          'option_name' => 'tranzak_payment_gateway',
          'label_for' => 'tz_env',
          'class' => 'tz-admin-page'
        ]
      ],
      [ 'id' => 'tz_currency',
        'title' => 'Default Currency',
        'callback' => [$this->callbacks, 'currencyCodeField'],
        'page' => 'tranzak_payment_gateway',
        'section' => 'tz_admin_index',
        'args' =>  [
          'option_name' => 'tranzak_payment_gateway',
          'label_for' => 'tz_currency',
          'class' => 'tz-admin-page'
        ]
      ],
      [ 'id' => 'tz_webhook',
        'title' => 'Webhook',
        'callback' => [$this->callbacks, 'webhookField'],
        'page' => 'tranzak_payment_gateway',
        'section' => 'tz_admin_index',
        'args' =>  [
          'option_name' => 'tranzak_payment_gateway',
          'label_for' => 'tz_webhook',
          'class' => 'tz-admin-page'
        ]
      ],
      [ 'id' => 'tz_auth_key',
        'title' => 'Auth Key',
        'callback' => [$this->callbacks, 'authKey'],
        'page' => 'tranzak_payment_gateway',
        'section' => 'tz_admin_index',
        'args' =>  [
          'option_name' => 'tranzak_payment_gateway',
          'label_for' => 'tz_auth_key',
          'class' => 'tz-admin-page'
        ]
      ],
      [ 'id' => 'tz_return_url',
        'title' => 'Return URL',
        'callback' => [$this->callbacks, 'returnUrl'],
        'page' => 'tranzak_payment_gateway',
        'section' => 'tz_admin_index',
        'args' =>  [
          'option_name' => 'tranzak_payment_gateway',
          'label_for' => 'tz_return_url',
          'class' => 'tz-admin-page'
        ]
      ],
        // [ 'id' => 'tz_org_id',
        // 'title' => 'Org ID ',
        // 'callback' => [$this->callbacks, 'orgIdField'],
        // 'page' => 'tranzak_payment_gateway',
        // 'section' => 'tz_admin_index',
        // 'args' =>  [
        //   'option_name' => 'tranzak_payment_gateway',
        //   'label_for' => 'tz_org_id',
        //   'class' => 'tz-admin-page'
        // ]
      // ],
    ];

    $this->settings->setFields($args);
  }
}
