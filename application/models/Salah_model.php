<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Salah_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->load->library('node');
        $this->load->helper('date');
    }

    /**
     * get db table data by passing the table, option parameters.
     *
     * parameter :
     * 1. string, necessary table name
     * 2. array, select option
     *
     * target database table : halalkorea.*
     * return type : result object [please read CI's reference]
     * made by Knunu, 15.10.25
     */
    public function get_data($table = '', $option = '')
    {
        $result = array();

        if ($table == '') {
            return $result['error'] = "table is NULL!";
        } else if ($option == '') {
            $result_set = $this->db->get($table);
        } else {
            $result_set = $this->db->get_where($table, $option);
        }

        return $result_set;
    }

    /**
     * insert db table data by passing the table_name, data parameters.
     *
     * parameter :
     * 1. string, necessary table name to be inserted
     * 2. array, table data
     *
     * target database table : halalkorea.*
     * return type : if insert successfully, true. if not, error message.
     * made by Knunu, 15.10.25
     */
    public function insert_data($table_name = '', $data = '')
    {
        if ($table_name == '') {
            return "error : table name is NULL!";
        } else if ($data == '') {
            return "error : table data is NULL!";
        } else {
            if ($this->db->insert($table_name, $data)) return true;
        }
    }

    /**
     * update db table data by passing the table_name, data, condition parameters.
     * UPDATE $table_name SET $data WHERE $condition
     * target database table : halalkorea.*
     *
     * @param string $table_name
     * @param array $data : [column => value]
     * @param array $condition : [column => value]
     * @return bool|string
     *
     * made by Knunu, 15.11.03
     */
    public function update_data($table_name = '', $data = array(), $condition = array())
    {
        if ($table_name == '') {
            return FALSE;
        } else if (count($data) == 0) {
            return FALSE;
        } else if (count($condition) == 0) {
            return FALSE;
        } else {
            $this->db->where($condition);
            if ($this->db->update($table_name, $data)) return true;
            return false;
        }
    }

    /**
     * this function gets salah pray time function
     *
     * @param $date : date to check
     * @param $param_x : latitude
     * @param $param_y : longitude
     * @return mixed : string containing pray times
     *
     * made by Knunu.
     */
    public function get_pray_time($date, $param_x, $param_y)
    {
        $js_file = 'this information can\'t be shared';

        if ($date != '') {
            $js_file .= ' ' . $date;
        }
        $js_file .= ' ' . $param_x . ' ' . $param_y;
        $pray_times = $this->node->node_start($js_file);

        return $pray_times;
    }

    /**
     * make and return daily quran list that select aya randomly everyday.
     *
     * target database table : halalkorea.quran_daily
     * return type : json object
     *
     * made by Knunu, Modified at 15.10.27
     */
    public function get_quran_daily_list()
    {
        $condition = array(
            'date <=' => date('Y-m-d'),
            'in_use' => TRUE,
        );

        $update_data = array(
            'in_use' => TRUE,
            'date' => date('Y-m-d'),
        );

        $table = 'this information can\'t be shared';

        $this->db->select('id, date');
        $raw_daily_list = $this->db->order_by('date', 'DESC')->get_where($table, $condition, 60);

        //if there is existing daily quran
        if ($raw_daily_list->num_rows() > 0) {
            // there is no today quran
            $result = $raw_daily_list->result_array();

            if ($raw_daily_list->first_row()->date != date('Y-m-d')) {
                $today_quran = $this->db->order_by('rand()', 'ASC')->get_where($table, array('in_use' => FALSE), 1)->row();

                $this->db->where('id', $today_quran->id);
                $this->db->update($table, $update_data);

                $this->db->select('id, date');
                array_unshift($result, $this->db->get_where($table, array('date' => date('Y-m-d')))->row());
                unset($result[60]); // result array 61's value should be removed;
            }
            // if there is no daily quran
        } else {
            $today_quran = $this->db->order_by('rand()', 'ASC')->get_where($table, array('in_use' => FALSE), 1)->row();

            $this->db->where('id', $today_quran->id);
            $this->db->update($table, $update_data);

            $this->db->select('id, date');
            $result[0] = $this->db->get_where($table, array('date' => date('Y-m-d')))->row();
        }

        echo json_encode($result);
    }

    /**
     * when application sets push alarm about pray time, this function put related information into database.
     *
     * @param $data : {device_id, push_token, (fajr, sunrise, dhuhr, asr, maghrib, isha), pre_alarm_time, latitude, longitude}
     * @database : halalkorea.salah_alarm
     * @return array : result array with code and message.
     * made by Knunu, 15.10.06
     */
    public function set_push($data)
    {
        $result = array();

        $insert_query = $this->db->insert_string('this information can\'t be shared', $data);
        $insert_query = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $insert_query);
        $this->db->query($insert_query);

        if ($this->db->affected_rows() == 1) {
            $result['code'] = '200';
            $result['message'][0] = 'DB insert success!';
        } else {
            $device_id = $data['device_id'];
            unset($data['device_id']);
            $update_data = $data;

            $this->db->where('device_id', $device_id);
            if ($this->db->update('salah_alarm', $update_data)) {
                $result['code'] = '200';
                $result['message'][0] = 'DB update success!';
            } else {
                $result['code'] = '400';
                $result['message'][0] = 'DB update error!';
            }
        }

        return $result;
    }

    /**
     * when application sets push alarm about pray time, this function sets alarm list of job_queue.
     * modified to include adhan information
     * target database table : job_queue
     * parameter type : json object
     * return type : result array
     * made by knunu, 15.10.07
     * modified by knunu, 16.01.29
     */
    public function set_alarm_queue($raw_pray_times, $data)
    {
        $result = array(
            'pray_code' => array(),
            'pray_message' => array(),
            'pray_error' => array(),
            'error' => ''
        );

        $device_id = $data['device_id'];
        $push_token = $data['push_token'];
        $pre_alarm_time = $data['pre_alarm_time'];
        $latitude = $data['latitude'];
        $longitude = $data['longitude'];
        $adhan_list = $data['adhan'];

        $pray_data = array(
            'fajr' => isset($data['fajr']) ? $data['fajr'] : -1,
            'sunrise' => isset($data['sunrise']) ? $data['sunrise'] : -1,
            'dhuhr' => isset($data['dhuhr']) ? $data['dhuhr'] : -1,
            'asr' => isset($data['asr']) ? $data['asr'] : -1,
            'maghrib' => isset($data['maghrib']) ? $data['maghrib'] : -1,
            'isha' => isset($data['isha']) ? $data['isha'] : -1,
        );

        $pray_times = array();

        for ($i = 1; $i < count($raw_pray_times) - 1; $i++) {
            $pray_class = substr($raw_pray_times[$i], 0, -6);
            $pray_time = substr($raw_pray_times[$i], -5);

            if ($pray_class == 'Sunset') continue;
            $pray_times[$pray_class] = $pray_time;
        }

        foreach ($pray_data as $pray_class => $alarm_type) {
            if ($alarm_type == -1) continue;
            $adhan = $adhan_list[$pray_class];
            $adhan_file = $this->db->get_where('this information can\'t be shared', array('name' => $adhan))->row()->file;

            if ($alarm_type != '0') {
                $alarm_time_amount = intval(substr($pray_times[$pray_class], 0, 2)) * 60 + intval(substr($pray_times[$pray_class], 3, 2)) - $pre_alarm_time;

                $minute = $alarm_time_amount % 60;
                $alarm_timestamp = floor($alarm_time_amount / 60) . ":" . ($minute < 10 ? '0' . $minute : $minute);
                $job_name = strtoupper(substr($pray_class, 0, 1)) . substr($pray_class, 1) . " time";
                $data = array(
                    'job_name' => $job_name,
                    'context' => json_encode(array(
                        'device_id' => $device_id,
                        'push_token' => $push_token,
                        'alarm_type' => $alarm_type,
                        'alarm_timestamp' => $alarm_timestamp,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'adhan' => $adhan,
                        'adhan_file' => $adhan_file
                    ))
                );

                $query = 'this information can\'t be shared';
                $result_set = $this->db->query($query)->row();
                if ($result_set != NULL) {
                    $id = $result_set->id;
                    $this->db->where('id', $id);
                    if ($this->db->update('job_queue', array('context' => $data['context']))) {
                        $result['pray_code'][$pray_class] = '200';
                        $result['pray_message'][$pray_class] = 'DB update success!';
                    } else {
                        $result['pray_code'][$pray_class] = '400';
                        $result['pray_error'][$pray_class] = 'DB update error!';
                    }
                } else {
                    if ($this->db->insert('job_queue', $data)) {
                        $result['pray_code'][$pray_class] = '200';
                        $result['pray_message'][$pray_class] = 'DB insert success!';
                    } else {
                        $result['pray_code'][$pray_class] = '400';
                        $result['pray_error'][$pray_class] = 'DB insert error!';
                    }
                }
            } else {
                $query = 'this information can\'t be shared';
                $this->db->query($query);

                if ($this->db->affected_rows() == 1) {
                    $result['pray_code'][$pray_class] = '201';
                    $result['pray_message'][$pray_class] = 'DB delete success!';
                } else {
                    $result['pray_code'][$pray_class] = '401';
                    $result['pray_message'][$pray_class] = 'DB delete already processed!';
                }
            }
        }

        if (!count($result['pray_error'])) {
            $result['code'] = '200';
        } else {
            $result['code'] = '400';
            $result['error'] = "DB processing error! Please check the pray_error key's value";
        }

        return $result;
    }

    /**
     * this function sets adhan(a call to prayer) per salah pray
     * by using client's device_id.
     *
     * target database table : halalkorea.salah_adhan
     *
     * @param $device_id : client device id, In case of android, gcm id
     * @param $pray_class : pray type, ex : fajr, maghrib ...
     * @param $adhan : prayer, ex : Az-Zahrani
     * @return array : result['code', 'message']
     *
     * made by Knunu, 16.01.13
     */
    public function set_adhan($device_id, $pray_class, $adhan) {
        $target_table = 'this information can\'t be shared';
        $condition = array(
            'device_id' => $device_id,
            'pray_class' => $pray_class
        );
        $value = array(
            'adhan' => $adhan
        );
        $result = array();

        $result_row = $this->db->get_where($target_table, $condition)->row();
        if ($result_row != null) {
            $this->db->where($condition);
            if ($this->db->update($target_table, $value)) {
                $result['code'] = 200;
                $result['message'] = 'success to update!';
            } else {
                $result['code'] = 400;
                $result['message'] = 'failed to update!';
            }
        } else {
            $insert_data = array_merge($condition, $value);
            if ($this->db->insert($target_table, $insert_data)) {
                $result['code'] = 200;
                $result['message'] = 'success to insert!';
            } else {
                $result['code'] = 400;
                $result['message'] = 'failed to insert!';
            }
        }

        return $result;
    }

    /**
     * this function gets adhan(a call to prayer) information per salah pray by using client's device_id.
     *
     * target database table : halalkorea.salah_adhan
     *
     * @param $device_id : client device id, In case of android, gcm id
     * @param $pray_class : pray type, ex : fajr, maghrib ...
     * @return array : result['code', 'message', ('adhan')]
     *
     * made by Knunu, 16.01.13
     */
    public function get_adhan($device_id, $pray_class) {
        $target_table = 'this information can\'t be shared';
        $target_column = 'this information can\'t be shared';
        $condition = array(
            'device_id' => $device_id,
            'pray_class' => $pray_class
        );
        $result = array();

        $this->db->select($target_column);
        $this->db->where($condition);
        $query = $this->db->get($target_table);

        if ($query) {
            $result_row = $query->row();

            if ($result_row != null) {
                $result['code'] = 200;
                $result['message'] = 'Success!';
                $result['adhan'] = $result_row;
            } else {
                $result['code'] = 410;
                $result['message'] = 'failed to get adhan with device id!';
            }
        } else {
            $result['code'] = 400;
            $result['message'] = 'failed to get query result!';
        }


        return $result;
    }

    /**
     * this function is for getting whole adhan list.
     * @database salah_adhan_list
     * @return array : adhan list array with result code, message
     *                 OR message array with result code
     * made by Knunu, 16.01.25
     */
    public function get_adhan_list() {
        $target_table = 'this information can\'t be shared';
        $result = array();

        $query = $this->db->get($target_table);
        if ($query) {
            $result['code'] = 200;
            $result['message'] = 'Success!';
            $result['adhan_list'] = $query->result_array();
        } else {
            $result['code'] = 400;
            $result['message'] = 'failed to get adhan list!';
        }

        return $result;
    }
}
?>