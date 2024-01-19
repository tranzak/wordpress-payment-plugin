<?php
/* 
 *@package  Tranzak_Payment_Gateway
 */

namespace Inc\Base;
use Inc\Base\BaseController;



class Donation extends BaseController
{

  public function __construct(){
    parent::__construct();
  }

  public function getTransactions($id){
    $transaction = new Transactions();
    $paidTransactions = $transaction->getTransactionsByOrigin(2, $id, $this->transactionStatuses['successful']);
    return $paidTransactions;
  }

  public function getDonation($id)
  {
    global $wpdb;

    $table = $wpdb->prefix . $this->wpDonationsTable;

    $result = $wpdb->get_row("SELECT * FROM $table WHERE ID = $id");
    if($result){
      return $result;
    }
    
    return false;
  }

  /**
   * Summary of saveDonation
   * @param mixed $title
   * @param mixed $amount
   * @param mixed $successMessage
   * @param mixed $buttonText
   * @return mixed
   */
  public function saveDonation($title, $amount, $successMessage, $buttonText, $currency ){

    global $wpdb;
    $table =  $wpdb->prefix . $this->wpDonationsTable;
    $data = array('title' => $title, 'amount' => $amount, 'button_text' => $buttonText, 'currency' => $currency,  'success_message' => $successMessage);
    $format = array('%s','%f', '%s', '%s', '%s');
    $result = $wpdb->insert($table,$data,$format);
    if($result){

      return $wpdb->insert_id;
    }
    return false;
  }

  /**
   * Summary of updateDonation
   * @param mixed $title
   * @param mixed $amount
   * @param mixed $successMessage
   * @param mixed $buttonText
   * @param mixed $id
   * @return mixed
   */
  public function updateDonation($title, $amount, $successMessage, $buttonText, $currency, $id ){

    global $wpdb;
    $table =  $wpdb->prefix . $this->wpDonationsTable;
    $data = array('title' => $title, 'amount' => $amount, 'button_text' => $buttonText, 'currency' => $currency,  'success_message' => $successMessage);
    
    $donation = $wpdb->update($table, $data, array('ID' => $id));
    
    if($donation){
      return $this->getTransaction($id);
    }
    return false;
  }
  
  public function deleteItem($id)
  {
    global $wpdb;

    $table = $wpdb->prefix . $this->wpDonationsTable;


    if (empty($id)) {
      return false;
    }
    $result = $wpdb->delete($table, array('ID' => $id));
  }
}