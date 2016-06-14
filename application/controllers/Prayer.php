<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
define('DEFAULT_ADHAN', 'Abdul Baset');

class Prayer extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('util');

        $this->load->model('prayer_model');
        $this->load->model('job_queue_model');
    }

    public function get($class)
    {
        // 배열 초기값 설정
        $result = $this->util->setRetCode(SUCCESS);

        switch($class) {
            case 'pray_times':

                //print_r("DATE : " . $this->input->post('date'));

                $information = array(
                    'date'      => $this->input->post('date'),
                    'latitude'  => $this->input->post('latitude'),
                    'longitude' => $this->input->post('longitude')
                );

                $data = $this->prayer_model->get_pray_times($information);
                if ($data == NULL) {
                    $result = $this->util->setRetCode(ERR_DB_NODATA);
                    break;
                }

                $result['pray_times'] = $data;

                break;

            case 'adhan_list':

                $adhan_list = $this->prayer_model->get_adhan_list();
                if ($adhan_list == NULL) {
                    $result = $this->util->setRetCode(ERR_DB_NODATA);
                    break;
                }

                $result['adhan_list'] = $adhan_list;

                break;

            case 'quran_daily':
                
                $quran = $this->prayer_model->get_quran_daily_list();
                if ($quran == NULL) {
                    $result = $this->util->setRetCode(ERR_DB_NODATA);
                    break;
                }

                $result['quran_daily'] = $quran;

                break;
        }

        echo json_encode($result);
    }

    public function set_default_adhan()
    {
        $device_ids = $this->prayer_model->get_device_id_all();

        foreach ($device_ids as $value) {

            $ret = $this->prayer_model->set_default_adhan($value['device_id']);
            
            print_r($ret);
            //$ret = $this->prayer_model->set_push_alarm($this->input->post('device_id'));
        }

    }

    public function set($class)
    {
        // 배열 초기값 설정
        $result = $this->util->setRetCode(SUCCESS);

        // 위경도 저장 배열
        $latLng = array(
            'latitude'  => $this->input->post('latitude'),
            'longitude' => $this->input->post('longitude')
        );
        $latLng = $this->util->getDefaultLatLng($latLng);
        $device_id = $this->input->post('device_id');

        // Device ID 체크
        if ($device_id == NULL) {
            $result = $this->util->setRetCode(ERR_MSG_INVALID_VALUE);
        }

        $ret = NULL;

        // 적절한 디바이스 ID 인지 확인
        if (strlen($device_id) != CLIENT_TOKEN_SIZE) {
            $result = $this->util->setRetCode(ERR_MSG_INVALID_VALUE);

            echo json_encode($result);
            return;
        }

        switch($class) {
            case 'location':

                $information = array(
                    'device_id' => $device_id,
                    'latitude'  => $latLng['latitude'],
                    'longitude' => $latLng['longitude']
                );

                // 지역 등록
                $data = $this->prayer_model->set_location($information);
                if (!$data) {
                    $result = $this->util->setRetCode(ERR_FAIL);
                    break;
                }

                // Adhan 등록
                $data = $this->prayer_model->set_default_adhan($this->input->post('device_id'));
                if (!$data) {
                    $result = $this->util->setRetCode(ERR_FAIL);
                }
                break;

            case 'push_alarm':

                $information = array(
                    'device_id' => $device_id,
                    'enabled'   => $this->input->post('enabled')
                );

                // prayer_push_info 테이블에 데이터 삽입
                $data = $this->prayer_model->set_push_on_off($information);
                if (!$data) {
                    $result = $this->util->setRetCode(ERR_FAIL);
                    break;
                }

                break;

            case 'pre_alarm':

                $information = array(
                    'device_id' => $device_id,
                    'pre_alarm' => $this->input->post('pre_alarm'),
                );

                $data = $this->prayer_model->set_pre_alarm($information);
                if (!$data) {
                    $result = $this->util->setRetCode(ERR_FAIL);
                    break;
                }

                break;


            case 'partly_alarm':

                $information = array(
                    'device_id'  => $device_id,
                    'pray_class' => $this->input->post('pray_class'),
                    'type'       => $this->input->post('type')
                );

                $data = $this->prayer_model->set_partly_alarm($information);
                if (!$data) {
                    $result = $this->util->setRetCode(ERR_FAIL);
                    break;
                }

                break;

            case 'adhan':

                $information = array(
                    'device_id'  => $device_id,
                    'pray_class' => $this->input->post('pray_class'),
                    'adhan'      => $this->input->post('adhan')
                );

                $data = $this->prayer_model->set_adhan($information);
                if (!$data) {
                    $result = $this->util->setRetCode(ERR_FAIL);
                    break;
                }

                break;
            
            default:

                $result = $this->util->setRetCode(ERR_FAIL);

                break;

        }

        $ret = $this->prayer_model->set_push_alarm($this->input->post('device_id'));
        if($ret == NULL)
        {
            $result = $this->util->setRetCode(ERR_FAIL);

            echo json_encode($result);
            return;
        }
        $result['device_id'] = $device_id;

        $this->util->writeLogFile("prayer_set_log.txt", $class, $result);
        echo json_encode($result);
    }
}
