<?php
  use Inc\Base\Donations;
  use Inc\Base\BaseController;

  $donations = new Donations();

  $errors = array();
  $pageSuccessMessage = "";
  $pageTitle = "Create Donation";

  $baseController = new BaseController();
  $currencies = $baseController->currencies;


  $id = null;
  $redirectScript = '';

  $amount = "";
  $target = "";
  $currency = $baseController->pluginOptions['currency'] ?? 'XAF';
  $title = "";
  $backgroundColor = "#06c125";
  $successMessage = "Thank you for donating.";
  $donateText = "Donate now";


  $title = $_POST['title'] ?? $title;
  $successMessage = $_POST['success_message'] ?? $successMessage;
  $amount = $_POST['amount'] ?? $amount;
  $target = $_POST['target'] ?? $target;
  $donateText = $_POST['button_text'] ?? $donateText;
  $currency = $_POST['currency'] ?? $currency;
  $backgroundColor = $_POST['background_color'] ?? $backgroundColor;

  $shortCodes = '';



  if(isset($_GET['id'])){
    $id = (int) $_GET['id'] ?? null;
    if($id){
      $pageTitle = "Update Donation";
      $donation = $donations->getDonation($id);
      if($donation && !isset($_POST['title'])){
        $amount = $donation->amount == 0? '': $donation->amount;
        $target = $donation->target == 0? '': $donation->target;
        $currency = $donation->currency;
        $backgroundColor = $donation->background_color;
        $title = $donation->title;
        $successMessage = $donation->success_message;
        $donateText = $donation->button_text;
      }

      if(isset($_GET['added']) && $_GET['added'] == 'true'){
        $pageSuccessMessage = 'Donation created successfully';
      }
      $shortCodes = "
      <div style=\"font-weight: bold\">
        <br />
        <p>Short code: <code> [tz_pg_donations id=$id] </code><div style=\"font-weight: 400\">Insert this short code where you want your donation / request button to be displayed</div></p>
        <br />
        <p>Progress bar short code: <code> [tz_pg_progress id=$id] </code><div style=\"font-weight: 400\">Insert this short code where to display a progress bar (if you entered a target amount)</div></p>
        <br />
        </div>
      ";
    }
  }
  if(isset($_POST['title'])){

    if( isset($_POST['title']) && empty($_POST['title'])){
      $errors[] = "Title is required";
      $title = '';
    }
    if( isset($_POST['success_message']) && empty($_POST['success_message'])){
      $errors[] = "Success message is required";
      $successMessage = '';
    }
    if( isset($_POST['button_text']) && empty($_POST['button_text'])){
      $errors[] = "Button text is required";
      $donateText = '';
    }
    if( isset($_POST['currency']) && empty($_POST['currency'])){
      $errors[] = "Currency is required";
      $currency = '';
    }
    if(count($errors) == 0){
      if($id){
        $pageSuccessMessage = 'Donation updated successfully';
        $donation = $donations->updateDonation($title, $amount, $successMessage, $donateText, $currency, $id, $backgroundColor, $target);
      }else{
        $donation = $donations->saveDonation($title, $amount, $successMessage, $donateText, $currency, $backgroundColor, $target);
      }
      if($donation){
        $redirectScript = admin_url( '/admin.php?page=tranzak_payment_gateway_donations_add&id='.$donation.'&added='.(empty($id)? 'true': '') ) ;
      }
    }
  }



  $inputFields = array(
    array( 'size' => 'regular-text', 'title' => 'Title', 'type' => 'input', 'value' => $title, 'description' => 'Name you would like to call this donation', 'input_type' => 'text', 'required' => 'true', 'name' => 'title', 'id' => 'tz-donation-title'),
    array( 'size' => 'regular-text', 'title' => 'Amount', 'type' => 'input', 'value' => $amount, 'description' => 'Amount to be paid (Leave blank if user is to specify amount)', 'input_type' => 'number', 'required' => '', 'name' => 'amount', 'id' => 'tz-donation-amount'),
    array( 'size' => 'regular-text', 'title' => 'Target amount', 'type' => 'input', 'value' => $target, 'description' => 'The desired campaign goal. <strong>It is required only if you want to keep track of a campaign</strong>', 'input_type' => 'number', 'required' => '', 'name' => 'target', 'id' => 'tz-donation-target'),
    array( 'size' => 'regular-text', 'title' => 'Success message', 'type' => 'textarea', 'value' => $successMessage, 'description' => 'Message the use sees after a successful payment', 'input_type' => 'text', 'required' => 'true', 'name' => 'success_message', 'id' => 'tz-donation-success_message'),
    array( 'size' => 'regular-text', 'title' => 'Button text', 'type' => 'input', 'value' => $donateText, 'description' => 'Text to be shown on the donate button', 'input_type' => 'text', 'required' => 'true', 'name' => 'button_text', 'id' => 'tz-donation-button_text'),
    array( 'size' => 'regular-text', 'title' => 'Currency', 'type' => 'select', 'value' => $currency, 'description' => 'Currency code to receive donations', 'input_type' => 'text', 'required' => 'true', 'name' => 'currency', 'id' => 'tz-donation-currency', 'options' => $currencies),
    array( 'size' => 'text', 'title' => 'Progress bar colour', 'type' => 'input', 'value' => $backgroundColor, 'description' => 'This will be the background color of the progress bar in case of campaign progress tracking', 'input_type' => 'color', 'required' => 'false', 'name' => 'background_color', 'id' => 'tz-background-color'),
  );

  if($redirectScript){
    echo '<script type="text/javascript"> location = "'.$redirectScript.'"; </script>';
    die();
  }


  echo '<div class="wrap"><h1 class="wp-heading-inline">'.$pageTitle.'</h1>
    <a href="'.admin_url( '/admin.php?page=tranzak_payment_gateway_donations_add').'" class="page-title-action">Add new</a><br /><br /><hr />
  ';
  echo '<form method="post">';

  foreach($errors as $error){
    echo '<p style="background: #f002; color: #c00; padding: 8px; border-radius: 4px">'.$error.'</p>';
  }

  if($pageSuccessMessage){
    echo '<p style="background: #0f01; color: #0a0; padding: 8px; border-radius: 4px">'.$pageSuccessMessage.'</p>';
  }

  echo "
    $shortCodes
    <table class=\"tz-create-donation-page form-table\" role=\"presentation\">
      <tbody>";

  foreach($inputFields as $field){
  echo '
    <tr>
      <th scope="row">
        <label for="'.$field['id'].'">'.$field['title'].'</label>
      </th>
      <td>
    ';

    if($field['type'] == 'input'){
      echo '
      <input maxlength="255" type="'.$field['input_type'].'" class="'.$field['size'].'" id="'.$field['id'].'" name="'.$field['name'].'" '.($field['required'] == 'true'? 'required': '').' value="'.$field['value'].'"/>
      ';
    }
    else if($field['type'] == 'textarea'){
      echo '
      <textarea type="'.$field['input_type'].'" class="regular-text" rows="4" id="'.$field['id'].'" name="'.$field['name'].'" '.($field['required'] == 'true'? 'required': '').'>'.$field['value'].'</textarea>
      ';
    }
    else if($field['type'] == 'select'){
      echo '<select name="'. $field['name'] . '" id="'.$field['id'].'" class="regular-text" value="'.$field['value'].'">';

      for($i=0; $i<count($field['options']); $i++){
        echo '<option value="'.$field['options'][$i].'"'. ($field['options'][$i] == $field['value']? 'selected': '') .'>'.$field['options'][$i].'</option>';
      }
    echo '</select>';
      // echo '
      // <textarea type="'.$field['input_type'].'" class="regular-text" rows="4" id="'.$field['id'].'" name="'.$field['name'].'" '.($field['required'] == 'true'? 'required': '').'>'.$field['value'].'</textarea>
      // ';
    }
    if($field['required'] == 'true'){
      echo '
      <br />
      <label style="color: #b00;" for="'.$field['id'].'">Required</label>
      ';
    }

  echo '
      <p>'.$field['description'].'</p>
      </td>

    </tr>';
  }

  echo '</tbody>
    </table>
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save donation"></p>
  ';
  echo '</div><donations/form>';


?>