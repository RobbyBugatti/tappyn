<?php defined("BASEPATH") or exit('No direct script access allowed');

class Payout
{
    protected $errors = FALSE;

    public function __construct()
    {

    }

    public function __get($var)
    {
        return get_instance()->$var;
    }

    /**
     * Create a payout for a contest
     * @param  integer $cid Contest to payout for
     * @param  integer $sid Submission chosen
     * @return boolean
     */
    public function create($cid, $sid)
    {
        $contest = $this->db->select('*')->from('contests')->where('id', $cid)->limit(1)->get()->row();
        $submission = $this->db->select('*')->from('submissions')->where('id', $sid)->limit(1)->get()->row();
        $insert_data = array(
            'created_at' => time(),
            'contest_id' => $cid,
            'submission_id' => $sid,
            'claimed' => 0,
            'pending' => 1,
            'user_id' => $submission->owner,
            'amount' => 5000
        );
        return $this->db->insert('payouts', $insert_data);
    }

    public function exists($params)
    {
        $this->db->select('*')->from('payouts');
        if(!empty($params)) $this->db->where($params);
        $payout = $this->db->get();
        if($payout && $payout->num_rows() > 0)
        {
            return $payout->row();
        }
        return FALSE;
    }
    public function get($pid)
    {
        $payout = $this->db->select('*')->from('payouts')->where('id', $pid)->get();
        if($payout && $payout->num_rows() == 1)
        {
            $payout = $payout->row();
            $payout->account = $this->db->select('*')->from('stripe_accounts')->where('account_id', $payout->account_id)->limit(1)->get()->row();
            $payout->transfer = $this->db->select('*')->from('stripe_transfers')->where('transfer_id', $payout->transfer_id)->limit(1)->get()->row();
            $payout->contest = $this->db->select('*')->from('contests')->where('id', $payout->contest_id)->limit(1)->get()->row();
            $payout->submission = $this->db->select('*')->from('submissions')->where('id', $payout->submission_id)->limit(1)->get()->row();
            return $payout;
        }
        return FALSE;
    }

    public function fetch($params)
    {
        $payouts = $this->db->select('*')->from('payouts')->where($params)->get();
        if($payouts)
        {
            $payouts = $payouts->result();
            foreach($payouts as $payout)
            {
                $payout->contest = $this->db->select('*')->from('contests')->where('id', $payout->contest_id)->limit(1)->get()->row();
                $payout->submission = $this->db->select('*')->from('submissions')->where('id', $payout->submission_id)->limit(1)->get()->row();
            }
            return $payouts;
        }
        return FALSE;
    }

    public function update($id, $data)
    {
        return $this->db->where('id', $id)->update('payouts', $data);
    }

    public function errors()
    {
        return $this->errors;
    }
}
