<?php defined("BASEPATH") or exit('No direct script access allowed');

class Contests extends CI_Controller
{
    protected $params = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->model('contest');
        $this->load->model('submission');
        $this->load->library('submission_library');
        $this->load->library('mailer');
        $this->load->model('user');
    }

    /**
     * View all available contests
     * @return void
     */
    public function index()
    {
        $this->params = array(
            'start_time <' => date('Y-m-d H:i:s'),
            'stop_time >' => date('Y-m-d H:i:s'),
            'paid' => 1
        );

        if($this->input->get('industry')) $this->params['industry'] = $this->input->get('industry');
        $config['base_url'] = base_url().'contests/index';
        $config['total_rows'] = $this->contest->count($this->params);
        $config['per_page'] = 20;
        $this->pagination->initialize($config);
        $limit = $config['per_page'];
        $offset = $this->uri->segment(3) ? $this->uri->segment(3) : 0;
        $contests = $this->contest->fetchAll($this->params, 'start_time', 'desc', $limit, $offset);

        if($contests !== FALSE)
        {
            $this->responder->data($contests)->respond();
        } else {
            $this->responder->fail("An unknown error occured")->code(500)->respond();
        }
    }

    public function leaderboard()
    {
        $this->params = array(
            'start_time <' => date('Y-m-d H:i:s'),
            'stop_time >' => date('Y-m-d H:i:s'),
            'paid' => 1
        );

        if(!$this->contest->where($this->params)->fetch())
        {
            $this->responder->fail("Server Error Occured")->code(500)->respond();
            return;
        }
        $contests = $this->contest->result();

        foreach($contests as $contest)
        {
            $contest->votes = $this->vote->select('COUNT(*) as count')->where('contest_id', $contest->id)->fetch()->row()->count;
        }

        usort($contests, function($a, $b)
            {
                return strcmp($b->votes, $a->votes);
            }
        );
        $this->responder->data(array('contests' => array_slice($contests, 0, 5)))->respond();
    }

    /**
     * Fetch a single contest
     *
     * Also, we log the impression so we can track views
     * @param  integer $id
     * @return void
     */
    public function show($cid)
    {
        $contest = $this->contest->get($cid);

        if(!$contest)
        {
            $this->responder->fail(
                "That contest does not exist"
            )->code(404)->respond();
        } else {
            $contest->views = $this->contest->views($cid);
            $this->responder->data(array(
                'contest' => $contest
            ))->respond();
            $this->contest->log_impression($cid);
        }
        $this->analytics->track(array(
            'event_name' => 'view_contest',
            'object_type' => 'contest',
            'object_id'  => $cid
        ));
    }

    /**
     * Create a new contest, or render the creation form
     * @return void
     */
    public function create($id = null)
    {
        if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group(3))
        {
            $this->responder->fail("You need to be logged in as a company to create contests")->code(403)->respond();
            return;
        }

        $this->form_validation->set_rules('audience', 'Audience Description', 'required');
        $this->form_validation->set_rules('different', 'How Your Different', 'required');
        $this->form_validation->set_rules('objective', 'Objective', 'required');
        $this->form_validation->set_rules('platform', 'Format', 'required');
        $this->form_validation->set_rules('summary', 'Summary', 'required');

        if($this->form_validation->run() == true)
        {
            $start_time = ($this->input->post('start_time') ? $this->input->post('start_time') : date('Y-m-d H:i:s'));
            // Do some preliminary formatting
            $data = array(
                'audience'          => $this->input->post('audience'),
                'summary'           => $this->input->post('summary'),
                'different'         => $this->input->post('different'),
                'objective'         => $this->input->post('objective'),
                'platform'          => $this->input->post('platform'),
                'age'               => $this->input->post('age'),
                'gender'            => $this->input->post('gender'),
                'owner'             => $this->ion_auth->user()->row()->id,
                'industry'          => $this->input->post('industry'),
                'start_time'        => $start_time,
                'stop_time'         => date('Y-m-d H:i:s', strtotime('+7 days'))
            );
            $images = array();
            if($this->input->post('additional_image_1')); $images[] = $this->input->post('additional_image_1');
            if($this->input->post('additional_image_2')); $images[] = $this->input->post('additional_image_2');
            if($this->input->post('additional_image_3')); $images[] = $this->input->post('additional_image_3');
            if(!empty($images)) $data['additional_images'] = json_encode($images);
            if(is_null($id))
            {
                $cid = $this->contest->create($data);
            } else {
                $cid = $this->contest->update($id, $data);
            }
        }
        if($this->form_validation->run() == true && $cid)
        {
            $this->responder->message($this->contest->messages())->data(array('id' => $cid))->respond();
            $this->analytics->track(array(
                'event_name' => "contest_creation",
                'object_type' => "contest",
                'object_id' => $cid
            ));
            $profile_data = array();
            $profile = $this->ion_auth->profile();
            if(is_null($profile->mission)) $profile_data['mission'] = $this->input->post('audience');
            if(is_null($profile->extra_info)) error_log("ExtraInfo null");
            if(is_null($profile->different)) $profile_data['different'] = $this->input->post('different');
            if(is_null($profile->summary)) $profile_data['summary'] = $this->input->post('summary');
            if(is_null($profile->company_email)) $profile_data['company_email'] = $this->input->post('company_email');
            if(is_null($profile->company_url)) $profile_data['company_url'] = $this->input->post('company_url');
            if(!empty($profile_data))
            {
                $this->user->saveProfile($this->ion_auth->user()->row()->id, $profile_data);
            }
        }
        else
        {
            $this->responder->fail(
                (validation_errors() ? validation_errors() : ($this->contest->errors() ? $this->contest->errors() : 'An unknown error occured'))
            )->code(500)->respond();
        }
    }

    /**
     * Set a submission as the winner of the contest
     * @param  integer $cid Contest ID
     * @param  integer $sid Submission ID
     * @return void
     */
    public function select_winner($cid)
    {
        $this->load->library('payout');
        $this->load->model('user');
        $sid = $this->input->post('submission');

        if(!$this->ion_auth->logged_in())
        {
            $this->responder->fail("You must be logged in to perform this action")->code(401)->respond();
            return;
        }
        // Check that submission exists
        if(!$submission = $this->submission->get($sid))
        {
            $this->responder->fail("That submission does not exist")->code(500)->respond();
            return;
        }
        // Check that contest exists
        else if(!$contest = $this->contest->get($cid))
        {
            $this->responder->fail("We couldn't find contest with id {$cid}")->code(500)->respond();
            return;
        }
        // Check that the contest has ended
        else if($contest->stop_time > date('Y-m-d H:i:s'))
        {
            $this->responder->fail("This contest has not finished yet")->code(500)->respond();
            return;
        }
        $company_name = $this->db->select('name')->from('profiles')->where("id", $contest->owner)->get();
        if($company_name)
        {
            $company_name = $company_name->row()->name;
        } else {
            $company_name = '';
        }
        // Check that we are admin or the ccontest owner
        if(!$this->ion_auth->user()->row()->id !== $contest->owner)
        {
            if(!$this->ion_auth->is_admin())
            {
                $this->responder->fail('You must own the contest to select a winner')->code(403)->respond();
                return;
            }
        }
        $payout = $this->payout->exists(array('contest_id' => $cid));
        if($payout)
        {
            $this->responder->fail("A submission has already been chosen as the winner")->code(500)->respond();
            return;
        }
        // Attempt to create the payouts
        if($pid = $this->payout->create($cid, $sid))
        {
            // Send the email congratulating the user

            // Tell the company they have successfully selected a winner!
            $this->responder->message(
                "A winner has been chosen!"
            )->respond();
            $this->user->attribute_points($submission->owner, $this->config->item('points_per_winning_submission'));
            $this->load->library('vote');
            $this->vote->dole_out_points($submission->id);
            $eid = $this->mailer->id($this->ion_auth->user()->row()->email, 'submission_chosen');
            $this->mailer
                ->to($this->ion_auth->user($submission->owner)->row()->email)
                ->from("squad@tappyn.com")
                ->subject("Congratulations, you're submission won!")
                ->html($this->load->view('emails/submission_chosen', array('company' => $company_name, 'eid' => $eid), TRUE))
                ->send();
                $this->analytics->track(array(
                    'event_name' => "winner_selected",
                    'object_type' => "contest",
                    'object_id' => $cid
                ));
            return;
        }
        else
        {
            $this->responder->fail(
                $this->payout->errors() ? $this->payout->errors() : "An unknown error occured"
            )->code(500)->respond();
            return;
        }
    }

    /**
     * [update description]
     * @param  integer $cid ID of the contest to update
     * @return void
     */
    public function update($cid = NULL)
    {

    }
}
