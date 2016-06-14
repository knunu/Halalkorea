<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Notice extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('notice_model');
    }
//
//    public function content()
//    {
//        $index = $this->input->post('index');
//
//        if (!is_numeric($index) || $index < 0) {
//            $response = array();
//            $response['code'] = 400;
//            $response['message'] = 'Invalid Input';
//            $response['result'] = null;
//            echo json_encode($response);
//
//        } else {
//
//            $this->notice_model->get_content($index);
//        }
//
//
//    }

    public function notice_list()
    {

//        $page = $this->input->post('page');
//
//        if (!is_numeric($page) || $page < 0) {
//
//            $response = array();
//            $response['code'] = 400;
//            $response['message'] = 'Invalid Input';
//            $response['result'] = null;
//            echo json_encode($response);
//
//        } else {

        //  echo 'hello';
        echo $this->notice_model->get_list();

        // }
    }

    public function notice_recent_list()
    {

        $most_recent_checked_id = $this->input->post('most_recent_checked_id');


        if (!is_numeric($most_recent_checked_id) || $most_recent_checked_id < 0) {

            $response = array();
            $response['code'] = 400;
            $response['message'] = 'Invalid Input';
            $response['result'] = null;
            echo json_encode($response);

        } else {

            echo $this->notice_model->get_recent_list($most_recent_checked_id);

        }

    }

}



