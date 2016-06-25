<?php defined("BASEPATH") or exit('No direct script access allowed');

$requirements = array('company', 'query_string');
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

<p style='text-align:justify;margin:auto;min-width:450px;width:50%'>Hi <?php echo $company->name; ?>,</p><br>

<p style='text-align:justify;margin:auto;min-width:450px;width:50%'>We hope you’re doing well!</p><br>

<p style='text-align:justify;margin:auto;min-width:450px;width:50%'>This is a friendly reminder that your campaign recently closed, and it’s time to Tapp your favorite ads.</p><br>

<p style='text-align:justify;margin:auto;min-width:450px;width:50%'>Tappyn partners who created personalized ads for your campaign have been anxiously waiting to find out if they have been chosen. If you have questions, let us know. If you’re ready, select your favorite ads
below!</p><br>

<p style='text-align:center'>
    <a style='display:inline-block;background:#FF5E00;border-radius:4px;color:#fff;font-height:400;font-size: 18px;width:250px;height:50px;padding:0;line-height:50px;text-decoration:none' href="<?php echo base_url().'dashboard'; ?>">
        Tapp Ads
    </a>
<p><br>

<p style='text-align:justify;margin:auto;min-width:450px;width:50%'>Please note that if you don’t choose at least one ad within 3 days of your campaign closing, the submission with the highest CTR will be chosen. So don’t delay: test your favorite ads now, and keep the ball rolling!</p><br>

<!-- End Email Content -->

<p style='text-align:justify;margin:auto;min-width:450px;width:50%'>
    Feel free to hit me up with any feedback or questions!
</p>
<br>
<p style='text-align:justify;margin:auto;min-width:450px;width:50%'>
  -Alek
  <br>
  Co-Founder Tappyn
    <br>
  (678)-367-1060
    <br>
    <a href="<?php echo base_url(); ?>">www.tappyn.com</a>
</p>
