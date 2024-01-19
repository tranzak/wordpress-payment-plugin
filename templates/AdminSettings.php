<div class="wrap">
  <h1>Tranzak Payment Gateway</h1>
  <?php settings_errors(); ?>
  <form action="options.php" method="post">
    <?php
      settings_fields('tz_payment_gateway_general_settings_group');

      do_settings_sections('tranzak_payment_gateway');

      submit_button();
    ?>
  </form>
</div>