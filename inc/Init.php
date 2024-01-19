<?php 
/*
*@package  Tranzak_Payment_Gateway
*/
namespace Inc;


final class Init{
  
  /**
   * Stores all classes in an array
   * @return array full list of classes
   */
  public static function get_services(){
    return [
      Pages\Admin::class,
      Base\Enqueue::class,
      Base\SettingsLinks::class,
      Base\Templates::class,
      Base\DonationsTemplate::class,
      Base\AdminNotice::class,
      Base\RestApi::class,
    ];
  }
  /**
   * Loop through the classes and call the register method if it exist
   * @return void
   */
  public static function register_services(){
    foreach( self::get_services() as $class){
      $service = self::instantiate($class);
      if( method_exists($service, 'register')){
        $service->register();
      }
    }
  }

  /**
   * Initialize a class parsed as param
   * @param class class from services array
   * @return class instantiated class
   */
  private static function instantiate($class){
    $service = new $class();
    return $service;
  }

  
}
