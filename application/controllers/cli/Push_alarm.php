<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Push_alarm extends CI_Controller                                     
{                                                                          

    public function __construct()
    {
        parent::__construct();
        $this->load->library('util');
        $this->load->library('gcm');
        $this->load->model('job_queue_model');
        $this->load->model('prayer_model');
    }

    public function test_device_id() {
        $push_info_list = $this->job_queue_model->get_push_info_list();

        foreach ($push_info_list as $push_info) {
            $device_id_list = $this->job_queue_model->get_device_id($push_info);
            print_r(json_encode($device_id_list));
        }
    }

    public function send_push_alarm()
    {
        $result = $this->util->setRetCode(SUCCESS);

        $push_info_list = $this->job_queue_model->get_push_info_list();

        if ($push_info_list == NULL) {
            $result = $this->util->setRetCode(ERR_DB_NODATA);
            $this->util->writeLogFile("push_alarm_log.txt", "send_push_alarm", $result);
            return;
        }

        foreach ($push_info_list as $push_info) {
            $device_id_list = $this->job_queue_model->get_device_id($push_info);
            $this->gcm->addRecepients($device_id_list);
            $this->gcm->setData(array(
                'title' => $push_info['pray_class'],
                'message' => $push_info['hour'] . ':' . $push_info['minute'],
                'alarm_type' => $push_info['type'],
                'adhan' => $push_info['adhan'],
                'adhan_file' => $push_info['file']
            ));
            $this->gcm->setTtl(false);
            $this->gcm->setGroup(false);

            if ($this->gcm->send()) {
                $result['status'] = $this->gcm->status;
                $result['messagesStatuses'] = $this->gcm->messagesStatuses;
                $this->util->writeLogFile("push_alarm_log.txt", "send_push_alarm", $result);
            } else {
                $result = $this->util->setRetCode(ERR_FAIL);
                $result['status'] = $this->gcm->status;
                $result['messagesStatuses'] = $this->gcm->messagesStatuses;
                $this->util->writeLogFile("push_alarm_error.txt", "send_push_alarm", $result);
            }
        }
    }

    public function reset_push_alarm()
    {
        $result = $this->util->setRetCode(SUCCESS);

        // push_alarm 테이블 TRUNCATE
        $truncate = $this->prayer_model->truncate_push_alarm();
        if (!$truncate) {
            $result = $this->util->setRetCode(ERR_FAIL);
            echo json_encode($result);
            return;
        }

        $device_ids = $this->prayer_model->get_device_id_all();

        foreach($device_ids as $value) {

            $ret = $this->prayer_model->set_push_alarm($value['device_id']);

            if ($ret == NULL) {
                $result = $this->util->setRetCode(ERR_MSG_INVALID_VALUE);
                $result['device_id'] = $value['device_id'];
                $this->util->writeLogFile("push_alarm_log.txt", "reset_push_alarm", $result);
            }
        }

        $this->util->writeLogFile("push_alarm_log.txt", "reset_push_alarm", $result);
        echo json_encode($result);
    }
}
