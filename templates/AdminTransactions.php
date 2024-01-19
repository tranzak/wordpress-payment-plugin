<?php
  use Inc\Base\Transactions;
  use Inc\Base\AdminTransactions;

  $donations = new AdminTransactions();

  $action = $_GET['action'] ?? null;
  $element = $_GET['element'] ?? null;



  if( !empty($action) && $action == 'delete' && !empty($element)){
    $deleted = $donations->deleteItem($element);
  }
  


  echo '<div class="wrap"><h1 class="wp-heading-inline">Transactions</h1><br /><br /><hr />';
  echo '<form method="post">';
  // Prepare table
  $donations->prepare_items();
  // Search form
  $donations->search_box('search', 'search_id');
  // Display table
  $donations->display();
  echo '</div><donations/form>';


?>