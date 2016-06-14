<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Prayerspot extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('prayerspot_model');
    }

    public function prayerspot_list()
    {
        echo $this->prayerspot_model->get_list();

    }

}

?>