<?php defined("BASEPATH") or exit('No direct script access allowed');

$requirements = array('company');
foreach($requirements as $req)
{
    if(!isset($$req))
    {
        throw new Exception("Email data missing {$req}");
    }
}
$query_string['redirect'] = 'dashboard';

?>


<?php $this->load->view('email_templates/header', array('query_string', $query_string)); ?>

<!-- Start Email Content -->

<h2 style='text-align:center;margin:auto;min-width:450px;width:50%'>Congratulations!</h2>
<br>
<!-- Orange header -->
<p style='text-align:center;margin:auto;min-width:450px;width:50%;border-bottom:2px solid #019F6E'></p><br>

<p style='text-align:left;margin:auto;width:600px'><?php echo $company->name; ?> has picked your awesome message for their campaign, and they are sending money your way.</p>
<br>
<p style='text-align:left;margin:auto;width:600px'>To collect your payment, head to your <?php echo anchor('api/v1/analytics/click?'.http_build_query($query_string), 'dashboard'); ?>, and claim the payout for your message. If you haven't yet,
    you'll need to set up your payout information, which takes only seconds. </p>
<br>
<?php $query_string['redirect'] = 'contests'; ?>
<p style='text-align:left;margin:auto;width:600px'>Congrats again on killing it, and keep up your Fabel reign by checking out our other campaigns <?php echo anchor('api/v1/analytics/click?'.http_build_query($query_string), 'here'); ?>. </p>
<br>
<!-- End Email Content -->
<!-- Orange header -->
<p style='text-align:center;margin:auto;min-width:450px;width:50%;border-bottom:2px solid #019F6E'></p><br>

<?php $this->load->view('email_templates/austin_footer'); ?>
