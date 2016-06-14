<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Faq_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

//    public function get_content($id)
//    {
//
//        $result = $this->db->query('select * from halalkorea.faq ' .
//            'where `id` = ' . $id);
//
//        if ($result) {
//            $response = array();
//            $response['code'] = 200;
//            $response['message'] = 'Success';
//            $result_array = $result->result_array();
//            $response = array_merge($response, $result_array[0]);
//            echo json_encode($response);
//        } else {
//            $response = array();
//            $response['code'] = 500;
//            $response['message'] = 'Internal Server Error';
//            $response['result'] = null;
//            echo json_encode($response);
//        }
//    }

    public function get_list()
    {

        // $offset = $page * 10;

        $result = $this->db->query('select * ' .
            'from halalkorea.faq ' .
            'order by `id` desc');

        if ($result) {
            $response = array();
            $response['code'] = 200;
            $response['message'] = 'Success';
            $response['result'] = $result->result_array();
            return json_encode($response);
        } else {
            $response = array();
            $response['code'] = 500;
            $response['message'] = 'Internal Server Error';
            $response['result'] = null;
            return json_encode($response);
        }

    }

//    public function get_recent_list($most_recent_checked_id){
//
//
//        //   $offset = $ * 10;
//
//        $result = $this->db->query('select * ' .
//            'from halalkorea.faq ' .
//            'where `id` > '. $most_recent_checked_id . ' order by `id` desc limit 10');
//
//        if ($result) {
//            $response = array();
//            $response['code'] = 200;
//            $response['message'] = 'Success';
//            $response['result'] = $result->result_array();
//            echo json_encode($response);
//        } else {
//            $response = array();
//            $response['code'] = 500;
//            $response['message'] = 'Internal Server Error';
//            $response['result'] = null;
//            echo json_encode($response);
//        }
//
//
//
//    }


}