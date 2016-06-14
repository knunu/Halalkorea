<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
define('DEFAULT_ADHAN', 'Abdul Baset');

class Salah extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('salah_model');
        $this->load->model('job_queue_model');
    }

    public function pray_times($class = '')
    {
        if ($class == '') {
            $latitude  = $this->input->post('latitude');
            $longitude = $this->input->post('longitude');
            if ($latitude == '' ||
                $latitude == '0.0' ||
                $latitude == '0' ||
                $latitude == null ||
                $latitude == 'null') $latitude = DEFAULT_X;
            if ($longitude == '' ||
                $longitude == '0.0' ||
                $longitude == '0' ||
                $longitude == null ||
                $longitude == 'null') $longitude = DEFAULT_Y;

            echo $this->salah_model->get_pray_time($this->input->post('date'), $latitude, $longitude);

        } else if ($class == 'set_location') {
            $result = array();
            $push_token = PUSH_TOKEN;

            $base_data = array(
                'device_id' => $this->input->post('device_id'),
                'push_token' => $push_token
            );

            # if there is already data in salah_alarm
            if ($this->salah_model->get_data('salah_alarm', $base_data)->num_rows() > 0) {
                # data should be ONE ROW!
                $data = $this->salah_model->get_data('salah_alarm', $base_data)->row();

                $base_data['latitude'] = $this->input->post('latitude');
                $base_data['longitude'] = $this->input->post('longitude');
                if ($base_data['latitude'] == '' ||
                    $base_data['latitude'] == '0.0' ||
                    $base_data['latitude'] == '0' ||
                    $base_data['latitude'] == null ||
                    $base_data['latitude'] == 'null') $base_data['latitude'] = DEFAULT_X;
                if ($base_data['longitude'] == '' ||
                    $base_data['longitude'] == '0.0' ||
                    $base_data['longitude'] == '0' ||
                    $base_data['longitude'] == null ||
                    $base_data['longitude'] == 'null') $base_data['longitude'] = DEFAULT_Y;

                # update user's latitude, longitude information in salah_alarm
                $result['set_push'] = $this->salah_model->set_push($base_data);

                $base_data['fajr'] = $data->fajr;
                $base_data['sunrise'] = $data->sunrise;
                $base_data['dhuhr'] = $data->dhuhr;
                $base_data['asr'] = $data->asr;
                $base_data['maghrib'] = $data->maghrib;
                $base_data['isha'] = $data->isha;

                $base_data['pre_alarm_time'] = $data->pre_alarm_time;

                # adhan data should be 6 ROWS!
                $adhan_raw_data = $this->salah_model->get_data('salah_adhan', array('device_id' => $base_data['device_id']));
                if ($adhan_raw_data->num_rows() > 0) {
                    $adhan_data = array();
                    foreach ($adhan_raw_data->result_array() as $row) {
                        $adhan_data[$row['pray_class']] = $row['adhan'];
                    }
                    $base_data['adhan'] = $adhan_data;
                } else {
                    $adhan_data = array(
                        'fajr' => DEFAULT_ADHAN,
                        'sunrise' => DEFAULT_ADHAN,
                        'dhuhr' => DEFAULT_ADHAN,
                        'asr' => DEFAULT_ADHAN,
                        'maghrib' => DEFAULT_ADHAN,
                        'isha' => DEFAULT_ADHAN
                    );
                    foreach ($adhan_data as $pray_class => $adhan) {
                        $adhan_result[$pray_class] =  $this->salah_model->set_adhan($base_data['device_id'], $pray_class, $adhan);
                    }
                    $base_data['adhan'] = $adhan_data;
                    $result['set_adhan'] = $adhan_result;
                }
                # update job_queue table information with new pray_time and base data
                $ref_pray_times = $this->salah_model->get_pray_time('', $base_data['latitude'], $base_data['longitude']);
                $result['set_alarm_queue'] = $this->salah_model->set_alarm_queue(explode(',', str_replace('"', '', $ref_pray_times)), $base_data);

                if ($result['set_push']['code'] == '200' && $result['set_alarm_queue']['code'] == '200') {
                    $result['code'] = '200';
                } else {
                    $result['code'] = '400';
                    $result['error'] = "Error appeared in set_push or set_alarm_queue!";
                }

                # if there is no data in salah_alarm
            } else {
                # insert data to salah_alarm by using set_push function
                $base_data['latitude'] = $this->input->post('latitude');
                $base_data['longitude'] = $this->input->post('longitude');
                if ($base_data['latitude'] == '' ||
                    $base_data['latitude'] == '0.0' ||
                    $base_data['latitude'] == '0' ||
                    $base_data['latitude'] == null ||
                    $base_data['latitude'] == 'null') $base_data['latitude'] = DEFAULT_X;
                if ($base_data['longitude'] == '' ||
                    $base_data['longitude'] == '0.0' ||
                    $base_data['longitude'] == '0' ||
                    $base_data['longitude'] == null ||
                    $base_data['longitude'] == 'null') $base_data['longitude'] = DEFAULT_Y;
                $base_data['fajr']    = 2;
                $base_data['sunrise'] = 2;
                $base_data['dhuhr']   = 2;
                $base_data['asr']     = 2;
                $base_data['maghrib'] = 2;
                $base_data['isha']    = 2;

                $base_data['pre_alarm_time'] = 0;
                # insert user's information in salah_alarm
                $result['set_push'] = $this->salah_model->set_push($base_data);

                # insert data to salah_adhan by using set_adhan function
                $adhan_result = array();
                # adhan data should be 6 ROWS!
                $adhan_data = array(
                    'fajr' => DEFAULT_ADHAN,
                    'sunrise' => DEFAULT_ADHAN,
                    'dhuhr' => DEFAULT_ADHAN,
                    'asr' => DEFAULT_ADHAN,
                    'maghrib' => DEFAULT_ADHAN,
                    'isha' => DEFAULT_ADHAN
                );
                foreach ($adhan_data as $pray_class => $adhan) {
                    $adhan_result[$pray_class] =  $this->salah_model->set_adhan($base_data['device_id'], $pray_class, $adhan);
                }
                $base_data['adhan'] = $adhan_data;
                $result['set_adhan'] = $adhan_result;


                # insert data to job_queue with proper pray time and base data.
                $ref_pray_times = $this->salah_model->get_pray_time('', $base_data['latitude'], $base_data['longitude']);
                $result['set_alarm_queue'] = $this->salah_model->set_alarm_queue(explode(',', str_replace('"', '', $ref_pray_times)), $base_data);

                if ($result['set_push']['code'] == '200' && $result['set_alarm_queue']['code'] == '200') {
                    $result['code'] = '200';
                } else {
                    $result['code'] = '400';
                    $result['error'] = "Error appeared in set_push or set_alarm_queue!";
                }
            }

            echo json_encode($result);
        # set_push OR set_push_partly
        } else if (strpos($class, 'set_push') !== false) {
            $result = array(
                'set_push' => array(),
                'set_alarm_queue' => array()
            );

            $data = array(
                'device_id' => $this->input->post('device_id'),
                'push_token' => PUSH_TOKEN
            );

            $base_data = $this->salah_model->get_data('salah_alarm', $data)->row();
            $classifier = strpos($class, 'set_push_partly');
            # set_push_partly
            if ($classifier !== false) {
                $pray_class = $this->input->post('pray_class');
                $alarm_type = $this->input->post('type');
                $data[$pray_class] = $alarm_type;
            # set_push (all on / off)
            } else {
                if ($this->input->post('enable') == '1') {
                    $data['fajr'] = 2;
                    $data['sunrise'] = 2;
                    $data['dhuhr'] = 2;
                    $data['asr'] = 2;
                    $data['maghrib'] = 2;
                    $data['isha'] = 2;
                } else { // fajr ~ isha should be 0 after modified.
                    $data['fajr'] = 0;
                    $data['sunrise'] = 0;
                    $data['dhuhr'] = 0;
                    $data['asr'] = 0;
                    $data['maghrib'] = 0;
                    $data['isha'] = 0;
                }
            }

            $data['pre_alarm_time'] = $base_data->pre_alarm_time;
            $data['latitude'] = $base_data->latitude == DEFAULT_X ? $this->input->post('latitude') : $base_data->latitude;
            $data['longitude'] = $base_data->longitude == DEFAULT_Y ? $this->input->post('longitude') : $base_data->longitude;

            if ($data['latitude'] == '' ||
                $data['latitude'] == '0.0' ||
                $data['latitude'] == '0' ||
                $data['latitude'] == null ||
                $data['latitude'] == 'null') $data['latitude'] = DEFAULT_X;
            if ($data['longitude'] == '' ||
                $data['longitude'] == '0.0' ||
                $data['longitude'] == '0' ||
                $data['longitude'] == null ||
                $data['longitude'] == 'null') $data['longitude'] = DEFAULT_Y;
//            echo("data before set_push in set_push_partly : \n");
//            print_r($data);
            $result['set_push'] = $this->salah_model->set_push($data);

            # get adhan data from salah_adhan database table
            # adhan data should be 6 ROWS!
            $adhan_data = array();
            $adhan_raw_data = $this->salah_model->get_data('salah_adhan', array('device_id' => $data['device_id']));
            if ($adhan_raw_data->num_rows() > 0) {
                foreach ($adhan_raw_data->result_array() as $row) {
                    $adhan_data[$row['pray_class']] = $row['adhan'];
                }
                $data['adhan'] = $adhan_data;
            } else {
                $result['error'] = "No data in salah_adhan!";
            }
//            echo("data before set_alarm_queue in set_push_partly : \n");
//            print_r($data);

            $ref_pray_times = $this->salah_model->get_pray_time('', $data['latitude'], $data['longitude']);
            $result['set_alarm_queue'] = $this->salah_model->set_alarm_queue(explode(',', str_replace('"', '', $ref_pray_times)), $data);

            if ($result['set_push']['code'] == '200' && $result['set_alarm_queue']['code'] == '200') {
                $result['code'] = '200';
            } else {
                $result['code'] = '400';
                $result['error'] = "Error appeared in set_push or set_alarm_queue!";
            }

            echo json_encode($result);

        } else if ($class == 'set_pre_alarm_time') {
            # set pre-conditions
            $result = array(
                'salah_alarm' => array(),
                'job_queue' => array(),
                'error' => ''
            );
            $condition = array(
                'device_id' => $this->input->post('device_id'),
            );
            $data = array(
                'pre_alarm_time' => intval($this->input->post('time')),
            );

            # salah_alarm update process
            if ($this->salah_model->update_data('salah_alarm', $data, $condition)) {
                $result['salah_alarm']['code'] = '200';
            } else {
                $result['salah_alarm']['code'] = '400';
                $result['error'] = 'salah_alarm update failed!';
            }

            # job_queue update process
            # First, get data from salah_alarm database table.
            $job = $this->salah_model->get_data('salah_alarm', $condition)->row_array();

            # get new pray time from get_pray_time function
            $ref_pray_times = $this->salah_model->get_pray_time('', $job['latitude'], $job['longitude']);
            $raw_pray_times = explode(',', str_replace('"', '', $ref_pray_times));
            $job['pre_alarm_time'] = $data['pre_alarm_time'];

            # get adhan data from salah_adhan database table
            # adhan data should be 6 ROWS!
            $adhan_raw_data = $this->salah_model->get_data('salah_adhan', array('device_id' => $condition['device_id']));
            if ($adhan_raw_data->num_rows() > 0) {
                $adhan_data = array();
                foreach ($adhan_raw_data->result_array() as $row) {
                    $adhan_data[$row['pray_class']] = $row['adhan'];
                }
                $job['adhan'] = $adhan_data;
            } else {
                $result['error'] = 'no data in salah_adhan!';
            }

            # set job_queue database table
            $result['job_queue'] = $this->salah_model->set_alarm_queue($raw_pray_times, $job);

            if ($result['job_queue']['error'] != '') {
                $result['error'] = 'job_queue update failed!';
            }

            # check that all processes are executed well
            if ($result['error'] == '') {
                $result['code'] = '200';
                $result['message'] = 'Success!';
            } else {
                $result['code'] = '400';
                $result['message'] = 'There are error in update process!';
            }

            echo json_encode($result);
        }
    }

    public function quran_daily()
    {
        $this->salah_model->get_quran_daily_list();
    }

    public function adhan($class)
    {
        $device_id = $this->input->post('device_id');
        $pray_class = $this->input->post('pray_class');

        if ($class == 'set') {
            $result = array();

            $adhan = $this->input->post('adhan');
            $result['set_adhan'] = $this->salah_model->set_adhan($device_id, $pray_class, $adhan);

            $adhan_file_data = $this->salah_model->get_data('salah_adhan_list', array('name' => $adhan));
            if ($adhan_file_data->num_rows() > 0) {
                $adhan_file = $adhan_file_data->row()->file;
                $result['set_job_queue'] = $this->job_queue_model->update_adhan($device_id, $pray_class, $adhan, $adhan_file);
            } else {
                $result['set_job_queue'] = 'there is no that adhan in salah_adhan_list';
            }
        } else if ($class == 'get') {
            $result = $this->salah_model->get_adhan($device_id, $pray_class);
        } else if ($class == 'get_list') {
            $result = $this->salah_model->get_adhan_list();
        }

        echo json_encode($result);
    }
}
