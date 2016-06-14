<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Prayer_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->load->library('node');
        $this->load->helper('date');
    }

    public function get_pray_times($information)
    {
        $date      = $information['date'];
        $latitude  = $information['latitude'];
        $longitude = $information['longitude'];

        $js_file = 'this information can\'t be shared';

        if ($date != '') {
            $js_file .= ' ' . $date;
        }
        $js_file .= ' ' . $latitude . ' ' . $longitude;
        $pray_times = $this->node->node_start($js_file);

        return json_decode($pray_times, true);
    }

    public function get_quran_daily_list()
    {
        $condition = array(
            'date' => date('Y-m-d'),
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
            // it there is no today quran
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

        return $result;
        //echo json_encode($result);
    }

    public function get_adhan_list()
    {
        $query = 'this information can\'t be shared';

        return $this->db->query($query)->result_array();
    }

    public function get_device_id_all()
    {
        $query = 'this information can\'t be shared';

        return $this->db->query($query)->result_array();
    }

    public function get_push_info($device_id)
    {

        $query = 'this information can\'t be shared';

        return $this->db->query($query)->row();

    }

    public function truncate_push_alarm()
    {
        $query = 'this information can\'t be shared';

        return $this->db->query($query);
    }

    public function set_push_info($information)
    {
        $device_id = $information['device_id'];
        $enabled   = $information['enabled'];
        $latitude  = $information['latitude'];
        $longitude = $information['longitude'];

        if(!insert_default_adhan($device_id)){
            return NULL;
        }

        $query = 'this information can\'t be shared';

        return $this->db->query($query);


    }

    public function set_push_on_off($information)
    {
        $device_id = $information['device_id'];
        $enabled   = $information['enabled'];

        $query = 'this information can\'t be shared';

        return $this->db->query($query);
    }

    public function set_pre_alarm($information)
    {

        $device_id = $information['device_id'];
        $pre_alarm = $information['pre_alarm'];

        $query = 'this information can\'t be shared';

        return $this->db->query($query);

    }

    public function set_location($information)
    {

        $device_id = $information['device_id'];
        $latitude  = $information['latitude'];
        $longitude = $information['longitude'];

        $query = 'this information can\'t be shared';


        return $this->db->query($query);

    }

    //public function set_default_adhan($information)
    public function set_default_adhan($device_id)
    {
        //$device_id = $information['device_id'];

        $query = 'this information can\'t be shared';

        return $this->db->query($query);

    }

    public function set_partly_alarm($information)
    {
        $device_id  = $information['device_id'];
        $pray_class = $information['pray_class'];
        $type       = $information['type'];

        $query = 'this information can\'t be shared';

        return $this->db->query($query);

    }

    public function set_adhan($information)
    {
        $device_id  = $information['device_id'];
        $pray_class = $information['pray_class'];
        $adhan      = $information['adhan'];

        $query = 'this information can\'t be shared';

        return $this->db->query($query);

    }

    // 시간값 계산
    public function set_push_alarm($device_id)
    {
        // 해당 device_id의 push 정보를 가져옴
        $push_info = $this->get_push_info($device_id);

        $latitude  = $push_info->latitude;
        $longitude = $push_info->longitude;
        $pre_alarm = $push_info->pre_alarm;

        // 기존에 테이블에 들어있는 데이터 삭제
        if (!$this->delete_push_alarm($device_id)) {
            return NULL;
        }

        if ($push_info->enabled == 0) {
            return TRUE;
        }

        //시간값 계산하기
        $pray_times = $this->calculate_pray_times($latitude, $longitude, $pre_alarm);
        if ($pray_times == NULL) {
            return NULL;
        }

        // 계산한 값으로 다시 테이블에 넣기
        if ($this->insert_push_alarm($device_id, $pray_times) == NULL) {
            return NULL;
        }

        return true;
    }

    public function delete_push_alarm($device_id)
    {

        $query = 'this information can\'t be shared';

        return $this->db->query($query);
    }

    public function calculate_pray_times($latitude, $longitude, $pre_alarm)
    {
        $information = array(
            'date'      => '',
            'latitude'  => $latitude,
            'longitude' => $longitude
        );

        $pray_times = $this->prayer_model->get_pray_times($information);
        if ($pray_times == NULL) {
            return NULL;
        }

        // pre_alarm이 0보다 크면 계산 함
        if ($pre_alarm > 0) {

            $pray_times = $this->calculate_pre_alarm($pray_times, $pre_alarm);
            if ($pray_times == NULL) {
                return NULL;
            }
        }

        //print_r($pray_times);

        return $pray_times;
    }


    public function calculate_pre_alarm($pray_times, $pre_alarm)
    {
        // 계산하기
        $fajr    = date('H:i', strtotime("-$pre_alarm minutes", strtotime($pray_times['fajr'])));
        $sunrise = date('H:i', strtotime("-$pre_alarm minutes", strtotime($pray_times['sunrise'])));
        $dhuhr   = date('H:i', strtotime("-$pre_alarm minutes", strtotime($pray_times['dhuhr'])));
        $asr     = date('H:i', strtotime("-$pre_alarm minutes", strtotime($pray_times['asr'])));
        $maghrib = date('H:i', strtotime("-$pre_alarm minutes", strtotime($pray_times['maghrib'])));
        $isha    = date('H:i', strtotime("-$pre_alarm minutes", strtotime($pray_times['isha'])));

        $pray_times['fajr']    = $fajr;
        $pray_times['sunrise'] = $sunrise;
        $pray_times['dhuhr']   = $dhuhr;
        $pray_times['asr']     = $asr;
        $pray_times['maghrib'] = $maghrib;
        $pray_times['isha']    = $isha;

        //print_r($pray_times);
        return $pray_times;
    }

    public function insert_push_alarm($device_id, $pray_times)
    {

        //$time_array = json_decode($time);

        $fajr    = explode(':',$pray_times['fajr']);
        $sunrise = explode(':',$pray_times['sunrise']);
        $dhuhr   = explode(':',$pray_times['dhuhr']);
        $asr     = explode(':',$pray_times['asr']);
        $maghrib = explode(':',$pray_times['maghrib']);
        $isha    = explode(':',$pray_times['isha']);

        $query = 'this information can\'t be shared';

        $this->db->query($query);

       
        return $this->db->query($query);

    }
}
