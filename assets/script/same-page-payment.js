/**
 * First define all globally used variables
 */
let overlay;
let lang = navigator.language || 'en-US';
let transition = {};
let tzGlobal = {};
let transaction = {};
let interval;
let intervalCounter;
let orderId;
let error;
let tzForm;

let requiredFields = [
  'billing_first_name',
  'billing_last_name',
  // 'billing_company',
  'billing_country',
  'billing_address_1',
  'billing_city',
  'billing_state',
  'billing_postcode',
  'billing_phone',
  'billing_email',
  'payment_method',
  'woocommerce-process-checkout-nonce',
];

let requiredShippingFields = [
  'shipping_first_name',
  'shipping_last_name',
  // 'shipping_company',
  'shipping_country',
  'shipping_address_1',
  'shipping_city',
  'shipping_state',
  'shipping_postcode',
  // 'order_comments',
  // 'shipping_method',
];



/**
 * Actions to run when page loads
 */
jQuery(document).ready($=> {
  // tzGlobal = mtnMoMo || orangeMoney || {};
  overlay = document.getElementById('tz-overlay');
  tzForm = jQuery('form[name="checkout"]');
  if(tzForm){
    tzForm = tzForm[0];
    jQuery(tzForm).on("change", function() {
      toggleCheckoutButton();
      tzHideError();
    });
  }
  if(!overlay){
    overlay = document.createElement('div');
    overlay.setAttribute('id', 'tz-overlay');
    overlay.style.display = 'none';
    document.body.prepend(overlay);
  }

  jQuery('body').on('init_checkout', function(){
    // setTimeout(toggleCheckoutButton, 800);
    toggleCheckoutButton();
  });

})

/**
 * This will push a new error message similar to the default woo commerce error message when an error occurred during checkout
 * @param {string} message 
 */
function tzPushWooError(message){
  let container = document.getElementsByClassName('woocommerce-NoticeGroup-checkout');
  if(container && container.length){
    container[0].innerHTML = message;

    jQuery("html, body").animate({ scrollTop: 0 }, "slow");
  }else{
    container = document.createElement('div');
    container.classList = 'woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout';
   container.innerHTML = message;
    tzForm.prepend(container);
    // window.scrollTo(0, 0);
    jQuery("html, body").animate({ scrollTop: 0 }, "slow");
  }
}

function tzIsMtn(phone) {
  return /^6[78]/.test(phone) || /^65[0-4]/.test(phone);
}

function tzValidPhoneNumber(phone) {
  return /^6[256789]\d{7}$/.test(phone);
}

function tzIsOrange(phone) {
  return /^69/.test(phone) || /^65[5-9]/.test(phone);
}


/**
 * This will do form validation since we are no longer using the default checkout button which handles form validation
 */
function tzValidateInput(){
  let res = true;
  const formData = new FormData(tzForm);

  /**
   * check user required information
   */
  // for(let i = 0; i < requiredFields.length; i++){
  //   const field = String(formData.get(requiredFields[i])).trim();
  //   if(!field){
  //     res = false;
  //     tzPushWooError('Enter all required fields');
  //     break;
  //   }
  // }

  /**
   * Check shipping information
   */
  if(res){
  //   for(let i = 0; i < requiredShippingFields.length; i++){
  //     const field = String(formData.get(requiredShippingFields[i])).trim();
  //     if(!field){
  //       res = false;
  //       tzPushWooError('Enter all required fields for shipping');
  //       break;
  //     }
  //   }
  }
  return res;
}

/**
 * This will hide / how the default checkout button depending on the selected payment method. It will hide if user selected either MTN or Orange Money as payment method
 */
async function toggleCheckoutButton(){
  const formData = new FormData(tzForm);
  if(window.tzPayments.indexOf(formData.get("payment_method")) < 0){
    jQuery('button[name=woocommerce_checkout_place_order]').show();
    jQuery('input[name=woocommerce_checkout_place_order]').show();
  }else{
    jQuery('button[name=woocommerce_checkout_place_order]').hide();
    jQuery('input[name=woocommerce_checkout_place_order]').hide();
  }
}

/**
 * This triggers the whole flow from validation
 * @param {Number} source Will take either 1 for MTN  and 2 for Orange Money
 * @returns null
 */

