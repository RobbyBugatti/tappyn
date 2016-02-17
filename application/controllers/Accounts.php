<?php defined("BASEPATH") or exit('No direct script access allowed');

class Accounts extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if(!$this->ion_auth->logged_in())
        {
            $this->responder->fail(array(
                'error' => "You have to be logged in to access this area"
            ))->code(401)->respond();
            return;
        }

        if(!$this->ion_auth->in_group(2))
        {
            $this->responder->fail(array(
                'error' => "Only content creators can access the accounts panel"
            ))->code(403)->respond();
            return;
        }
        $this->load->model('user');
        $this->load->library('stripe/stripe_account_library');
        $this->stripe_account_id = $this->user->account($this->ion_auth->user()->row()->id);
        if($this->stripe_account_id)
        {
            $this->account = $this->stripe_account_library->get($this->stripe_account_id);
            $this->data['account'] = $this->account;
        }
        $this->config->load('secrets');
    }

    /**
     * Debug current account
     * @return void
     */
    public function debug()
    {
        echo json_encode($this->account);
    }

    /**
     * Endpoint for setting user level account details
     * @return void
     */
    public function details()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $this->form_validation->set_rules('first_name', 'First Name', 'required');
            $this->form_validation->set_rules('last_name', 'Last Name', 'required');
            $this->form_validation->set_rules('dob_day', 'DOB - Day', 'required');
            $this->form_validation->set_rules('dob_month', 'DOB - Month', 'required');
            $this->form_validation->set_rules('dob_year', 'DOB - Year', 'required');
            $this->form_validation->set_rules('country', 'Country', 'required');
            if($this->form_validation->run() === TRUE)
            {
                // Preproces
                $data = array();
                if($this->input->post('first_name')) $data['legal_entity.first_name'] = $this->input->post('first_name');
                if($this->input->post('last_name'))  $data['legal_entity.last_name'] = $this->input->post('last_name');
                if($this->input->post('dob_day'))    $data['legal_entity.dob.day'] = $this->input->post('dob_day');
                if($this->input->post('dob_year'))   $data['legal_entity.dob.year'] = $this->input->post('dob_year');
                if($this->input->post('dob_month'))  $data['legal_entity.dob.month'] = $this->input->post('dob_month');
                if($this->input->post('country'))    $data['country'] = $this->input->post('country');
                if($this->input->post('stripe_tos')) {
                    $data['tos_acceptance.ip'] = $_SERVER['REMOTE_ADDR'];
                    $data['tos_acceptance.date'] = time();
                }
            }
            // If the form pass validation, and we can create / upadte based on presence
            if($this->form_validation->run() === TRUE &&
                ($this->stripe_account_id ?
                    $this->stripe_account_library->update($this->stripe_account_id, $data) :
                    $this->stripe_account_library->create($this->ion_auth->user()->row()->email, $data)))
            {
                // We have successfully created our account, so return the new account details
                $this->responder
                    ->message(
                        'Account details successfully updated'
                    )
                    ->data(array(
                        'account' => $this->stripe_account_library->get($this->stripe_account_id)
                    ))
                    ->respond();
            } else {
                // We tried to run the form, but encountered an errors
                $this->responder->fail(
                    validation_errors() ? validation_errors() : ($this->stripe_account_library->errors() ? $this->stripe_account_library->errors() : "An unknown error occured")
                )->code(500)->respond();
            }
        } else {
            $this->responder->data(array(
                'account' => $this->account
            ))->respond();
        }
    }

    /**
     * Endpoint for managing payment methods
     * @return void
     */
    public function payment_methods()
    {
        if(!$this->stripe_account_id)
        {
            $this->responder->fail(
                'error' => "You have not set up any account details yet"
            )->code(500)->respond();
            return;
        }
        if(!$this->account->transfers_enabled)
        {
            // Check that verification fields is not empty,
            // and that the only required field is the external account
            // else send back to details
            $fields = $this->account->verification->fields_needed;
            $key = array_search('external_account', $fields);
            unset($fields[$key]);
            if(!empty($fields))
            {
                $this->responder->fail(
                    'error' => "You haven't finished filling out your account details"
                )->code(500)->respond();
                return;
            }
        }

        if($this->input->post('stripeToken'))
        {
            if($this->stripe_account_library->addSource($this->stripe_account_id, $this->input->post('stripeToken'), $this->input->post('currency')))
            {
                $this->responder
                    ->message(
                        'Account successfully updated'
                    )
                    ->data(
                        array('account' => $this->stripe_account_library->get($this->stripe_account_id))
                    )
                    ->respond();
                $this->data['message'] = "Account successfully updated";
            } else {
                $this->responder->fail(
                    $this->stripe_account_library->errors() ? $this->stripe_account_library->errors() : "An unknown error occured"
                )->code(500)->respond();
                return;
            }
        } else {
            $this->responder->data(array(
                'account' => $this->stripe_account_library->get($this->stripe_account_id)
            ))->respond();
        }
    }

    /**
     * Remove payment methods
     * @return void
     */
    public function remove_method()
    {
        if($this->input->post('source_id'))
        {
            foreach($this->account->external_accounts->data as $source)
            {
                if($source->id === $this->input->post('source_id'))
                {
                    if($this->stripe_account_library->removeSource($this->stripe_account_id, $source->id))
                    {
                        $this->responder->message(
                            'Payment method successfully removed'
                        )->respond();
                    } else {
                        $this->responder->fail(array(
                            $this->stripe_account_library->errors() ? $this->stripe_account_library->errors() : "An unknown error occured"
                        ))->code(500)->respond();
                    }
                }
            }
        } else {
            $this->responder->fail(array(
                'source_id' => "You must provide a ayment source you want to remove"
            ))->code(400)->respond();
        }
    }
}
