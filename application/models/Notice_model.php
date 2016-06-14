<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Notice_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }


    public function get_list()
    {

        // $offset = $page * 10;

        $result = $this->db->query('this information can\'t be shared');

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

    public function get_recent_list($most_recent_checked_id)
    {


        //   $offset = $ * 10;

        $result = $this->db->query('this information can\'t be shared');

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


}

?>
