<?php 
/*
*@package  Tranzak_Payment_Gateway
*/

namespace Inc\Api;

class SettingsApi{
  public $adminPages = array();
  public $settings = array();
  public $sections = array();
  public $fields = array();
  public $adminSubPages = array();
  public function addPages( array $pages){
    $this->adminPages = $pages;
    return $this;
  }

  public function withSubPage(string $title = null){
    if(empty($this->adminPages)){
      return $this;
    }

    $adminPage = $this->adminPages[0];
    $subPages = [
      [
        'parent_slug' => $adminPage['menu_slug'],
        'page_title' => $adminPage['page_title'],
        'menu_title' => $title ?? $adminPage['menu_title'],
        'capability' => $adminPage['capability'],
        'menu_slug' => $adminPage['menu_slug'],
        'callback' => $adminPage['callback'],
      ]
    ];

    $this->adminSubPages = $subPages;
    return $this;
  }

  public function addSubPage(array $pages){

    $this->adminSubPages = array_merge($this->adminSubPages, $pages);
    return $this;
  }

  public function register(){
    if(!empty($this->adminPages)){
      add_action('admin_menu', array($this, 'addAdminMenu'));
    }
    if(!empty($this->settings)){
      add_action('admin_init', array($this, 'registerCustomFields'));
    }
  }

  public function addAdminMenu(){
    foreach($this->adminPages as $page){
      add_menu_page($page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback'], $page['icon_url'], $page['position']);
    }
    foreach($this->adminSubPages as $page){
      add_submenu_page($page['parent_slug'], $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback']);
    }
  }

  public function setSettings( array $settings){
    $this->settings = $settings;
    return $this;
  }

  public function setSections( array $sections){
    $this->sections = $sections;
    return $this;
  }

  public function setFields( array $fields){
    $this->fields = $fields;
    return $this;
  }

  public function registerCustomFields()
  {
    /**
     * Register settings
     */
    foreach ($this->settings as $setting) {
      register_setting($setting['option_group'], $setting['option_name'], $setting['callback'] ?? '');
    }
    /**
     * Add / generate settings section
     */
    foreach ($this->sections as $section) {
      add_settings_section($section['id'], $section['title'], $section['callback'] ?? '', $section['page']);
    }
    /**
     * Register settings fields
     */
    foreach ($this->fields as $field) {
      add_settings_field($field['id'], $field['title'], $field['callback'] ?? '', $field['page'], $field['section'], $field['args'] ?? '');
    }
  }
}