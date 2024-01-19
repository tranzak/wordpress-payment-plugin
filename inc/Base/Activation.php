<?php 
/*
*@package  Tranzak_Payment_Gateway
*/

namespace Inc\Base;

class Activation{
  static function activate(){
    flush_rewrite_rules();

    global $wpdb;
    $transactions_table_name = $wpdb->prefix . "tranzak_pg_transactions";
    $donations_table_name = $wpdb->prefix . "tranzak_pg_donations";
    $dbVersion = '1.0.0';
    $charsetCollate = $wpdb->get_charset_collate();

    if ( $wpdb->get_var("SHOW TABLES LIKE '{$transactions_table_name}'") != $transactions_table_name ) {

        $sql = "CREATE TABLE $transactions_table_name (
                ID mediumint(9) NOT NULL AUTO_INCREMENT,
                `origin` INT NOT NULL,
                `status` INT NOT NULL,
                `reference` VARCHAR(255),
                `currency` VARCHAR(255) NOT NULL,
                `amount` DECIMAL NOT NULL,
                `description` VARCHAR(255),
                `url` TEXT,
                `external_transaction_id` VARCHAR(255),
                `extra` TEXT,
                `created_at` timestamp default now(), 
                `updated_at` timestamp default now() on update now(), 
                PRIMARY KEY  (ID)
        ) $charsetCollate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /** 
     * Creating donations table
     */
    $donations_table_name = $wpdb->prefix . "tranzak_pg_donations";

    if ( $wpdb->get_var("SHOW TABLES LIKE '{$donations_table_name}'") != $donations_table_name ) {

        $sql = "CREATE TABLE $donations_table_name (
                ID mediumint(9) NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(255),
                `status` INT NOT NULL DEFAULT 1,
                `currency` VARCHAR(255) NOT NULL DEFAULT 'XAF',
                `amount` DECIMAL NOT NULL,
                `target` DECIMAL,
                `description` VARCHAR(255),
                `button_text` VARCHAR(255),
                `background_color` VARCHAR(10),
                `success_message` TEXT,
                `created_at` timestamp default now(), 
                `updated_at` timestamp default now() on update now() ,
                `extra` TEXT,
                PRIMARY KEY  (ID)
        ) $charsetCollate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    if(!get_option('tranzak_payment_gateway')){
      $default = [
        'app_id' => '',
        'return_url' => '',
        'env' => 'production',
        'currency' => 'XAF',
        'db_version' => $dbVersion
      ];
  
      /**
       * Update db entires with default empty object to avoid errors
       */
  
      update_option('tranzak_payment_gateway', $default);
    }

    /**
     * Checking if token already exist in the db
     */

    $option = get_option('tranzak_payment_gateway_token');
    
    
    if(!get_option('tranzak_payment_gateway_token')){
      $default = [
        'token' => '',
        'expiresIn' => 0
      ];
  
      /**
       * Update db entires with default empty object to avoid errors
       */
  
      update_option('tranzak_payment_gateway_token', $default);
    }

  }

  /**
   * Creating a default page that will be used to reconcile payments between tranzak and the website
   * @return void
   */
  static function createDefaultPage(){
    global $wpdb;
    $baseController = new BaseController();
    $customPost = array(
      'post_title' => $baseController->paymentTemplateTitle,
      'post_name' => $baseController->paymentTemplateName,
      // 'post_content' => '',
      'post_content' => "<!-- wp:shortcode --> [$baseController->transactionShortCode] <!-- /wp:shortcode -->",
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
      // 'page_template' => $baseController->paymentTemplateSlug
    );

    $paymentPage = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='page'", $customPost['post_title'] ));



    if( !$paymentPage) {
      $postId = wp_insert_post( $customPost );
      if($postId ){
        // wp_update_post(array(
        //   'guid' => wp_count_posts('page').'page='.$postId
        // ));
      }
    }else{
      
    }
  }
}