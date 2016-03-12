<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->library(array('ion_auth','form_validation'));
		$this->load->helper(array('url','language'));
		$this->load->library('mailer');

		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
		$this->lang->load('auth');
	}

	/**
	 * Check if a user is logged in
	 * If they are return their ajax_user() data
	 * @return mixed
	 */
	function is_logged_in()
	{
		if($this->ion_auth->logged_in())
		{
			$this->responder->data(
				$this->ion_auth->ajax_user()
			)->message($this->session->flashdata('message'))->respond();
		} else {
			$this->responder->fail($this->session->flashdata('error'))->code(401)->respond();
		}
	}

	function resend_verification()
	{
		if(!$this->ion_auth->logged_in())
		{
			$this->responder->fail("You need to be logged in")->code(403)->respond();
			return;
		}
		$user = $this->ion_auth->user()->row();
		if($user->active == 1)
		{
			$this->responder->message("Your account has already been verified")->respond();
			return;
		}
		$data = array(
			'identity'   => $user->email,
			'id'         => $user->id,
			'email'      => $user->email,
			'activation' => $user->activation_code,
		);

		$message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('email_activate', 'ion_auth'), $data, true);

		$this->mailer
				->to($data['identity'])
				->from('Registration@tappyn.com')
				->subject("Account successfully created")
				->html($message);
		if ($this->mailer->send() == TRUE)
		{
			$this->responder->message("Verification email successfully resent")->respond();
			return;
		} else {
			$this->responder->fail("There was an error sending your verification email")->code(500)->respond();
			return;
		}
	}

	/**
	 * Facebook login endpoint
	 * @return void
	 */
	function facebook()
	{
		$this->email_activation = FALSE;
		//$this->ion_auth->logout();
		$this->load->library('facebook_ion_auth');

		if($this->facebook_ion_auth->login())
		{
			// User has successfully logged in
			redirect('#/dashboard', 'refresh');
		} else {
			$this->session->set_flashdata('error', $this->facebook_ion_auth->errors());
			redirect('/', 'refresh');
		}
	}

	/**
	 * Login
	 * @return void
	 */
	function login()
	{
		//$this->ion_auth->logout();
		//validate form input
		$this->form_validation->set_rules('identity', 'Identity', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');
		if(!$this->input->post('identity'))
		{
			$this->responder->fail("The Identity field is required")->code(500)->respond();
			return;
		}

		if(!$this->input->post('password'))
		{
			$this->responder->fail("The Password field is required")->code(500)->respond();
			return;
		}
		if ($this->form_validation->run() == true)
		{
			// check to see if the user is logging in
			// check for "remember me"
			$remember = (bool) $this->input->post('remember');

			if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember))
			{
				$this->responder->message("Login successful")->data($this->ion_auth->ajax_user())->respond();
				return;
			}
			else
			{
				$this->responder->fail($this->ion_auth->errors())->code(500)->respond();
				return;
			}
		}
		else
		{
			$this->responder->fail(validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : 'Some random error was encountered'))->code(400)->respond();
		}
	}

	/**
	 * Logout
	 * @return void
	 */
	function logout()
	{
		// log the user out
		$logout = $this->ion_auth->logout();
		$this->responder->message('Logout successful')->respond();
	}

	/**
	 * Change a users password
	 * @return void
	 */
	function change_password()
	{
		$this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
		$this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

		if(!$this->input->post('old'))
		{
			$this->responder->fail("The field Current Password field is required")->code(500)->respond();
			return;
		}
		if(!$this->input->post('new'))
		{
			$this->responder->fail("The New Password field is required")->code(500)->respond();
			return;
		}

		if (!$this->ion_auth->logged_in())
		{
			redirect('auth/login', 'refresh');
		}

		$user = $this->ion_auth->user()->row();

		if ($this->form_validation->run() == false)
		{
			$this->responder->fail(
				(validation_errors() ? validation_errors() : "An unknown error occured")
			)->code(500)->respond();
			return;
		}
		else
		{
			$identity = $this->session->userdata('identity');

			$change = $this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'));

			if ($change)
			{
				$this->responder->message(
					"Password successfully updated"
				)->respond();
			}
			else
			{
				$this->responder->fail(
					$this->ion_auth->errors()
				)->code(500)->respond();
			}
		}
	}

	/**
	 * Request to reset a password
	 * @return void
	 */
	function forgot_password()
	{
		// setting validation rules by checking wheather identity is username or email
		if($this->config->item('identity', 'ion_auth') != 'email' )
		{
		   $this->form_validation->set_rules('identity', $this->lang->line('forgot_password_identity_label'), 'required');
		}
		else
		{
		   $this->form_validation->set_rules('identity', $this->lang->line('forgot_password_validation_email_label'), 'required|valid_email');
		}


		if ($this->form_validation->run() == false)
		{
			$this->data['type'] = $this->config->item('identity','ion_auth');
			// setup the input
			$this->data['identity'] = array('name' => 'identity',
				'id' => 'identity',
			);

			if ( $this->config->item('identity', 'ion_auth') != 'email' ){
				$this->data['identity_label'] = $this->lang->line('forgot_password_identity_label');
			}
			else
			{
				$this->data['identity_label'] = $this->lang->line('forgot_password_email_identity_label');
			}

			$this->responder->fail((validation_errors() ? validation_errors() : $this->session->flashdata('error')))->code(500)->respond();
			return;
		}
		else
		{
			$identity_column = $this->config->item('identity','ion_auth');
			$identity = $this->ion_auth->where($identity_column, $this->input->post('identity'))->users()->row();

			if(empty($identity)) {

        		if($this->config->item('identity', 'ion_auth') != 'email')
            	{
            		$this->ion_auth->set_error('forgot_password_identity_not_found');
            	}
            	else
            	{
            	   $this->ion_auth->set_error('forgot_password_email_not_found');
            	}

				$this->responder->fail(($this->ion_auth->errors() ? $this->ion_auth->errors() : "An unknown error occured"))->code(500)->respond();
				return;
    		}

			// run the forgotten password method to email an activation code to the user
			$forgotten = $this->ion_auth->forgotten_password($identity->{$this->config->item('identity', 'ion_auth')});

			if ($forgotten)
			{
				$this->responder->message("Password successfully reset. An email has been sent with instructions")->respond();
				return;
			}
			else
			{
				$this->responder->fail(($this->ion_auth->errors() ? $this->ion_auth->errors() : "An unknown error occured"))->code(500)->respond();
				return;
			}
		}
	}

	/**
	 * Resetting a password
	 * @param string $code Generated Forgotten password code
	 * @return void
	 */
	public function reset_password($code = NULL)
	{
		if (!$code)
		{
			$this->responder->fail("Necessary fields were missing")->code(500)->respond();
			return;
		}

		$user = $this->ion_auth->forgotten_password_check($code);

		if ($user)
		{
			// if the code is valid then display the password reset form

			$this->form_validation->set_rules('new', $this->lang->line('reset_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]');
			$this->form_validation->set_rules('new_confirm', $this->lang->line('reset_password_validation_new_password_confirm_label'), 'required');

			if ($this->form_validation->run() == false)
			{
				$this->responder->data(array(
					'csrf' => $this->_get_csrf_nonce(),
					'user_id' => $user->id
				))->respond();
				return;
			}
			else
			{
				if ($this->_valid_csrf_nonce() === FALSE || $user->id != $this->input->post('user_id'))
				{

					// something fishy might be up
					$this->ion_auth->clear_forgotten_password_code($code);

					$this->responder->fail("Invalid Request")->code(500)->respond();
					return;

				}
				else
				{
					// finally change the password
					$identity = $user->{$this->config->item('identity', 'ion_auth')};

					$change = $this->ion_auth->reset_password($identity, $this->input->post('new'));

					if ($change)
					{
						// if the password was successfully changed
						$this->responder->message("Password successfully updated")->respond();
						return;
					}
					else
					{
						$this->responder->fail(($this->ion_auth->errors() ? $this->ion_auth->errors() : "An unknown error occured"))->code(500)->respond();
					}
				}
			}
		}
		else
		{
			// if the code is invalid then send them back to the forgot password page
			$this->responder->fail(($this->ion_auth->errors() ? $this->ion_auth->errors() : "An unknown error occured"))->code(500)->respond();
			return;
		}
	}

	/**
	 * Activate a users account
	 *
	 * Can be done through email link or admin panel
	 * @param  integer $id   ID of the user
	 * @param  string $code Activation code (required if done through email)
	 * @return void
	 */
	function activate($id, $code=false)
	{
		if ($code !== false)
		{
			$activation = $this->ion_auth->activate($id, $code);
		}
		else if ($this->ion_auth->is_admin())
		{
			$activation = $this->ion_auth->activate($id);
		}
		if ($activation)
		{
			if($this->ion_auth->is_admin())
			{
				$this->session->set_flashdata('message', 'User successfully activated');
				redirect('admin/users/show/'.$id, 'refresh');
			} else {
				redirect("#/dashboard", 'refresh');
			}
		}
		// Error activating yuser
		else
		{
			if($this->ion_auth->is_admin())
			{
				$this->session->set_flashdata('error', $this->ion_auth->errors());
				redirect('admin/users/show/'.$id, 'refresh');
			}
			else
			{
				$this->load->view('auth/errors/failed_activation');
			}
		}
	}

	/**
	 * Deactivate a user
	 * @param  integer $id
	 * @return void
	 */
	function deactivate($id = NULL)
	{
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			// redirect them to the home page because they must be an administrator to view this
			return show_error('You must be an administrator to view this page.');
		}

		$id = (int) $id;

		$this->load->library('form_validation');
		$this->form_validation->set_rules('confirm', $this->lang->line('deactivate_validation_confirm_label'), 'required');
		$this->form_validation->set_rules('id', $this->lang->line('deactivate_validation_user_id_label'), 'required|alpha_numeric');

		if ($this->form_validation->run() == FALSE)
		{
			// insert csrf check
			$this->data['csrf'] = $this->_get_csrf_nonce();
			$this->data['user'] = $this->ion_auth->user($id)->row();

			$this->_render_page('auth/deactivate_user', $this->data);
		}
		else
		{
			// do we really want to deactivate?
			if ($this->input->post('confirm') == 'yes')
			{
				// do we have a valid request?
				if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id'))
				{
					show_error($this->lang->line('error_csrf'));
				}

				// do we have the right userlevel?
				if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin())
				{
					$this->ion_auth->deactivate($id);
				}
			}

			// redirect them back to the auth page
			redirect('auth', 'refresh');
		}
	}

	/**
	 * Create a user
	 * @return void
	 */
	function create_user()
    {
		$this->email_activation = $this->config->item('email_activation', 'ion_auth');
		//$this->ion_auth->logout();
		// Check if they are registering as a guest, which limits the required fields for registration
		$as_guest = false;

        $tables = $this->config->item('tables','ion_auth');
        $identity_column = $this->config->item('identity','ion_auth');
        $this->data['identity_column'] = $identity_column;

        // validate form input
		if($this->input->post('group_id') == 1)
		{
			die('Invalid request');
		}
		if($this->input->post('group_id') == 2)
		{
			$this->form_validation->set_rules('age', 'Age', 'required');
			$this->form_validation->set_rules('gender', 'Gender', 'required');
		} else {
			$this->email_activation = FALSE;
		}
		$this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']');
        $this->form_validation->set_rules('identity', $this->lang->line('create_user_validation_email_label'), 'required|valid_email|is_unique[' . $tables['users'] . '.email]');
		$this->form_validation->set_rules('group_id', 'Group', 'required');

		if(!$this->input->post('identity'))
	    {
		    $this->responder->fail("Email field is required")->code(500)->respond();
	    	return;
	   	}

        if ($this->form_validation->run() == true)
        {
            $email    = strtolower($this->input->post('identity'));
            $identity = ($identity_column==='email') ? $email : $this->input->post('identity');
            $password = $as_guest ? bin2hex(openssl_random_pseudo_bytes(5)) : $this->input->post('password');

			if($this->input->post('group_id') == 2)
			{
				$name_chunks = explode(' ', $this->input->post('name'));
	            $additional_data = array(
	                'first_name' => $name_chunks[0],
	                'last_name'  => (isset($name_chunks[1]) ? $name_chunks[1] : ''),
					'age'		=> ($this->input->post('age') ? $this->input->post('age') : NULL),
					'gender' 	=> ($this->input->post('gender') ? $this->_genderize($this->input->post('gender')) : NULL),
	            );
			} else {
				$additional_data = array(
					'first_name' => $this->input->post('name'),
					'last_name' => ''
				);
			}
        }

        if ($this->form_validation->run() == true && ($id = $this->ion_auth->register($identity, $password, $email, $additional_data, array($this->input->post('group_id')))))
        {
			// Succesful company regstration
			if($this->input->post('group_id') == 3)
			{
				$this->mailer
					->to($email)
					->from("Registration@tappyn.com")
					->subject('Account Successfully Created')
					->html($this->load->view('auth/email/registration', array(), true))
					->send();
				$this->user->saveProfile($id, array('name' => $this->input->post('name'), 'logo_url' => $this->input->post('logo_url'), 'company_url' => $this->input->post('company_url')));
	            if($this->ion_auth->login($identity, $password))
				{
					$this->responder->message('Account successfully created')->data($this->ion_auth->ajax_user())->respond();
				} else {
					$this->responder->fail("Your account was created, but we hit an error logging you in")->code(500)->respond();
				}
				return;
			}
			else
			{
				if($this->ion_auth->login($identity, $password))
				{
					$this->responder->data($this->ion_auth->ajax_user())->message("Account successfully created. Check your email for verification")->respond();
				} else {
					$this->responder->fail("Your account was created, but we hit an error logging you in")->code(500)->respond();
				}
				return;
			}

			// Track the login event
			$this->analytics->track(array(
				'event_name' => 'registration',
				'object_type' => 'user',
				'object_id'  => $id
			));
        }
        else
        {
    		$this->responder->fail(
				(validation_errors() ? validation_errors() : ($this->ion_auth->errors_array() ? $this->ion_auth->errors_array() : "An unknown error occured"))
			)->code(500)->respond();
        }
    }

	function _get_csrf_nonce()
	{
		$this->load->helper('string');
		$key   = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return array($key => $value);
	}

	function _valid_csrf_nonce()
	{
		if ($this->input->post('csrf')[$this->session->flashdata('csrfkey')] !== FALSE &&
			$this->input->post('csrf')[$this->session->flashdata('csrfkey')] == $this->session->flashdata('csrfvalue'))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function _genderize($gender)
	{
		if($gender == 'female') return 2;
		if($gender == 'male') return 1;
		return 0;
	}

	function _render_page($view, $data=null, $returnhtml=false)//I think this makes more sense
	{

		$this->viewdata = (empty($data)) ? $this->data: $data;

		$view_html = $this->load->view($view, $this->viewdata, $returnhtml);

		if ($returnhtml) return $view_html;//This will return html on 3rd argument being true
	}
}
