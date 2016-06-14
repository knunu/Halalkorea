<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require 'aws.php';

class Prayerspot_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_list()
    {
        $result = $this->db->query('this information can\'t be shared');
        $result_array = $result->result_array();

        foreach ($result_array as $key => $value) {

            if ($result_array[$key]['image_url'])
                $result_array[$key]['image_url'] = getURLFromS3('uploads', $result_array[$key]['image_url']);
            else
                $result_array[$key]['image_url'] = '';

        }

        //echo getURLFromS3('10075143875627546817.jpg');
        //
        return json_encode($result_array);

    }

}

?>