const tzTriggerPayment = async function (source = 1){

  try{
    
      if(source == 1){
        tzGlobal = mtnMoMo;
      }else if(source == 2){
        tzGlobal = orangeMoney;
      }else{
        tzShowError('Stop playing. I see what you doing. 😁😂😂');
        return;
      }
    
      if(!tzValidateInput()) return;
    
      tzSetError();
    
    
      const phone = document.getElementById(`${tzGlobal.key}-input`);
      phone.value = phone.value.trim();
    
      
      if(!phone){
        tzShowError('Stop playing. I see what you doing. 😁😂😂');
        return 0;
      }
    
      if(!tzValidPhoneNumber(phone.value)){
        tzShowError('Enter a valid 9 digit Cameroon number');
        return;
      }
    
      if(source == 1){
        if(!tzIsMtn(phone.value)){
          tzShowError('Enter a valid MTN number');
          return 0;
        }
      }else if(source == 2){
        if(!tzIsOrange(phone.value)){
          tzShowError('Enter a valid ORANGE number');
          return 0;
        }
        
      }
    
      if(!tzForm){
        tzShowError('An error occurred. Please leave a messages and your website');
        return 0;
      }
    
    
    
      form = new FormData(tzForm);
      
      form.set('user_id', tzGlobal.userId || undefined);
    
      const formDataObj = {};
      form.forEach((value, key) => (formDataObj[key] = value));
    
      formDataObj['tz_phone_number'] = phone.value;
      
    
    
      let txnCreated = false;
    
      tzHideError();
      tzShowLoader();
      
      await jQuery.post(`${tzGlobal.siteUrl}/?wc-ajax=checkout`, formDataObj, function(res) {
        if(res && res.result == 'success'){
          txnCreated = true;
          const data = JSON.parse(res.message);
          if(data.errorMsg){
            tzPushWooError(data.errorMsg || 'Failed to place order. Please try again.');
            tzHideOverlay();
            return;
          }
          if(data.data && data.data.status){
            transaction = data.data;
            if(transaction.status == 'PAYMENT_IN_PROGRESS'){
              tzStartPolling();
              return;
            }
            return processTransaction(transaction);

          }
        }
        if(res.messages){
          res.errorMsg = res.messages
        }
        tzPushWooError(res.errorMsg || 'Failed to place order. Please try again.');
        tzHideOverlay();
        
      }).fail(function(){
        tzHideOverlay();
      });

  }catch(e){
    tzPushWooError('Failed to place order. An error occurred');
  }


}

function tzRenderOverlay() {
  overlay.style.display = 'block';
  document.body.style.overflow = 'hidden';
}

function tzFormatCurrency (value, currency = 'XAF'){
  return new Intl.NumberFormat(lang, { style: 'currency', currency: currency }).formatToParts(value).map(
      p => p.type != 'literal' && p.type != 'currency' ? p.value : ''
  ).join('');
}

/**
 * This is for the error just above the phone number input
 */
function tzSetError(){
  error = document.getElementById(tzGlobal.errorKey);
}

/**
 * 
 * @param {string} message This message goes to the error just above the phone number input box
 */
function tzShowError(message){
  if(error){
    error.style.display = 'block';
    error.innerText = message
  }
}

/**
 * This will hide the error message box just above the phone number input and will hide depending on the selected payment method
 */
function tzHideError(){
  if(error){
    error.style.display = 'none';
  }
}

/**
 * This is template for the spinner
 */
function tzGetLoaderTemplate(){
  return `
    <div class="tz-overlay-container">
    <span class="tz-loader"></span>
    </div>
  `;
}

/**
 * This is template for redirection
 */
