<?php defined("BASEPATH") or exit('No direct script access allowed'); ?>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript">
  // This identifies your website in the createToken call below
  Stripe.setPublishableKey("<?php echo $publishable_key; ?>");
  jQuery(function($) {
      $('#payment-form').submit(function(event) {
        var $form = $(this);

        // Disable the submit button to prevent repeated clicks
        $form.find('button').prop('disabled', true);

        Stripe.card.createToken($form, stripeResponseHandler);

        // Prevent the form from submitting with the default action
        return false;
      });

      function stripeResponseHandler(status, response) {
      var $form = $('#payment-form');

      if (response.error) {
        // Show the errors on the form
        $form.find('.payment-errors').text(response.error.message);
        $form.find('button').prop('disabled', false);
      } else {
        // response contains id and card, which contains additional card details
        var token = response.id;
        console.log(token);
        return;
        // Insert the token into the form so it gets submitted to the server
        $form.append($('<input type="hidden" name="stripeToken" />').val(token));
        // and submit
        $form.get(0).submit();
      }
    };
    });
</script>
<?php $this->load->view('templates/notification', array(
'error' => ($this->session->flashdata('error') ? $this->session->flashdata('error') : (isset($error) ? $error : false )),
'message' => ($this->session->flashdata('message') ? $this->session->flashdata('message') : (isset($message) ? $message : false ))
)); ?>
<?php if(!empty($account->external_accounts->data)): ?>
    <?php foreach($account->external_accounts->data as $source): ?>
        <?php echo form_open('accounts/remove_method'); ?>
        <?php echo form_input(array('name' => 'source_id', 'type' => 'hidden', 'value' => $source->id)); ?>
        <?php echo form_submit('remove', "Remove Method"); ?>
        <?php echo form_close(); ?>
    <?php endforeach; ?>
<?php endif; ?>
<section class='innerpage'>
	<div class='small-12 medium-6 div-center'>
	<form action="" method="POST" id="payment-form">
	<span class="payment-errors"></span>
		<div class='form-row'>
		<label><span>Account Type</span></label>
		<?php echo form_dropdown('account_type', array(
    'card' => "Debit Card",
    'bank_account' => "Bank Account"
)); ?></div>
	  <div class="form-row">
	    <label>
	      <span>Card Number</span>
	      <input type="text" size="20" data-stripe="number"/>
	    </label>
	  </div>
	  <div class="form-row">
	    <label>
	      <span>CVC</span>
	      <input type="text" size="4" data-stripe="cvc"/>
	    </label>
	  </div>
	  <div class="form-row">
	    <label>
	      <span>Expiration (MM/YYYY)</span>
	      <input type="text" size="2" data-stripe="exp-month"/>
	    </label>
	    <span> / </span>
	    <input type="text" size="4" data-stripe="exp-year"/>
	  </div>
	  <input type='hidden' data-stripe='currency' value='usd'/>
	  <div class='form-row'><button class='btn' type="submit">Submit Payment</button></div>
	</form>
	</div>
</section>




<?php $this->load->view('templates/footer'); ?>
