<?php defined("BASEPATH") or exit('No direct script access allowed');

class Mailing extends CI_Controller
{
    protected $email_data = array();

    public function __construct()
    {
        parent::__construct();
        if (!is_cli()) {
            die('Invalid request');
        }
        $this->load->library('mailer');
        $this->config->load('emails');
        $this->email_config = $this->config->item('email_program');
    }

    public function execute()
    {
        $queue = $this->db->select('*')->from('mailing_queue')->where('processing', 0)->get()->result();
        foreach ($queue as $job) {
            $attachment = null;
            $this->db->where('id', $job->id)->update('mailing_queue', array('processing' => 1));
            $subject  = false;
            $continue = true;
            // First lets get any associated data with this email
            switch ($job->email_type) {
                case 'contest_closing':
                    // Get data for the email
                    $contest = $this->db->select('*')->from('contests')->where('id', $job->object_id)->limit(1)->get();
                    if (!$contest || $contest->num_rows() == 0) {
                        $this->error_out($job->id, '["Invalid contest supplied"]');
                        $continue = false;
                        continue;
                    }
                    $contest        = $contest->row();
                    $contest->owner = $this->db->select('*')->from('profiles')->where('id', $contest->owner)->get()->row();

                    // Set our subject and any additional data
                    $subject                     = sprintf($this->email_config[$job->email_type]['subject'], is_null($contest->owner->name) ? "This awesome" : $contest->owner->name . 's');
                    $this->email_data['contest'] = $contest;

                    break;

                case 'post_contest_package':
                    $contest = $this->db->select('*')->from('contests')->where('id', $job->object_id)->limit(1)->get();
                    if (!$contest || $contest->num_rows() == 0) {
                        $this->error_out($job->id, '["Invalid contest supplied"]');
                        $continue = false;
                        continue;
                    }

                    $contest        = $contest->row();
                    $contest->owner = $this->db->select('*')->from('profiles')->where('id', $contest->owner)->get()->row();
                    $submission     = $this->db->select('*')->from('payouts')->join('submissions', 'payouts.submission_id = submissions.id', 'left')->where('payouts.contest_id', $contest->id)->get()->row();
                    // Set our subject and any additional data
                    $subject                        = sprintf($this->email_config[$job->email_type]['subject'], is_null($contest->owner->name) ? "This awesome" : $contest->owner->name . 's');
                    $this->email_data['contest']    = $contest;
                    $this->email_data['submission'] = $submission;
                    if (!is_null($this->email_data['submission']->attachment)) {
                        $attachment = $this->email_data['submission']->attachment;
                    }

                    break;

                case 'winner_announced':
                    $contest = $this->db->select('*')->from('contests')->where('id', $job->object_id)->get();
                    if (!$contest || $contest->num_rows() == 0) {
                        $this->error_out($job->id, '["Invalid contest supplied"]');
                        $continue = false;
                        continue;
                    }
                    $contest                     = $contest->row();
                    $contest->owner              = $this->db->select('*')->from('profiles')->where('id', $contest->owner)->get()->row();
                    $subject                     = sprintf($this->email_config[$job->email_type]['subject'], is_null($contest->owner->name) ? "They " : $contest->owner->name);
                    $this->email_data['contest'] = $contest;
                    $this->email_data['company'] = $contest->owner;
                    break;

                case 'mailing_list_conf':

                    break;

                case 'sign_up_conf':
                    $user = $this->db->select('*')->from('users')->where('id', $job->object_id)->limit(1)->get();
                    if (!$user || $user->num_rows() == 0) {
                        $this->error_out($job->id, '["Invalid user supplied"]');
                        $continue = false;
                        continue;
                    }
                    $user                           = $user->row();
                    $this->email_data['activation'] = is_null($user->activation_code) ? 'activation' : $user->activation_code;
                    $this->email_data['uid']        = $user->id;
                    break;

                case 'contest_completed':
                    $contest = $this->db->select('*')->from('contests')->where('id', $job->object_id)->get();
                    if (!$contest || $contest->num_rows() == 0) {
                        $this->error_out($job->id, '["Invalid contest supplied"]');
                        $continue = false;
                        continue;
                    }
                    $contest                     = $contest->row();
                    $this->email_data['company'] = $this->db->select('*')->from('profiles')->where('id', $contest->owner)->get()->row();
                    $this->email_data['contest'] = $contest;
                    break;

                case 'submission_confirmation':
                    $submission = $this->db->select('*')->from('submsssions')->where('id', $job->object_id)->get();
                    if (!$submission || $submission->num_rows() == 0) {
                        $this->error_out($job->id, '["Invalid submission supplied"]');
                        $continue = false;
                        continue;
                    }
                    $submission->contest             = $this->db->select('*')->from('contests')->join('profiles', 'contests.owner = profiles.id', 'left')->where('contests.id', $submission->contest_id)->get()->row();
                    $this->email_data['submissions'] = $submission;
                    break;

                case 'company_sign_up_conf':
                    $company = $this->db->select('*')->from('profiles')->where('id', $job->object_id)->get();
                    if (!$company || $company->num_rows() == 0) {
                        $this->error_out($job->id, '["Invalid company supplied"]');
                        $continue = false;
                        continue;
                    }
                    $this->email_data['company'] = $company->row();
                    break;

                case 'submission_chosen':
                    $contest = $this->db->select('*')->from('contests')->where('id', $job->object_id)->get();
                    if (!$contest || $contest->num_rows() == 0) {
                        $this->error_out($job->id, '["Invalid contest supplied"]');
                        $continue = false;
                        continue;
                    }
                    $contest                     = $contest->row();
                    $contest->owner              = $this->db->select('*')->from('profiles')->where('id', $contest->owner)->get()->row();
                    $subject                     = sprintf($this->email_config[$job->email_type]['subject'], is_null($contest->owner->name) ? "They " : $contest->owner->name);
                    $this->email_data['contest'] = $contest;
                    $this->email_data['company'] = $contest->owner;
                    break;

                case 'contest_receipt':
                    $this->load->library('stripe/stripe_charge_library');
                    $contest = $this->db->select('*')->from('contests')->where('id', $job->object_id)->get();
                    if (!$contest || $contest->num_rows() == 0) {
                        $this->error_out($job->id, '["Invalid contest supplied"]');
                        $continue = false;
                        continue;
                    }
                    $contest                     = $contest->row();
                    $this->email_data['contest'] = $contest;
                    $this->email_data['voucher'] = false;
                    $this->email_data['charge']  = false;
                    $voucher                     = $this->db->select('*')->from("voucher_uses")->where('contest_id', $contest->id)->get()->row();

                    if (!empty($voucher)) {
                        $this->email_data['voucher'] = $this->db->select('*')->from('vouchers')->where('id', $voucher->voucher_id)->get()->row();
                    }
                    $charge = $this->db->select('*')->from('stripe_charges')->where('contest_id', $contest->id)->get()->row();
                    if (!empty($charge)) {
                        $this->email_data['charge'] = $this->stripe_charge_library->retrieve($charge->charge_id);
                        if (!$this->email_data['charge']) {
                            $this->error_out($job->id, '["Error fetching charge details from Stripe :: ' . $this->stripe_charge_library->errors() . '"]');
                            $continue = false;
                            continue;
                        }
                    }
                    $this->email_data['company'] = $this->db->select('*')->from('profiles')->where('id', $contest->owner)->get()->row();
                    break;

                case 'payout_receipt':
                    $payout = $this->db->select('*')->from('payouts')->where('id', $job->object_id)->get();
                    if (!$payout || $payout->num_rows() == 0) {
                        $this->error_out($job->id, '["Invalid payout supplied"]');
                        $continue = false;
                        continue;
                    }
                    $payout                     = $payout->row();
                    $this->email_data['payout'] = $payout;
                    break;

                case 'ab_test':
                    $ad = $this->db->select('*')->from('ads')->where('contest_id', $job->object_id)->limit(1)->get();
                    if (!$ad || $ad->num_rows() == 0) {
                        $this->error_out($job->id, '["Invalid ad supplied"]');
                        $continue = false;
                        continue;
                    }
                    $ad                          = $ad->row();
                    $ad->content                 = unserialize($ad->content);
                    $subject                     = 'contest#' . $ad->contest_id . ' paid ' . $ad->content['price'] . ' amount for a/b test';
                    $this->email_data['ad']      = $ad;
                    $this->email_data['subject'] = $subject;
                    break;

                case 'pending_purchase':
                    $contest = $this->db->select('*')->from('contests')->where('id', $job->object_id)->get();
                    if (!$contest || $contest->num_rows() == 0) {
                        $this->error_out($job->id, '["Invalid contest supplied"]');
                        $continue = false;
                        continue;
                    }
                    $contest                     = $contest->row();
                    $contest->owner              = $this->db->select('*')->from('profiles')->where('id', $contest->owner)->get()->row();
                    $this->email_data['contest'] = $contest;
                    $this->email_data['company'] = $contest->owner;
                    break;

                default:
                    $this->error_out($job->id, '["Invalid email type ' . $job->email_type . ' supplied"]');
                    $continue = false;
                    continue;

            }
            if (!$continue) {
                continue;
            }

            // Set base data for every email
            foreach ($this->email_config[$job->email_type]['additional_data'] as $key => $value) {
                $this->email_data[$key] = $value;
            }
            $this->email_data['query_string']        = $this->email_config[$job->email_type]['query_string'];
            $this->email_data['query_string']['eid'] = $job->id;
            if (!$subject) {
                $subject = $this->email_config[$job->email_type]['subject'];
            }

            // Generate our html email based on template and our
            try {
                $generated_html = $this->load->view($this->email_config[$job->email_type]['template'], $this->email_data, true);
            } catch (Exception $e) {
                $this->error_out($job->id, '["Could not generate email html body::' . $e->getMessage() . '"]');
                continue;
            }
            if (!$generated_html) {
                $this->error_out($job->id, '["Template missing from requested location"]');
                continue;
            }
            //var_dump($subject, $generated_html);
            //die();
            // Clean up before we try and send the email
            $this->email_data = array();
            /**
             * Now we actually send the email using our generated stuff
             */
            $this->mailer->to($job->recipient)
                ->from($this->email_config[$job->email_type]['from'])
                ->subject($subject)
                ->html($generated_html);
            if (!is_null($attachment)) {
                $tmp_file = tempnam(sys_get_temp_dir(), uniqid()) . '.jpg';
                error_log($tmp_file);
                // Download and create the file.
                file_put_contents($tmp_file, file_get_contents($attachment));
                // Tell SG were atttaching a file
                $this->mailer->attach($tmp_file);
            }
            if ($this->mailer->send()) {
                $this->db->where('id', $job->id)->update('mailing_queue', array(
                    'sent_at' => time(),
                ));
            } else {
                $this->db->where('id', $job->id)->update('mailing_queue', array(
                    'failure_reason' => $this->mailer->errors(),
                ));
            }
            if (!is_null($attachment)) {
                unlink($tmp_file);
            }
        }
    }

    /**
     * Run every hour, and find contests that have recently ended
     * @return [type] [description]
     */
    public function find_recently_closed_contests()
    {
        $contests = $this->db->select('*')->from('contests')->where(array(
            'DATE(stop_time)' => date('Y-m-d'),
            'HOUR(stop_time)' => date('H', strtotime('-1 hour')),
        ))->get();
        foreach ($contests->result() as $contest) {
            $owner = $this->db->select('*')->from('users')->where('id', $contest->owner)->get()->row();
            $this->mailer->queue($owner->email, $owner->id, 'contest_completed', 'contest', $contest->id);
        }
    }

    public function error_out($id, $error)
    {
        $this->db->where('id', $id)->update('mailing_queue', array('failure_reason' => $error));
    }
}
