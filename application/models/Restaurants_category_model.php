<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Restaurants_category_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get()
    {
        $result = $this->db->query('this information can\'t be shared');
        $result_array = $result->result_array();

        return json_encode($result_array);

    }

}

?>