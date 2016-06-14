<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Job_queue_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('date');
        $this->load->library('util');
        $this->load->library('gcm');
    }

    public function gets($option = '', $option_value = '')
    {
        if ($option != '') {
            $query = "SELECT * FROM job_queue WHERE context LIKE '%\"" . $option . "\":\"" . $option_value . "\"%'";
        } else {
            $query = "SELECT * FROM job_queue";
        }

        return $this->db->query($query)->result();
    }

    public function get_push_info_list() {
        $query = "
            SELECT Q.pray_class, A.adhan, A.type, S.file, TIME_FORMAT(CURTIME(), '%H') 'hour', TIME_FORMAT(CURTIME(), '%i') 'minute'
              FROM prayer_push_alarm Q
        INNER JOIN prayer_adhan_info A
                ON (Q.device_id = A.device_id AND Q.pray_class = A.pray_class)
        INNER JOIN prayer_adhan_list S
                ON A.adhan = S.name
             WHERE Q.time_hour = TIME_FORMAT(CURTIME(), '%H')
               AND Q.time_minute = TIME_FORMAT(CURTIME(), '%i')
          GROUP BY Q.pray_class, A.adhan, A.type
        ";

        return $this->db->query($query)->result_array();
    }

    public function get_device_id($push_info) {
        $pray_class = $push_info['pray_class'];
        $adhan = $push_info['adhan'];
        $type = $push_info['type'];
        $hour = $push_info['hour'];
        $minute = $push_info['minute'];

        $query = "
            query can't be shared.
        ";

        $result_array = $this->db->query($query)->result_array();
        $value_array = array();
        foreach ($result_array as $row) {
            array_push($value_array, $row['device_id']);
        }
        
        return $value_array;
    }

    /**
     * DEPRECATED
     * @return mixed
     */
    public function get_push_data()
    {
        $hour   = sprintf("%02d", date('G'));
        $minute = sprintf("%02d", date('i'));

        $query = "query can't be shared.";

        return $this->db->query($query)->result_array();
    }

    public function add($option)
    {
        $this->db->set('job_name', $option['job_name']);
        $this->db->set('context', $option['context']);
        $this->db->insert('job_queue');
        $result = $this->db->insert_id();

        return $result;
    }

    public function delete($option)
    {
        return $this->db->delete('job_queue', array('id' => $option['id']));
    }

    public function update_salah_pray_time($condition, $data)
    {
        $query = "query can't be shared.";
        $result_set = $this->db->query($query)->row();

        if (isset($result_set)) {
            $id = $result_set->id;
            $this->db->where('id', $id);
            if ($this->db->update('job_queue', array('context' => $data['context']))) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * this function set adhan to job_queue per salah pray
     * by using client's device_id and pray class.
     *
     * target database table : halalkorea.job_queue
     *
     * @param $device_id : client device id, In case of android, gcm id
     * @param $pray_class : pray type, ex : fajr, maghrib ...
     * @param $adhan : prayer, ex : Az-Zahrani
     * @return array : result['code', 'message']
     *
     * made by Knunu, 16.01.29
     */
    public function update_adhan($device_id, $pray_class, $adhan, $adhan_file) {
        $target_table = 'job_queue';
        $job_name = strtoupper(substr($pray_class, 0, 1)) . substr($pray_class, 1) . " time";

        $query = "query can't be shared.";

        $query_result = $this->db->query($query)->row();
        if ($query_result != NULL) {
            $job_id = $query_result->id;
            $context = json_decode($query_result->context);

            $this->db->where(array('id' => $job_id));
            if ($this->db->update($target_table, array('context' => $context))) {
                $result['code'] = 200;
                $result['message'] = 'success to update!';
            } else {
                $result['code'] = 400;
                $result['message'] = 'failed to update!';
            }
        }

        return $result;
    }
}
