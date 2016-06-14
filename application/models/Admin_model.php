<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: Knunu
 * Date: 2015. 12. 1.
 * Time: 오전 10:06
 */
class Admin_model extends CI_Model
{
    public function __construct() {
        parent::__construct();
    }

    public function restore($target_table_name, $source_table_name) {
        $query = "SELECT * FROM $source_table_name";
        $queue = $this->db->query($query)->result();

        foreach ($queue as $raw_source) {
            $source = json_decode($raw_source->context);

            $data = array(
                // this information can't be shared.
            );

            $insert_query = $this->db->insert_string($target_table_name, $data);
            $insert_query = str_replace("INSERT INTO `$target_table_name` (0, `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8`, `9`, `10`)", "INSERT IGNORE INTO $target_table_name (device_id, fajr, sunrise, dhuhr, asr, maghrib, isha, push_token, pre_alarm_time, latitude, longitude)", $insert_query);
            $this->db->query($insert_query);
        }
    }
}
