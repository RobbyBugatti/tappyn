<?php defined("BASEPATH") or exit('No direct script access allowed');

class Stripe_customer_library
{
    protected $errors;
    protected $api_key;
    public function __construct()
    {
        $this->config->load('secrets');
        $this->api_key = $this->config->item('stripe_api_key');
        \Stripe\Stripe::setApiKey($this->api_key);
        $this->load->model('stripe/stripe_customer');
    }

    public function __get($var)
    {
        return get_instance()->$var;
    }

    public function __call($method, $arguments)
    {
        if (!method_exists($this->stripe_customer, $method)) {
            throw new Exception("Call to undefined method Stripe_customer::{$method}()");
        }
        return call_user_func_array(array($this->stripe_customer, $method), $arguments);
    }

    public function charges($args = null)
    {
        try {
            $charges = \Stripe\Charge::all($args);
        } catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
        return $charges;
    }

    public function create($uid, $token, $email = null)
    {
        try {
            $customer = \Stripe\Customer::create(array(
                "email"       => $email,
                "description" => "Customer for {$uid}",
                "source"      => $token,
            ));
        } catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
        if ($this->save($uid, $customer)) {
            return $customer;
        }
        return false;
    }

    public function update($uid, $cid, $data)
    {
        $customer = $this->fetch($cid);
        if (!$customer) {
            return false;
        }
        foreach ($data as $key => $value) {
            $customer->$key = $value;
        }
        try {
            $customer->save();
        } catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }

        if ($this->save($uid, $customer)) {
            return $customer;
        }
        return false;
    }
    public function addPaymentSource($cid, $token)
    {
        $customer = $this->fetch($cid);
        if (!$customer) {
            return false;
        }

        try {
            $customer->sources->create(array('source' => $token));
        } catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
        return true;
    }

    public function fetch($cid)
    {
        try {
            $customer = \Stripe\Customer::retrieve($cid);
        } catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
        return $customer;
    }

    public function errors()
    {
        return $this->errors;
    }
}
