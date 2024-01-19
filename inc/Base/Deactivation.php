<?php 
/*
*@package  Tranzak_Payment_Gateway
*/

namespace Inc\Base;

class Deactivation{
  static function deactivate(){
    flush_rewrite_rules();
  }
}