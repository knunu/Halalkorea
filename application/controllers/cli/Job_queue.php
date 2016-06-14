<?php #if ( ! defined('BASEPATH')) exit('No direct script access allowed');
define('PUSH_TOKEN', 'push token can\'t be shared');
define('DEFALUT_ADHAN', 'Abdul Baset');

class Job_queue extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('job_queue_model');

        // this controller can only be called from the command line
        #if (!$this->input->is_cli_request()) show_error('Direct access is not allowed');
    }

    public function send_salah_alarm()
    {
        $target_table_name = "alarm_timestamp";
        #$current_timestamp = intval(date('H')) * 60 + intval(date('i'));
        $current_timestamp = date('G:i');

        $queue = $this->job_queue_model->gets($target_table_name, $current_timestamp);

        $alarm = array(
            'notification' => array()
        );

        foreach ($queue as $job) {
            $context = json_decode($job->context);

            // if there is valid device_id
            if ($context->device_id != '') {
                if ($context->push_token == PUSH_TOKEN) {
                    $headers = array(
                        'Content-Type:application/json',
                        'Authorization:key=' . PUSH_TOKEN
                    );

                    if (strlen($context->device_id) == 140) {
                        // android push alarm data
                        $alarm = array(
                            'data' => array()
                        );
                        $alarm['to']                 = $context->device_id;
                        $alarm['data']['title']      = $job->job_name;
                        $alarm['data']['message']    = $context->alarm_timestamp;
                        $alarm['data']['alarm_type'] = $context->alarm_type;
                        if (isset($context->adhan))
                            $alarm['data']['adhan']  = $context->adhan;
                        if (isset($context->adhan_file))
                            $alarm['data']['adhan_file'] = $context->adhan_file;
                    } else if (strlen($context->device_id) == 152) {
                        // IOS push alarm data
                        $alarm = array(
                            'notification' => array()
                        );
                        $alarm['to'] = $context->device_id;
                        $alarm['content_available'] = true;
                        $alarm['priority'] = "high";
                        $alarm['notification']['title'] = $job->job_name;
                        $alarm['notification']['body'] = $context->alarm_timestamp;
                        if (isset($context->adhan_file))
                            $alarm['notification']['sound'] = $context->adhan_file . '.caf';
                    }
                }

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://gcm-http.googleapis.com/gcm/send');
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($alarm));
                $response = curl_exec($ch);
                curl_close($ch);

                echo $response;
            } else {
                echo json_encode(array('error' => 'device_id is null'));
            }
        }
    }

    public function reset_alarm_queue()
    {
        $this->load->model('salah_model');
        $condition = array(
            'fajr !=' => '0',
        );
        # If alarm is on, get salah_alarm information from db table
        $queue = $this->salah_model->get_data('salah_alarm', $condition)->result();

        # queue is the list of salah_alarm datas, job is an one row.
        foreach ($queue as $job) {
            # get new time data by using entered latitude, longitude.
            $ref_pray_times = $this->salah_model->get_pray_time('', $job->latitude, $job->longitude);
            # set proper time format
            $raw_pray_times = explode(',', str_replace('"', '', $ref_pray_times));
            # Update job_queue's data with new time per salah_alarm's row, there are 6 datas(fajr, sunrise, dhuhr, asr, maghrib, isha) per job.
            for ($i = 1; $i < count($raw_pray_times) - 1; $i++) {
                # set pray_class, final_pray_time, adhan, adhan_file_data, and other salah_alarm(job)'s data for resetting job_queue.
                $pray_class = substr($raw_pray_times[$i], 0, -6);
                if ($pray_class == 'sunset') continue;
                $pray_time = substr($raw_pray_times[$i], -5);

                $alarm_timestamp = intval(substr($pray_time, 0, 2)) * 60 + intval(substr($pray_time, 3, 2)) - intval($job->pre_alarm_time);
                $minute = $alarm_timestamp % 60;
                $final_pray_time = floor($alarm_timestamp / 60) . ":" . ($minute < 10 ? '0' . $minute : $minute);

                $condition = array(
                    'device_id' => $job->device_id,
                    'pray_class' => $pray_class
                );

                $adhan_data = $this->salah_model->get_data('salah_adhan', $condition);
                if ($adhan_data->num_rows() > 0) {
                    $adhan = $adhan_data->row()->adhan;
                } else {
                    $adhan = DEFALUT_ADHAN;
                }
                $adhan_file_data = $this->salah_model->get_data('salah_adhan_list', array('name' => $adhan));
                if ($adhan_file_data->num_rows() > 0) {
                    $adhan_file = $adhan_file_data->row()->file;
                } else {
                    $adhan_file = 'INVALID ADHAN';
                }

                $data = array(
                    'job_name' => $pray_class . " time",
                    'context' => json_encode(array(
                        'device_id' => $job->device_id,
                        'push_token' => $job->push_token,
                        'alarm_type' => $job->$pray_class,
                        'alarm_timestamp' => $final_pray_time,
                        'latitude' => $job->latitude,
                        'longitude' => $job->longitude,
                        'adhan' => $adhan,
                        'adhan_file' => $adhan_file
                    ))
                );

                $condition['push_token'] = PUSH_TOKEN;
                # When setting condition and data arrays is completed, update salah pray time of job_queue's one row(one pray of one device)
                echo $this->job_queue_model->update_salah_pray_time($condition, $data);
            }
        }
    }
}