function tzGetRedirectionTemplate(){
  return `
  <div class="tz-overlay-container">
      <div class="tz-overlay-content">
        <div class="tz-mt-50 tz-text-center tz-overlay-content-body">
          <div class="tz-row tz-justify-content-around tz-align-items-center tz-psp-logos">
            <div class="tz-text-center">
              <h2>Redirecting...</h2>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
}

function tzGetRefreshTemplate (){
  return `
  <div class="tz-overlay-container">
    <div class="tz-overlay-content">
      <div class="tz-row">
        <div class="tz-overlay-title tz-w-100">
          Transaction Timeout
        </div>
        <div>
          <div class="tz-close-overlay">
            <span onclick="tzHideOverlay()">x</span>
          </div>
        </div>
      </div>
      <div class="tz-mt-50 tz-text-center tz-overlay-content-body">
        <div class="tz-row tz-justify-content-around tz-align-items-center tz-psp-logos">
          <div class="tz-text-center">
            <p>
              This transaction has not been completed. If you authorized this transaction already, click the refresh button.
            </p>
            <p>
              To proceed with this transaction, dial <strong>${tzGlobal.code}</strong> and authorize this transaction, then click the 'refresh status' button.
            </p>
            <p>
              To initiate a new transaction, click the close button.
            </p>
          </div>
        </div>
      </div>
      <div class="tz-mt-50 tz-text-center">
        <h1 class="tz-d-inline"><strong>${tzFormatCurrency(transaction.amount || 0,transaction.currencyCode || 'XAF')}</strong></h1>${transaction.currencyCode || 'XAF'}
      </div>
      <div class="tz-mt-50 tz-text-center">
        <div class="tz-row">
          <div class="tz-w-100">
            <button class="tz-btn" onclick="tzRefreshTransactionStatus()">Refresh status</button>
          </div>
          <div class="tz-w-100">
            <button class="tz-danger-btn" onclick="tzHideOverlay()">Close</button>
          </div>
        </div>
      </div>
      <div class="tz-text-center tz-mt-20">
        <small>
          Powered by <a class="tz-color" href="https://tranzak.net">Tranzak (www.tranzak.net)</a>
        </small>
      </div>
    </div>
  </div>
`;
}

/**
 * This will generate a template for the waiting page which requires user to authorize transaction
 * @param {Number} amount This is the amount gotten after creating a payment request with Tranzak and it includes fee as well
 * @param {String} currency This is the currency code for the triggered transaction
 * @returns template
 */
function tzGetAuthTemplate(amount = 0, currency = 'XAF', close = false){
  return `
    <div class="tz-overlay-container">
      <div class="tz-overlay-content">
        <div class="tz-row">
          <div class="tz-overlay-title tz-w-100">
            Authorization Payment
          </div>
          <div>
          <!-- 
            <div class="tz-close-overlay">
              <span onclick="tzHideOverlay()">x</span>
            </div>
          -->
          </div>
        </div>
        <div class="tz-mt-50 tz-text-center tz-overlay-content-body">
          <div class="tz-row tz-justify-content-around tz-align-items-center tz-psp-logos">
            <div class="tz-text-center tz-psp-logo">
              <img src="${tzGlobal.pluginUrl}/assets/img/${tzGlobal.logo}">
            </div>
            <div class="tz-text-center">
              <div class="snippet" data-title="dot-carousel">
                <div class="stage">
                  <div class="dot-carousel"></div>
                </div>
              </div>
            </div>
            <div class="tz-text-center">
              <img src="${tzGlobal.defaultLogo? tzGlobal.defaultLogo:  tzGlobal.pluginUrl + '/assets/img/logo.png'}">
            </div>
          </div>
        </div>
        <div class="tz-mt-50 tz-text-center">
          <h1 class="tz-d-inline"><strong>${tzFormatCurrency(amount,currency)}</strong></h1>${currency}
        </div>
        <div class="tz-mt-50 tz-text-center">
          Dial <h3 class="tz-d-inline"><strong>${tzGlobal.code}</strong></h3> to authorize transaction
        </div>
        <div class="tz-text-center tz-mt-20">
          <small>
            Powered by <a class="tz-color" href="https://tranzak.net">Tranzak (www.tranzak.net)</a>
          </small>
        </div>
      </div>
    </div>
  `;
}

/**
 * This will trigger polling every 2 seconds and will expire after 1 / 2 minutes depending on the value of @intervalCounter if user doesn't authorize current transaction
 */
function tzStartPolling() {
  intervalCounter = 60;
  interval = setInterval(tzGetTransactionStatus, 2000)
}

/**
 * This will stop polling the backend if transaction was successful or polling timeout reached
 */
function tzStopPolling() {
  tzHideOverlay();
  intervalCounter = 0;
  clearInterval(interval);
}

/**
 * Show redirecting template
 */
function tzShowRedirection() {
  overlay.innerHTML = tzGetRedirectionTemplate();
  tzRenderOverlay();
}

/**
 * This will load the spinner to the overlay
 */
function tzShowLoader() {
  overlay.innerHTML = tzGetLoaderTemplate();
  tzRenderOverlay();
}

/**
 * This should show the page to enable refreshing transaction status
 */

function tzShowRefreshTemplate(amount, currency){
  overlay.innerHTML = tzGetRefreshTemplate(amount, currency);
  tzRenderOverlay();
}

/**
 * This will load the waiting page to the overlay
 * @param {Number} amount This is the amount gotten from payment request creation and includes payment fee
 * @param {String} currency This is the currency code for the said transaction
 */
function tzShowAuthTemplate(amount, currency) {
  overlay.innerHTML = tzGetAuthTemplate(amount, currency);
  // overlay.style.display = 'block';
}

/**
 * This will hide overlay
 */
function tzHideOverlay() {
  overlay.style.display = 'none';
  document.body.style.overflow = 'auto';
}

async function tzRefreshTransactionStatus(){
  if(!transaction.requestId){
    tzHideOverlay();
    tzPushWooError('Failed to process payment. Please try again.');
    return;
  }
  const formData = {
    id: transaction.requestId
  };

  tzShowLoader();
  
  await jQuery.post(`${tzGlobal.siteUrl}/wp-json/tranzak-payment-gateway/v1/request/verify`,formData, function(res) {
    if(res && res.success){
      transaction = res.data;
      
      if(transaction.status == "SUCCESSFUL"){
        tzClearCart();
        tzShowRedirection();
        location.href = transaction.links.returnUrl + '?transactionId=' + transaction.transactionId;
        return;
      }
      else if(transaction.status == "FAILED"){
        tzPushWooError('Transaction Authorization failed. Please Try again.');
        tzHideOverlay();
        return;
      }
      tzShowRefreshTemplate(transaction.amount + (transaction.fee || 0), transaction.currency );
    }else{
      tzHideOverlay();
      tzStopPolling();
      tzPushWooError(res.errorMsg);
    }
  }).fail(e=>{

  });
}

/**
 * This is the method called during polling and it checks for transaction status for the created payment request
 */
async function tzGetTransactionStatus(){
  if(!transaction.requestId){
    tzStopPolling();
    tzPushWooError('Failed to process payment. Please try again.');
    return;
  }
  const formData = {
    id: transaction.requestId
  };
  // formData.set('id',transaction.requestId);
  // await jQuery.get(`${tzGlobal.siteUrl}/?wc-ajax=checkout`, formDataObj, function(res) {
    await jQuery.post(`${tzGlobal.siteUrl}/wp-json/tranzak-payment-gateway/v1/request/verify`,formData, function(res) {
      intervalCounter -= 1;
      console.log(intervalCounter);
      if(intervalCounter <=0){
        console.log('entered the den')
        tzStopPolling();
        tzShowRefreshTemplate();
        return;
      }
      if(res && res.success){
        transaction = res.data;
        console.log("Transaction here ", transaction)
        tzShowAuthTemplate(transaction.amount + (transaction.fee || 0), transaction.currency );
        return processTransaction(transaction);
      }else{
        tzHideOverlay();
        tzStopPolling();
        tzPushWooError(res.errorMsg);
      }
    }).fail(e=>{
      intervalCounter -=1;
    });
}

function processTransaction(transaction){
  console.log('Came here')
  if(transaction.status == "SUCCESSFUL"){
    tzStopPolling();
    tzClearCart();
    tzShowRedirection();
    location.href = transaction.links.returnUrl + '?transactionId=' + transaction.transactionId;
  }
  else if(transaction.status == "FAILED"){
    tzStopPolling();
    tzHideOverlay();
    tzPushWooError('Transaction Authorization failed. Please Try again.');
  }
  else if(transaction.status == "CANCELLED/REFUNDED"){
    tzStopPolling();
    tzHideOverlay();
    tzPushWooError('Transaction was either canceled or refunded. Please try again.');
  }
  else if(transaction.status == "CANCELLED"){
    tzStopPolling();
    tzHideOverlay();
    tzPushWooError('Transaction was canceled. Please try again.');
  }
  else if(transaction.status == "CANCELLED_BY_PAYER"){
    tzStopPolling();
    tzHideOverlay();
    tzPushWooError('Transaction was canceled by payer. Please try again.');
  }
  else if((transaction.status == 'PENDING' || transaction.status == 'PAYER_REDIRECT_REQUIRED') && transaction.links.paymentAuthUrl ){
    tzStopPolling();
    tzClearCart();
    tzShowRedirection();
    location.href = transaction.links.paymentAuthUrl
  }
  return true;
}

/**
 * This clears the cart after payment was successful
 */
function tzClearCart(){
  jQuery.get(`${tzGlobal.siteUrl}/cart/?tz-empty-cart`, function(res) {
  }).fail(e=>{
    
  });
}