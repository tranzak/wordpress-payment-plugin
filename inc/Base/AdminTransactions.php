<?php
/* 
 *@package  Tranzak_Payment_Gateway
 */

namespace Inc\Base;
use Inc\Base\BaseController;

class AdminTransactions extends \WP_List_Table
{


  private $baseController;
  // Here we will add our code

  // define $table_data property
  private $table_data;

  public function __construct(){
    parent::__construct();
    $this->baseController = new BaseController();
  }

  // Get table data
  private function get_table_data($params)
  {
    global $wpdb;

    // add_filter( 'query_vars', 'addnew_query_vars', 10, 1 );
    // function addnew_query_vars($vars)
    // {   
    //     $vars[] = 'c'; // c is the name of variable you want to add       
    //     return $vars;
    // }


    $table = $wpdb->prefix . $this->baseController->wpTransactionsTable;

    if (!empty($params)) {
      $query = "SELECT * from {$table} WHERE ID > 0";
      if(isset($params['txn_status']) && !empty($params['txn_status'])){
        $query.= " AND status = '{$params['txn_status']}'";
      }
      if(isset($params['origin']) && !empty($params['origin'])){
        $query.= " AND origin = '{$params['origin']}'";
      }
      if(isset($params['service']) && !empty($params['service'])){
        $query.= " AND origin = '{$params['service']}'";
      }
      if(isset($params['description']) && !empty($params['description'])){
        $query.= " AND description like '%{$params['description']}%'";
      }
      if(isset($params['orderby']) && !empty($params['orderby'])){
        $order = isset($params['order']) && $params['order'] == 'asc'? 'asc' : 'desc';
        $query.= " ORDER BY {$params['orderby']} {$order}";
      }else{
        $query.= " ORDER BY created_at desc";
      }
      return $wpdb->get_results(
        $query,
        // "SELECT * from {$table} WHERE amount Like '%{$search}%' OR description Like '%{$search}%' OR id Like '%{$search}%' OR currency Like '%{$search}%'",
        ARRAY_A
      );
    } else {
      return $wpdb->get_results(
        "SELECT * from {$table} ORDER BY created_at desc",
        ARRAY_A
      );
    }
  }

  public function getDonation($id)
  {
    global $wpdb;

    $table = $wpdb->prefix . $this->baseController->wpTransactionsTable;

    $result = $wpdb->get_row("SELECT * FROM $table WHERE ID = $id");
    if($result){
      return $result;
    }
    
    return false;
  }

  

  // Define table columns
  function get_columns()
  {
    $columns = array(
      // 'cb' => '<input type="checkbox" />',
      'id' => __('ID', 'supporthost-admin-table'),
      'service' => __('Service', 'supporthost-admin-table'),
      'amount' => __('Amount', 'supporthost-admin-table'),
      'status' => __('status', 'supporthost-admin-table'),
      'description' => __('Description', 'supporthost-admin-table'),
      'created_at' => __('Created at', 'supporthost-admin-table'),
    );
    return $columns;
  }

