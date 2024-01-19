<?php
  use Inc\Base\Transactions;
  use Inc\Base\Donations;

  $donations = new Donations();

  $action = $_GET['action'] ?? null;
  $element = $_GET['element'] ?? null;



  if( !empty($action) && $action == 'delete' && !empty($element)){
    $deleted = $donations->deleteItem($element);
  }
  


  echo '<div class="wrap"><h1 class="wp-heading-inline">Donations</h1>
  <a href="'.admin_url( '/admin.php?page=tranzak_payment_gateway_donations_add').'" class="page-title-action">Add new</a><br /><br /><hr />';
  echo '<form method="post">';
  // Prepare table
  $donations->prepare_items();
  // Search form
  $donations->search_box('search', 'search_id');
  // Display table
  $donations->display();
  echo '</div><donations/form>';


?>