<?php
/* 
 *@package  Tranzak_Payment_Gateway
 */

namespace Inc\Base;
use Inc\Base\BaseController;

if(!class_exists('WP_List_Table')) :
  
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
endif;

class Donations extends \WP_List_Table
{

  private $baseController;
  // Here we will add our code

  // define $table_data property
  private $table_data;

  public function __construct(){
    parent::__construct(array(
      'singular'=> 'donation', //Singular label
      'plural' => 'donations', //plural label, also this well be one of the table css class
      'ajax'   => false //We won't support Ajax for this table
      ) );
    $this->baseController = new BaseController();
  }

  // Get table data
  private function get_table_data($search = '')
  {
    global $wpdb;

    $table = $wpdb->prefix . $this->baseController->wpDonationsTable;

    if (!empty($search)) {
      return $wpdb->get_results(
        "SELECT * from {$table} WHERE title Like '%{$search}%' OR description Like '%{$search}%' OR status Like '%{$search}%'",
        ARRAY_A
      );
    } else {
      return $wpdb->get_results(
        "SELECT * from {$table}",
        ARRAY_A
      );
    }
  }

  public function getDonation($id)
  {
    global $wpdb;

    $table = $wpdb->prefix . $this->baseController->wpDonationsTable;

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
  public function saveDonation($title, $amount, $successMessage, $buttonText, $currency, $backgroundColor, $target ){

    global $wpdb;
    $table =  $wpdb->prefix . $this->baseController->wpDonationsTable;
    $data = array('title' => $title, 'amount' => $amount, 'button_text' => $buttonText, 'currency' => $currency,  'success_message' => $successMessage, 'background_color' => $backgroundColor, 'target' => $target);
    $format = array('%s','%f', '%s', '%s', '%s', '%s', '%f');
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
  public function updateDonation($title, $amount, $successMessage, $buttonText, $currency, $id, $backgroundColor, $target ){

    global $wpdb;
    $table =  $wpdb->prefix . $this->baseController->wpDonationsTable;
    $data = array('title' => $title, 'amount' => $amount, 'button_text' => $buttonText, 'currency' => $currency,  'success_message' => $successMessage, 'background_color' => $backgroundColor, 'target' => $target);
    
    $donation = $wpdb->update($table, $data, array('ID' => $id));
    
    if($donation){
      return $this->getTransaction($id);
    }
    return false;
  }
  
  public function deleteItem($id)
  {
    global $wpdb;

    $table = $wpdb->prefix . $this->baseController->wpDonationsTable;


    if (empty($id)) {
      return false;
    }
    $result = $wpdb->delete($table, array('ID' => $id));

  }
  
  

  // Define table columns
  function get_columns()
  {
    $columns = array(
      // 'cb' => '<input type="checkbox" />',
      // 'id' => __('ID', 'supporthost-admin-table'),
      'title' => __('Title', 'supporthost-admin-table'),
      'short_code' => __('Short code', 'supporthost-admin-table'),
      'amount' => __('Amount', 'supporthost-admin-table'),
      'success_message' => __('Success message', 'supporthost-admin-table'),
      'progress_short_code' => __('Progress bar short code', 'supporthost-admin-table'),
      'button_text' => __('Button text', 'supporthost-admin-table'),
      'created_at' => __('Created at', 'supporthost-admin-table')
    );
    return $columns;
  }

  // Bind table with columns, data and all
  function prepare_items()
  {
    //data
    if (isset($_POST['s'])) {
      $this->table_data = $this->get_table_data($_POST['s']);
    } else {
      $this->table_data = $this->get_table_data();
    }

    $columns = $this->get_columns();
    $hidden = (is_array(get_user_meta(get_current_user_id(), 'tz_donations_page_list', true))) ? get_user_meta(get_current_user_id(), 'tz_donations_page_list', true) : array();
    $sortable = $this->get_sortable_columns();
    $primary = 'title';
    $this->_column_headers = array($columns, $hidden, $sortable, $primary);

    usort($this->table_data, array(&$this, 'usort_reorder'));

    /* pagination */
    $per_page = $this->get_items_per_page('elements_per_page', 10);
    $current_page = $this->get_pagenum();
    $total_items = count($this->table_data);

    $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

    $this->set_pagination_args(
      array(
        'total_items' => $total_items,
        // total number of items
        'per_page' => $per_page,
        // items to show on a page
        'total_pages' => ceil($total_items / $per_page) // use ceil to round up
      )
    );

    $this->items = $this->table_data;
  }

  // set value for each column
  function column_default($item, $column_title)
  {
    switch ($column_title) {
      case 'id':
      case 'title':
      case 'success_message':
      case 'amount':
      // case 'order':
      default:
        return $item[$column_title];
    }
  }

  // Add a checkbox in the first column
  function column_cb($item)
  {
    return sprintf(
      '<input type="checkbox" title="element[]" value="%s" />',
      $item['ID']
    );
  }
  function column_created_at($item)
  {
    return $item['created_at']. ' ('. date_default_timezone_get(). ')'; 
  }
  function column_short_code($item)
  {
    return sprintf(
      '<code> [tz_pg_donations id=%s] </code>',
      $item['ID']
    );
  }
  function column_progress_short_code($item)
  {
    return sprintf(
      '<code> [tz_pg_progress id=%s] </code>',
      $item['ID']
    );
  }
  function column_ID($item)
  {
    return sprintf(
      '#%s',
      $item['ID']
    );
  }
  function column_amount($item)
  {
    $amount = (float) $item['amount'];
    $response = '<h4>'.number_format((float) $amount, gettype((float) $amount) == 'integer' ? 0 : 2).' <small>'. $item['currency'].'</small></h4>';
    if($item['amount'] == '0'){
      $response = 'Specified by user ('.$item['currency'].')';
    }
    return $response;
  }

  // Define sortable column
  protected function get_sortable_columns()
  {
    $sortable_columns = array(
      // 'title' => array('name', false),
      // 'amount' => array('status', true),
      // 'id' => array('order', true)
    );
    return $sortable_columns;
  }

  // Sorting function
  function usort_reorder($a, $b)
  {
    // If no sort, default to user_login
    $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'ID';

    // If no order, default to asc
    $order = (!empty($_GET['id'])) ? $_GET['id'] : 'desc';

    // Determine sort order
    $result = strcmp($a[$orderby], $b[$orderby]);

    // Send final sort direction to usort
    return ($order === 'asc') ? $result : -$result;
  }

  // Adding action links to column
  function column_title($item)
  {
    $actions = array(
      'edit' => sprintf('<a href="'.admin_url( '/admin.php?page=tranzak_payment_gateway_donations_add&id='.$item['ID'].'">' . __('Edit', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'edit', $item['ID'])),
      'delete' => sprintf('<a href="?page=%s&action=%s&element=%s">' . __('Delete', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'delete', $item['ID']),
    );

    return sprintf('%1$s %2$s', $item['title'], $this->row_actions($actions));
  }

  // To show bulk action dropdown
  function get_bulk_actions()
  {
    $actions = array(
      // 'delete_all' => __('Delete', 'supporthost-admin-table'),
      // 'draft_all' => __('Move to Draft', 'supporthost-admin-table')
    );
    return $actions;
  }
}