  // Bind table with columns, data and all
  function prepare_items()
  {
    //data
    if (isset($_POST ) && !empty($_POST )) {
      $this->table_data = $this->get_table_data($_POST);
    } else {
      $this->table_data = $this->get_table_data($_GET);
    }

    $columns = $this->get_columns();
    $hidden = (is_array(get_user_meta(get_current_user_id(), 'tz_transactions_page_list', true))) ? get_user_meta(get_current_user_id(), 'tz_transactions_page_list', true) : array();
    $sortable = $this->get_sortable_columns();
    $primary = 'amount';
    $this->_column_headers = array($columns, $hidden, $sortable, $primary);

    // usort($this->table_data, array(&$this, 'usort_reorder'));

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

  protected function extra_tablenav( $which ) {
    $status = '';
    $statuses = array(
      array('title' => 'Pending', 'value' => 1),
      array('title' => 'Paid', 'value' => 2),
      array('title' => 'Failed', 'value' => -1),
      array('title' => 'All statuses', 'value' => '')
    );

    if ( 'top' !== $which ) {
      return;
    }

    if(isset($_GET['txn_status'])){
        $status = (int)sanitize_text_field($_GET['txn_status']);
    }
    ?>
    <div class="alignleft actions bulkactions">
      
      <select name="txn_status" id="filter-by-status" onchange="tzFilterElementChange(this)">
        <!-- <option value="" >Status</option> -->
        <?php

        foreach($statuses as $item){
          echo '<option value="'.$item['value'].'" '.($item['value'] == $status ? 'selected': '').'>'.$item['title'].'</option>';

        }
      
        // submit_button( __( 'Filter' ), '', 'status', false, array( 'status' => 'post-query-submit' ) );

        ?>
      </select>
    </div>
    <?php

    $origin = '';
    $origins = array(
      array('title' => 'Purchase / Order', 'value' => 1),
      array('title' => 'Donation', 'value' => 2),
      array('title' => 'All services', 'value' => '')
    );

    if(isset($_GET['origin'])){
      $origin = (int)sanitize_text_field($_GET['origin']);
    }
    ?>
    <div class="alignleft actions bulkactions">
      
      <select name="origin" id="filter-by-origin" onchange="tzFilterElementChange(this)">
        <!-- <option value="" >Service</option> -->
        <?php

        foreach($origins as $item){
          echo '<option value="'.$item['value'].'" '.($item['value'] == $origin ? 'selected': '').'>'.$item['title'].'</option>';

        }
      
        // submit_button( __( 'Filter' ), '', 'status', false, array( 'status' => 'post-query-submit' ) );

        ?>
      </select>
    </div>
    <?php
  }

  // set value for each column
  function column_default($item, $column_service)
  {
    switch ($column_service) {
      case 'id':
      case 'service':
      case 'amount':
      case 'status':
      case 'description':
      case 'created_at':
      default:
        return $item[$column_service];
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
  function column_service($item)
  {
    $service = 'Donation';
    $url = null;
    if($item['origin'] == 1){
      $service = 'Purchase / Order';
      $url = '<p><a style="margin-left: 5px;" target="_blank" href="'.$item['url'].'">See detail</a></p>';
    }
    return '<code> '.$service.'</code>'.$url; 
  }
  function column_created_at($item)
  {
    return $item['created_at']. ' ('. date_default_timezone_get(). ')'; 
  }
  function column_status($item)
  {
    $status = 'Unpaid';
    $style = "background: #3336; color: #222";
    if((int) $item['status'] == 2){
      $status = 'Paid';
      $style = "background: #0c03; color: #0c0";
    }else if( (int) $item['status'] == -1){
      $status = 'Failed';
      $style = "background: #c003; color: #c00";
    }
    return '<code style="'.$style.'"> '.$status.'</code>'; 
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
      'service' => array('origin', true),
      'amount' => array('amount', true),
      'status' => array('status', true),
      'created_at' => array('created_at', true),
      'id' => array('ID', true)
    );
    return $sortable_columns;
  }

  // Sorting function
  // function usort_reorder($a, $b)
  // {
  //   // If no sort, default to user_login
  //   $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'ID';

  //   // If no order, default to asc
  //   $order = (!empty($_GET['id'])) ? $_GET['id'] : 'asc';
    
  //   // If no order, default to asc
  //   // $order = (!empty($_GET['txn_status'])) ? $_GET['txn_status'] : 'asc';
    
  //   // // If no order, default to asc
  //   // $order = (!empty($_GET['origin'])) ? $_GET['origin'] : 'asc';

  //   // Determine sort order
  //   $result = strcmp($a[$orderby], $b[$orderby]);

  //   // Send final sort direction to usort
  //   return ($order === 'asc') ? $result : -$result;
  // }


  // To show bulk action dropdown
  function get_bulk_actions()
  {
    // $actions = array(
    //   'delete_all' => __('Delete', 'supporthost-admin-table'),
    //   'draft_all' => __('Move to Draft', 'supporthost-admin-table')
    // );
    // return $actions;
  }
}