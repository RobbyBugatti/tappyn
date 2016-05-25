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

<h5 style='text-align:center'><?php echo $company->name; ?>'s contest has ended</h5>

<p style='text-align:center;margin:auto;width:600px'>They loved all of the submissions, but there was just one that edged out the rest.</p>

<?php $query_string['redirect'] = 'contest/'.$contest->id; ?>
<p style='text-align:center;margin:auto;width:600px'>See the winning submission <?php echo anchor('api/v1/analytics/click?'.http_build_query($query_string), 'here'); ?></p>

<?php $query_string['redirect'] = 'guide'; ?>
<p style='text-align:center;margin:auto;width:600px'>Wondering why your submission wasn't chosen? Take a look at our creative guide to learn more about what makes a great ad <?php echo anchor('api/v1/analytics/click?'.http_build_query($query_string), 'here'); ?></p>

<?php $query_string['redirect'] = 'contests'; ?>
<p style='text-align:center;margin:auto;width:600px'>Or you can see some of our <?php echo anchor('api/v1/analytics/click?'.http_build_query($query_string), 'new contests'); ?></p>

<!-- End Email Content -->

<?php $this->load->view('email_templates/footer'); ?>
