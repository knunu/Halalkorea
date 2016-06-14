<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('encryption');
    }

    public function register_user($email, $name, $password, $session_id = '0', $login_flag = 'H')
    {
        #$photo_path = '52.68.136.59/halalkorea/application/image/user/';

        $data = 'this information can\'t be shared';

        $insert_query = $this->db->insert_string('this information can\'t be shared', $data);
        $insert_query = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $insert_query);
        $this->db->query($insert_query);

        if ($this->db->affected_rows() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function get_user_info()
    {
        $result = array();

        /* user_session_id 에 post로 넘어온 session_id를 입력 */
        $user_session_id = $this->input->post('session_id');

        // if session_id is already stored in user (auto login)
        if ($user_session_id != '0')
        {
            $query = $this->db->get_where('this information can\'t be shared', array('session_id' => $user_session_id))->row();

            if ($query != NULL)
            {
                $result['code']  = '200';
                $result['message']  = 'Success';
                $result['name']  = $query->name;
                $result['email']  = $query->email;
                $result['photo']  = $query->photo;
            }

            else
            {
                $result['code'] = '400';
            }
        }
        else
        {
            $result['code'] = '400';
        }

        echo json_encode($result);

    }

    public function set_user_name()
    {   
        $result = array();

        $user_session_id = $this->input->post('session_id');
        $user_name       = $this->input->post('name');

        if ($user_session_id != '0')
        {
            $query = 'this information can\'t be shared';

            $this->db->query($query);

            if ($this->db->affected_rows() == 1)
            {
                $result['code'] = '200';
                $result['message'] = 'success';
            }
            else
            {
                $ressult['code'] = '400';
            }
        }
        else
        {
            $ressult['code'] = '400';
        }

        echo json_encode($result);
    }   

    public function set_user_photo($full_path)
    {
        $result = array();

        $user_session_id = $this->input->post('session_id');

        if ($user_session_id != '0') 
        {
            if ($full_path != '') 
                $query = 'this information can\'t be shared';
            else
                $query = 'this information can\'t be shared';

            $this->db->query($query);

            if ($this->db->affected_rows() == 1) {
                $result['code'] = '200';
                $result['message'] = 'success';
            }
            else
            {
                $ressult['code'] = '400';
            }
        }
        else
        {
            $ressult['code'] = '400';
        }

        echo json_encode($result);
    }

    public function modify_user()
    {
        $result = array();

        /* user_session_id 에 post로 넘어온 session_id를 입력 */
        $user_session_id = $this->input->post('session_id');
        //$result['session_id'] = $user_session_id;

        $cur_password = $this->input->post('cur_password');
        //$result['cur_password'] = $cur_password;

        $new_password = $this->input->post('new_password');
        //$result['new_password'] = $new_password;


        if ($user_session_id != '0')
        {
            $query = $this->db->get_where('this information can\'t be shared', array('session_id' => $user_session_id))->row();

            if ($query != NULL)
            {
                // 패스워드 동일하면
                if (password_verify($cur_password, $query->password))
                {
                    $this->db->where('session_id', $user_session_id);
                    $this->db->update('this information can\'t be shared');

                    $result['code'] = '200';
                    $result['message'] = 'Success';
                }

                else
                {
                    $result['code'] = '420';
                    $result['message'] = 'different password';
                }
            }

            else
            {
                $result['code'] = '400';
            }
        }
        else
        {
            $result['code'] = '400';
        }

        echo json_encode($result);

    }

    //public function withdraw_user($email)
    public function withdraw_user()
    {
        $result = array();

        $user_session_id = $this->input->post('session_id');
        $user_email      = $this->input->post('email');
        $user_password   = $this->input->post('password');

        if ($user_session_id == '0')
        {
            $result['code'] = '400';
            $result['message'] = 'not login user';
        }
        else
        {
            $query = $this->db->get_where('this information can\'t be shared', array('session_id' => $user_session_id))->row();

            if ($query == NULL)
            {
                $result['code'] = '400';
                $result['message'] = 'not found user';
            }
            else
            {
                // 패스워드 동일하면
                if (!password_verify($user_password, $query->password))
                {
                    $result['code'] = '400';
                    $result['message'] = 'different password';
                }

                else
                {
                    if ($this->db->delete('this information can\'t be shared', array('session_id' => $user_session_id)))
                    {
                        $result['code'] = '200';
                        $result['message'] = 'Success';
                    }
                    else
                    {
                        $result['code'] = '400';
                        $result['message'] = 'failed to delete';
                    }
                }
            }
        }

        echo json_encode($result);
        /*
        if ($this->db->delete('basic_user', array('email' => $email))) return true;
        else return false;
        */
    }

    public function get_user($session_id, $email, $password, $login_flag = 'H')
    {
        $result = array();

        if ($login_flag == 'H') {
            $user_session_id = $session_id;
            $this->session->sess_regenerate();
            $new_session_id = $this->session->session_id;

            // if session_id is already stored in user (auto login)
            if ($user_session_id != '0') {
                $this->db->where('session_id', $user_session_id);
                $this->db->update('this information can\'t be shared', array('session_id' => $new_session_id));

                if ($this->db->affected_rows() == 1) {
                    $result['code'] = '200';
                    $result['session_id'] = $new_session_id;
                }
            } else {
                $user = $this->db->get_where('this information can\'t be shared', array('email' => $email, 'login_flag' => $login_flag))->row();

                if ($user != NULL && password_verify($password, $user->password) && $user->pe_validation == 'Y') {
                    // if there is a valid user, create session data
                    $condition = array(
                        'email' => $user->email,
                        'login_flag' => $user->login_flag
                    );
                    $this->db->where($condition);
                    $this->db->update('this information can\'t be shared', array('session_id' => $new_session_id));

                    $result['code'] = '200';
                    $result['session_id'] = $new_session_id;
                } else if ($user->pe_validation == 'N') {
                    $result['code'] = '410';
                    $result['message'] = 'please valid your email!';
                } else {
                    // if there is a invalid user, just return
                    $result['code'] = '420';
                    $result['session_id'] = '0';
                    $result['message'] = 'different password!';
                }
            }
        } else if ($login_flag == 'F' || $login_flag == 'G') {
            $user = $this->db->get_where('this information can\'t be shared', array('email' => $email, 'login_flag' => $login_flag))->row();

            if ($user != NULL) {
                if ($user->session_id != $session_id) {
                    $condition = array(
                        'email' => $user->email,
                        'login_flag' => $user->login_flag
                    );
                    $this->db->where($condition);
                    $this->db->update('this information can\'t be shared', array('session_id' => $session_id));
                }
                $result['code'] = '200';
                $result['session_id'] = $session_id;
            } else {
                $result['code'] = '400';
                $result['session_id'] = '0';
                $result['message'] = 'there is no user';
            }
        }

        echo json_encode($result);
    }

    public function send_email($dest_email, $type, $user_name = '')
    {
        $this->load->library('email');
        $this->email->from('halalkorea@platformstory.com');

        if ($type == 'CERT') {
            $this->email->to($dest_email);
            $this->email->subject("Halalkorea Verification Email");
            $enc_email = 'this information can\'t be shared';
            $this->email->message('this information can\'t be shared');

            if ($this->email->send()) {
                return true;
            } else {
                return false;
            }
        } else if ($type == 'RESET') {
            $this->email->to($dest_email);
            $this->email->subject("Halalkorea Password Reset Email");
            $enc_email = 'this information can\'t be shared';
            $this->email->message('this information can\'t be shared');

            if ($this->email->send()) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function verify_email($email) {
        $dec_email = $this->encryption->decrypt(str_replace(array('-','_','~'), array('+','/','='), $email));
        if ($dec_email != '') {
            $query = $this->db->get_where('basic_user', array('email' => $dec_email));

            if ($query->num_rows()) {
                $this->db->where('email', $dec_email);
                if ($this->db->update('basic_user', array('pe_validation' => 'Y'))) {
                    echo "<script>alert('Verification succeed!');self.close()</script>";
                    return true;
                } else {
                    echo "<script>alert('Verification failed! Contact to administrator');self.close()</script>";
                    return false;
                }
            }
        }
    }

    public function check_duplication($email, $login_flag = 'H')
    {
        if ($email != '') {
            if ($login_flag != '') {
                $query = $this->db->get_where('this information can\'t be shared', array('email' => $email, 'login_flag' => $login_flag));

                if ($query->num_rows() == 0) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function set_profile_picture($image)
    {
        //TODO
    }

    public function reset_password($email, $password) {
        $result = array();
        $condition = array();

        // connect to view and getting new password information from user.
        if ($email != '') {
            $condition['email'] = $this->encryption->decrypt(str_replace(array('-','_','~'), array('+','/','='), $email));
            if ($condition['email'] == false) {
                $condition['email'] = $email;
            }

            if ($password != '') {
                $condition['login_flag'] = 'H';
                $this->db->where($condition);

                if ($this->db->update('this information can\'t be shared')) {
                    echo "<script>alert('Password reset succeed!');self.close()</script>";
                    $result['code'] = '200';
                    $result['message'] = 'password reset succeed!';
                    echo json_encode($result);
                    return true;
                } else {
                    echo "<script>alert('Password reset failed!');</script>";
                    $result['code'] = '400';
                    $result['message'] = 'update failed.';
                }
            } else {
                $result['code'] = '400';
                $result['message'] = 'password is invalid.';
            }
        } else {
            $result['code'] = '400';
            $result['message'] = 'email is invalid.';
        }

        echo json_encode($result);
        return false;
    }
}
