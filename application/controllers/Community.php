<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Community extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('community_model');

        $this->load->library('form_validation');
    }

    public function notice_list()
    {
        echo $this->community_model->get_notice();
    }

    public function lists($class)
    {
        $this->community_model->get_list($class);
    }

    public function like($class)
    {
        $this->community_model->like_object($class);
    }

    public function write($class)
    {
        $path_info = array();

        switch ($class) {
            case 'article':
                if (isset($_FILES['image'])) {
                    $config = array(
                        'allowed_types' => 'gif|jpg|jpeg|png',
                        'max_size' => '3072',
                        'encrypt_name' => TRUE
                    );
                    $config['upload_path'] = 'upload path can\'t be shared';

                    switch ($this->input->post('class')) {
                        case 'F':
                            $config['upload_path'] .= 'free/';
                            break;
                        case 'S':
                            $config['upload_path'] .= 'share/';
                            break;
                        case 'Q':
                            $config['upload_path'] .= 'qna/';
                            break;
                        default:
                            break;
                    }

                    $this->load->library('upload', $config);

                    if (!$this->upload->do_upload('image')) {
                        $path_info['image_path'] = '';
                        #$this->upload->display_errors('', '');
                    } else {
                        $path_info['image_path'] = $this->upload->data('full_path');
                    }
                } else if (isset($_FILES['video'])&&
                            isset($_FILES['thumbnail'])) {
                    # video upload part
                    $config = array(
                        'upload_path' => '/tmp/video/',
                        'allowed_types' => 'mp4|3gp|mov',
                        'max_size' => '102400',
                        'encrypt_name' => TRUE
                    );
                    $this->load->library('upload', $config);
                    
                    if ($this->upload->do_upload('video')) {
                        $path_info['video_path'] = $this->upload->data('full_path');
                    }
                    # thumbnail upload part
                    $config = array(
                        'upload_path' => '/tmp/image/',
                        'allowed_types' => 'jpg|jpeg|png|gif',
                        'max_size' => '3024',
                        'encrypt_name' => TRUE
                    );
                    $this->upload->initialize($config);

                    if ($this->upload->do_upload('thumbnail')) {
                        $path_info['thumb_path'] = $this->upload->data('full_path');
                    }
                }
                break;
            case 'comment':
                $config = array();
                $config['upload_path'] = 'upload path can\'t be shared';

                $config['allowed_types'] = 'gif|jpg|jpeg|png';
                $config['max_size'] = '3072';
                $config['encrypt_name'] = TRUE;
                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('image')) {
                    $path_info['image_path'] = '';
                    #$this->upload->display_errors('', '');
                } else {
                    $path_info['image_path'] = $this->upload->data('full_path');
                }
                break;
            case 'notice':
                break;
            default:
                break;
        }

//        $this->form_validation->set_rules($rules);
        $this->community_model->write_object($class, $path_info);
    }

    public function delete($class)
    {
        $this->community_model->delete_object($class);
    }

    public function modify($class)
    {
        $full_path = '';

        switch ($class) {
            case 'article':
                $config = array();
                $config['upload_path'] = 'upload path can\'t be shared';

                switch ($this->input->post('class')) {
                    case 'F':
                        $config['upload_path'] .= 'free/';
                        break;
                    case 'S':
                        $config['upload_path'] .= 'share/';
                        break;
                    case 'Q':
                        $config['upload_path'] .= 'qna/';
                        break;
                    default:
                        break;
                }

                $config['allowed_types'] = 'gif|jpg|jpeg|png';
                $config['max_size'] = '3072';
                $config['encrypt_name'] = TRUE;
                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('image')) {
                    $full_path = '';
                    #$this->upload->display_errors('', '');
                } else {
                    $full_path = $this->upload->data('full_path');
                }

                $rules = array(
                    array('field' => 'session_id', 'label' => 'User session id', 'rules' => 'trim|required'),
                    array('field' => 'class', 'label' => 'Class', 'rules' => 'required'),
                    array('field' => 'content', 'label' => 'Content', 'rules' => 'required'),
                    array('field' => 'address', 'label' => 'Address', 'rules' => 'required')
                );
                break;
            case 'comment':
                $config = array();
                $config['upload_path'] = 'upload path can\'t be shared';

                $config['allowed_types'] = 'gif|jpg|jpeg|png';
                $config['max_size'] = '3072';
                $config['encrypt_name'] = TRUE;
                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('image')) {
                    $full_path = '';
                    #$this->upload->display_errors('', '');
                } else {
                    $full_path = $this->upload->data('full_path');
                }

                $rules = array(
                    array('field' => 'article_id', 'label' => 'Board article id', 'rules' => 'trim|required'),
                    array('field' => 'session_id', 'label' => 'User session id', 'rules' => 'trim|required'),
                    array('field' => 'content', 'label' => 'Content', 'rules' => 'required')
                );
                break;
            case 'notice':
                $rules = array(
                    array('field' => 'title', 'label' => 'Notice id', 'rules' => 'required'),
                    array('field' => 'contents', 'label' => 'Notice id', 'rules' => 'required')
                );
                break;
            default:
                break;
        }

        $this->form_validation->set_rules($rules);
        $this->community_model->modify_object($class, $full_path);
    }
}
