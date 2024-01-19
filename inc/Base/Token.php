<?php 
/* 
*@package  Tranzak_Payment_Gateway
*/

namespace Inc\Base;

class Token extends BaseController{
  public function __construct(){
    parent::__construct();
  }
  /**
   * Generates token and save to db
   * @return void
   */
  public function genToken(){

    /**Set CRON here after generating token */
    $this->setCRON();
  }
  
  /**
   * Get token from db and return false if not set
   * @return mixed
   */
  public function getToken(){
    
  }

  /**
   * Schedule a task to refresh token 
   * @return bool
   */
  public function setCRON(){
    add_action( 'wpb_custom_cron', array($this, 'genToken') );
    return false;
  }


  
}