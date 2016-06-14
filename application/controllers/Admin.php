<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Created by PhpStorm.
* User: Knunu
* Date: 2015. 12. 1.
* Time: 오전 10:03
*/

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin_model');
    }

    public function restore_salah_alarm() {
        $this->admin_model->restore('salah_alarm', 'job_queue');
    }
}

?>