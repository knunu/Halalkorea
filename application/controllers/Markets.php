<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Markets extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('markets_category_model');
    }

    public function category()
    {
        echo $this->markets_category_model->get();

    }

}

?>