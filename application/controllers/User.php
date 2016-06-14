<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->library('user');
        $this->load->library('form_validation');
    }

    public function register()
    {
        $result = array();

        $rules = array(
            array('field' => 'email', 'label' => 'Email', 'rules' => 'trim|required|valid_email'),
            array('field' => 'name', 'label' => 'User name', 'rules' => 'trim|required|min_length[4]|max_length[20]'),
            array('field' => 'password', 'label' => 'Password', 'rules' => 'trim|required|min_length[8]'),
        );
        $this->form_validation->set_rules($rules);

        $email = $this->input->post('email');
        $name = $this->input->post('name');
        $password = $this->input->post('password');

        if ($this->user_model->check_duplication($email)) {
            //if ($this->user_model>register_user($session_id ,$email, $name, $password, $login_flag)) {
            if ($this->user_model->register_user($email, $name, $password)) {
                if ($this->user_model->send_email($email, 'CERT', $name)) {
                    $result['code'] = '200';
                    $result['message'] = 'success!';
                } else {
                    $result['code'] = '430';
                    $result['message'] = 'failed to send the email!';
                }
            } else {
                $result['code'] = '400';
                $result['message'] = 'register failed by unknown reason.';
            }
        } else {
            $result['code'] = '410';
            $result['message'] = 'your entered duplicated email or wrong email.';
        }

        echo json_encode($result);
    }

    public function user_info()
    {
        $this->user_model->get_user_info();
    }

    public function user_photo()                                                                                               
    {   
        $full_path = '';

        $config = array();
        $config['upload_path'] = 'upload path can\'t be shared';
        $config['allowed_types'] = 'gif|jpg|jpeg|png';                                                                         
        $config['max_size'] = '3072';                                                                                          
        $config['encrypt_name'] = TRUE;                                                                                        
        $this->load->library('upload', $config);                                                                               
    
        if (!$this->upload->do_upload('user_photo')) {                                                                         
            $full_path = '';                                                                                                   
        } else {                                                                                                               
            $full_path = $this->upload->data('full_path');                                                                     
        }                                                                                                                      
        
        $this->user_model->set_user_photo($full_path);                                                                         
    }   

    public function user_name()
    {
        $this->user_model->set_user_name();
    }

    public function modify()
    {
        $this->user_model->modify_user();
    }

    public function withdraw()
    {
        $this->user_model->withdraw_user();
        /*
        $result = array();

        $email = $this->input->post('email');

        if ($this->user_model->withdraw_user($email)) {
            $result['code'] = '200';
            $result['message'] = 'success!';
        } else {
            $result['code'] = '400';
            $result['message'] = 'your account already deleted.';
        }

        echo json_encode($result);
        */
    }

    public function login()
    {
        $session_id = $this->input->post('session_id');
        $email = $this->input->post('email');
        $name = $this->input->post('name');
        $password = $this->input->post('password');
        $login_flag = $this->input->post('login_flag');

        if ($login_flag == 'H' || $login_flag == '') {
            $this->user_model->get_user($session_id, $email, $password);
        } else if ($login_flag == 'F' || $login_flag == 'G') {
            // first login, we should register first, and then call get_user
            if ($this->user_model->check_duplication($email, $login_flag)) {
                // if register failed
                if (!$this->user_model->register_user($email, $name, $password, $session_id, $login_flag)) {
                    echo json_encode(array('code' => '410', 'message' => 'your email is already registered.'));
                    return;
                }
            }

            // Non-first login or after register, just call get_user
            $this->user_model->get_user($session_id, $email, $password, $login_flag);
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
    }

    public function check_email($type = '', $param_email = '', $login_flag = 'H')
    {
        if ($param_email == '') $email = $this->input->post('email');
        else $email = $param_email;

        if ($type == '') {
            echo "check_email function's parameter(type) is NULL!";
            return false;
        } else if ($type == 'cert') {
            $this->user_model->verify_email($email);
        } else if ($type == 'dup') {
            $this->user_model->check_duplication($email, $login_flag);
        } else if ($type == 'reset') {
            $this->load->view('reset_password', array('email' => $email));
        }
    }

    public function reset_password()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        
        if ($password == '') {
            if ($this->user_model->send_email($email, 'RESET')) {
                echo json_encode(array('code' => '200'));
            } else {
                echo json_encode(array('code' => '400', 'message' => 'failed to send email!'));
            }
        } else {
            $this->user_model->reset_password($email, $password);
        }
    }

}
