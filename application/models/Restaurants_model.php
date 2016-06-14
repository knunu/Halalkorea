<?php
/**
 * Created by PhpStorm.
 * User: Purple
 * Date: 2015. 11. 16.
 * Time: 오전 10:01
 */

class Restaurants_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($category_id)
    {
        $result = $this->db->query('this information can\'t be shared');
        $result_array = $result->result_array();

        return json_encode($result_array);

    }

}