<?php  defined("BASEPATH") or exit("No direct script access allowed");

class User extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'users';
        $this->order_by = 'users.id';
        $this->order_dir = 'desc';
        $this->load->database();
    }

    public function __get($var)
    {
        return get_instance()->$var;
    }

    /**
     * Get the profile of a user
     * @param  integer $uid
     * @return object
     */
    public function profile($uid)
    {
        $profile = $this->db->select('*')->from('profiles')->where('id', $uid)->limit(1)->get();
        if($profile !== FALSE)
        {
            return $profile->row();
        }
        return FALSE;
    }

    /**
     * Save a user profile
     * @param  integer $uid
     * @param  array $data
     * @return boolean
     */
    public function saveProfile($uid, $data)
    {
        $check = $this->db->select('*')->from('profiles')->where('id', $uid)->limit(1)->get();
        if($check !== FALSE)
        {
            if($check->num_rows() > 0)
            {
                // Update
                if($this->db->where('id', $uid)->update('profiles', $data))
                {
                    return TRUE;
                } else {
                    die(json_encode($this->db->error()));
                }
            }
            else
            {
                $data['id'] = $uid;
                if($this->db->insert('profiles', $data))
                {
                    return TRUE;
                } else {
                    die(json_encode($this->db->error()));
                }
            }
        }
        return FALSE;
    }

    public function account($uid)
    {
        $account = $this->db->select('*')->from('stripe_accounts')->where('user_id', $uid)->limit(1)->get();
        if($account && $account->num_rows() == 1)
        {
            return $account->row()->account_id;
        }
        return false;
    }

    /**
     * Get count of users
     * @param array $where
     * @param array $like
     * @return integer  Count of rows given params
     */
    public function count($where = array(), $like = array())
    {
        $this->db->select("COUNT(*) as count")->from('users');
        if(!empty($where)) $this->db->where($where);
        if(!empty($like)) $this->db->like($like);

        return (int) $this->db->get()->row()->count;
    }
}
