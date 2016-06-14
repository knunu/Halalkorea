<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Contact extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('contact_model');
    }

    public function support()
    {
        $email = $this->input->post('email');
        $content = $this->input->post('content');

        echo $this->contact_model->add($email, $content);


    }

}

?>