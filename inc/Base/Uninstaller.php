<?php 
/*
*@package  Tranzak_Payment_Gateway
*/

namespace Inc\Base;

class Uninstaller{
  static function uninstall(){
    
    if (!defined('WP_UNINSTALL_PLUGIN')){
      die;
    }
    
    $tz_options = 'tranzak_payment_gateway';
    $tz_token = 'tranzak_payment_gateway_token';
    
    delete_option( $tz_options );
    delete_option( $tz_token );
    
    global $wpdb;
    
    $transactions_table_name = $wpdb->prefix . "tranzak_pg_transactions";
    $donations_table_name = $wpdb->prefix . "tranzak_pg_donations";
    
    $wpdb->query( "DROP TABLE IF EXISTS ".$transactions_table_name );
    $wpdb->query( "DROP TABLE IF EXISTS ".$donations_table_name );
    
    wp_cache_flush();
  }
}