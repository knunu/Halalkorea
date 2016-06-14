<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Mosque extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('mosque_model');
    }

    public function mosque_list()
    {
        echo $this->mosque_model->get_list();

    }

}

?>