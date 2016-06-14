<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once 'aws.php';

class Community_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('date');
        $this->load->library('s3');
        $this->load->library('util');
        $this->load->library('user_info');
    }

    public function get_notice()
    {
        $notice_type = $this->input->post('class');

        $result = $this->db->get_where('community_notice', array('should_popup' => 1, 'notice_type' => $notice_type));

        if ($result) {
            $response = array();
            $response['code'] = 200;
            $response['message'] = 'Success';
            $response['result'] = $result->result_array();
            return json_encode($response);
        } else {
            $response = array();
            $response['code'] = 400;
            $response['message'] = 'cannot get proper notice';
            $response['result'] = null;
            return json_encode($response);
        }

    }

    public function get_list($class)
    {
        $find_option = array(
            'session_id' => $this->input->post('session_id')
        );

        $email = $this->user_info->get($find_option, 'email');
        $login_flag = $this->user_info->get($find_option, 'login_flag');
        $since_id = $this->input->post('since_id');
        $max_id = $this->input->post('max_id');
        $count = $this->input->post('count');

        if ($class == 'article') {
            $query = "SELECT
            CONCAT(C.class, C.id) article_id,
            C.content,
            CASE WHEN L.email = '$email' THEN 'TRUE' ELSE 'FALSE' END like_checker,
             C.like_count,
             C.comment_count,
             CASE WHEN ( C.email = '$email' && C.login_flag = '$login_flag' ) THEN 'TRUE' ELSE 'FALSE' END ownership,
             B.name user_name,
             B.photo user_photo,
             C.write_date,
             C.image,
             C.video,
             C.thumbnail,
             C.address,
             C.is_faq
				FROM halalkorea.community_article AS C
				JOIN halalkorea.basic_user AS B ON (C.email = B.email
				AND C.login_flag = B.login_flag)
				LEFT JOIN halalkorea.community_article_like AS L ON (C.id = L.id 
					AND L.email = '$email')
				WHERE C.class = '" . $this->input->post('class') . "'";

            if ($since_id != '') {
                $since_id = substr($since_id, 1, strlen($since_id));
                $query .= " AND C.id > " . $since_id;
            }

            if ($max_id != '') {
                $max_id = substr($max_id, 1, strlen($max_id));
                $query .= " AND C.id < " . $max_id;
            }

            $query .= " ORDER BY C.id desc";
            if ($count != '') {
                $query .= " limit " . $count;
            }

            $result = $this->db->query($query)->result_array();

        } else if ($class == 'comment') {
            $query =
            "SELECT 
                C.comment_id, 
                C.content, 
                CASE WHEN L.email = '$email' THEN 'TRUE' ELSE 'FALSE' END like_checker,
                C.like_count, 
                CASE WHEN ( C.email = '$email' && C.login_flag = '$login_flag' ) THEN 'TRUE' ELSE 'FALSE' END ownership,
                B.name, 
                B.photo, 
                C.image,
                C.write_date
            FROM halalkorea.community_comment AS C
                JOIN halalkorea.basic_user AS B ON (C.email = B.email
                AND C.login_flag = B.login_flag)
                LEFT JOIN halalkorea.community_comment_like AS L ON (C.comment_id = L.id AND L.email = '$email')
            WHERE C.article_id = '" . $this->input->post('article_id') . "'";

            if ($since_id != '') {
                $query .= " AND C.comment_id > " . $since_id;
            }

            if ($max_id != '') {
                $query .= " AND C.comment_id < " . $max_id;
            }

            $query .= " ORDER BY C.comment_id ";
            if ($count != '') {
                $query .= " limit " . $count;
            }

            $result = $this->db->query($query)->result_array();

        } else if ($class == 'notice') {
            $result = $this->db->order_by('id', 'DESC')->get_where('notice')->result_array();
        }

        echo json_encode($result);
    }

    public function get_image_path($class, $id) {

        switch($class) {
            case 'article':
                $target_table = 'community_article';
                $find_option = array(
                    'id' => $id
                );
                break;
            case 'comment':
                $target_table = 'community_comment';
                $find_option = array(
                    'comment_id' => $id
                );
                break;
            case 'notice':
                $target_table = 'community_notice';
                $find_option = array(
                    'id' => $id
                );
                break;
            default:
                break;
        }

        if ($this->db->get_where($target_table, $find_option)->num_rows() > 0) {
            return $this->db->get_where($target_table, $find_option)->row()->image;
        }
        return '';
    }
    public function like_object($class)
    {
        $result = array();
        $find_option = array(
            'session_id' => $this->input->post('session_id')
        );
        $email = $this->user_info->get($find_option, 'email');

        if ($email != NULL) {
            if ($class == 'article') {
                $article_id = $this->input->post('article_id');
                $article_class = substr($article_id, 0, 1);
                $article_raw_id = substr($article_id, 1, strlen($article_id));
                $data = array(
                    'id' => $article_raw_id,
                    'email' => $email,
                    'class' => $article_class,
                );

                $user = $this->db->get_where('community_article_like', $data)->row();

                if ($user == NULL && $email != NULL) { // if there is no LIKE
                    $this->db->insert('community_article_like', $data);
                } else { // if there is already LIKE
                    $this->db->delete('community_article_like', $data, 1);
                }

                // count the article's LIKE
                $this->db->select('COUNT(*) like_count');
                $this->db->from('community_article_like');
                $this->db->where('class', $article_class);
                $this->db->where('id', $article_raw_id);
                $query = $this->db->get();

                // update community_article.like_count
                $this->db->where('id', $article_raw_id);
                $this->db->update('community_article', array('like_count' => $query->row()->like_count));

            } else if ($class == 'comment') {
                $comment_id = $this->input->post('comment_id');
                $data = array(
                    'id' => $comment_id,
                    'email' => $email,
                );

                $user = $this->db->get_where('community_comment_like', $data)->row();
                if ($user == NULL) { // if there is no LIKE
                    $this->db->insert('community_comment_like', $data);
                } else { // if there is already LIKE
                    $this->db->delete('community_comment_like', $data, 1);
                }

                // count the comment's LIKE
                $this->db->select('COUNT(*) like_count');
                $this->db->from('community_comment_like');
                $this->db->where('id', $comment_id);
                $query = $this->db->get();

                // update community_comment.like_count
                $this->db->where('comment_id', $comment_id);
                $this->db->update('community_comment', array('like_count' => $query->row()->like_count));
            }
            $result['code'] = '200';
            $result['like_count'] = $query->row()->like_count;

        } else {
            $result['code'] = '400';
            $result['error'] = 'email is NULL';
        }

        echo json_encode($result);
    }

    public function write_object($class, $path_info)
    {
        $result = array();

        // write available when user logged in
        $result['received_session_id'] = $this->input->post('session_id');
        if ($result['received_session_id'] != '0' && $result['received_session_id'] != '') {
            $find_option = array(
                'session_id' => $this->input->post('session_id')
            );
            if ($class == 'article') {
                if (isset($path_info['video_path'])) {
                    $video_path = 'http://d2f318m28dvnfn.cloudfront.net/'.basename($path_info['video_path']);
                    if (!saveFileToS3($path_info['video_path'], 'community/video', basename($path_info['video_path']))) {
                        $result['code'] = '410';
                        $result['message'] = 'It is failed to upload video to S3';
                        echo json_encode($result);
                    }
                    unlink($path_info['video_path']);
                } else {
                    $video_path = '';
//                    $result['code'] = '420';
//                    $result['message'] = 'There is no video_path';
//                    echo json_encode($result);
                }

                if (isset($path_info['thumb_path'])) {
                    $thumb_path = 'http://halalkorea.s3.amazonaws.com/community/thumbnail/'.basename($path_info['thumb_path']);
                    if (!saveFileToS3($path_info['thumb_path'], 'community/thumbnail', basename($path_info['thumb_path']))) {
                        $result['code'] = '410';
                        $result['message'] = 'It is failed to upload video thumbnail image to S3';
                        echo json_encode($result);
                    }
                    unlink($path_info['thumb_path']);
                } else {
                    $thumb_path = '';
//                    $result['code'] = '420';
//                    $result['message'] = 'There is no thumb_path';
//                    echo json_encode($result);
                }

                $data = array(
                    'class' => $this->input->post('class'),
                    'email' => $this->user_info->get($find_option, 'email'),
                    'content' => $this->input->post('content'),
                    'comment_count' => '0',
                    'like_count' => '0',
                    'address' => $this->input->post('address'),
                    'image' => isset($path_info['image_path']) ? $path_info['image_path'] : '',
                    'video' => $video_path,
                    'thumbnail' => $thumb_path,
                    'write_date' => date('Y-m-d H:i:s', now()),
                    'login_flag' => $this->input->post('login_flag')
                );
                if ($data['login_flag'] == '') unset($data['login_flag']);
                $result['path_info'] = $path_info;

            } else if ($class == 'comment') {
                $data = array(
                    'article_id' => $this->input->post('article_id'),
                    'email' => $this->user_info->get($find_option, 'email'),
                    'content' => $this->input->post('content'),
                    'comment_count' => '0',
                    'like_count' => '0',
                    'image' => isset($path_info['image_path']) ? $path_info['image_path'] : '',
                    'write_date' => date('Y-m-d H:i:s', now()),
                    'login_flag' => $this->user_info->get($find_option, 'login_flag')
                );
                if ($data['login_flag'] == '') unset($data['login_flag']);

                $article_class = substr($data['article_id'], 0, 1);
                $article_raw_id = substr($data['article_id'], 1, strlen($data['article_id']));

                //get article's comment count by class and id
                $current_comment_count = $this->db->get_where('community_article', array('class' => $article_class, 'id' => $article_raw_id))->row()->comment_count;

            } else if ($class == 'notice') {
                $data = array(
                    'title' => $this->input->post('title'),
                    'content' => $this->input->post('content'),
                    'write_date' => date('Y-m-d H:i:s', now())
                );
            }

            // check session validation
            if ($data['email'] == '') {
                $result['code'] = '400';
                $result['error'] = 'double login has been detected. your session has expired!';

            } // insert data to database
            else if ($this->db->insert('community_' . $class, $data)) {
                $result['code'] = '200';
                
                // update article's comment count
                if ($class == 'comment') {
                    $condition = array(
                        'class' => $article_class,
                        'id' => $article_raw_id
                    );
                    $this->db->where($condition);
                    $this->db->update('community_article', array('comment_count' => $current_comment_count + 1));
                }
            } else {
                $result['code'] = '400';
                $result['error'] = 'DB insertion error!';
            }
        } else {
            $result['code'] = '400';
            $result['error'] = 'session invalid error!';
        }

        echo json_encode($result);
    }

    public function delete_object($class)
    {
        $result = $this->util->setRetCode(SUCCESS);
        // delete available when user logged in
        $received_session_id = $this->input->post('session_id');

        if ($received_session_id != '0' && $received_session_id != '') {

            if ($class == 'article') {
                $article_id = $this->input->post('article_id');
                $article_class = substr($article_id, 0, 1);
                $article_raw_id = substr($article_id, 1, strlen($article_id));

                // delete article from db, comments related to the article from db
                if ($this->db->delete('community_article', array('class' => $article_class, 'id' => $article_raw_id)) && $this->db->delete('community_comment', array('article_id' => $article_id))) {
                    if ($this->get_image_path($class, $article_raw_id)) {
                        unlink($this->get_image_path($class, $article_raw_id));
                    }
                    $result['data']['received_session_id'] = $received_session_id;
                } else {
                    $result = $this->util->setRetCode(ERR_DB_NODATA);
                }
            } else if ($class == 'comment') {
                $comment_id = $this->input->post('comment_id');

                // get article's comment count by comment id
                $article_id = $this->db->get_where('community_comment', array('comment_id' => $comment_id))->row()->article_id;
                $article_class = substr($article_id, 0, 1);
                $article_raw_id = substr($article_id, 1, strlen($article_id));
                $current_comment_count = $this->db->get_where('community_article', array('class' => $article_class, 'id' => $article_raw_id))->row()->comment_count;

                // delete comments from db
                if ($this->db->delete('community_comment', array('comment_id' => $comment_id))) {
                    if ($this->get_image_path($class, $comment_id) != '') {
                        unlink($this->get_image_path($class, $comment_id));
                    }

                    // update article's comment count
                    $condition = array(
                        'class' => $article_class,
                        'id' => $article_raw_id
                    );
                    $this->db->where($condition);
                    $this->db->update('community_article', array('comment_count' => $current_comment_count - 1));

                } else {
                    $result = $this->util->setRetCode(ERR_DB_NODATA);
                }
            } else {
                $result = $this->util->setRetCode(ERR_FAIL);
            }
        } else {
            $result = $this->util->setRetCode(ERR_MSG_INVALID_SESSION_ID);
        }

        echo json_encode($result);
    }

    /**
     * general modify function
     * @param $class object : class such as 'article', 'comment', etc...
     * @param $full_path : image path
     */
    public function modify_object($class, $full_path)
    {
        $result = array();
        $find_option = array(
            'session_id' => $this->input->post('session_id')
        );
        // modify available when user logged in
        if ($find_option['session_id'] != '0' && $find_option['session_id'] != '') {
            if ($class == 'article') {
                $article_id = $this->input->post('article_id');
                $condition = array(
                    'class' => substr($article_id, 0, 1),
                    'id' => substr($article_id, 1, strlen($article_id))
                );
                $data = array(
                    'content' => $this->input->post('content'),
                    'address' => $this->input->post('address'),
                    'image' => $full_path,
                    'write_date' => date('Y-m-d H:i:s', now())
                );

                if ($full_path == '') {
                    if ($this->get_image_path($class, $condition['id'])) {
                        unlink($this->get_image_path($class, $condition['id']));
                    }
                }
            } else if ($class == 'comment') {
                $condition = array(
                    'comment_id' => $this->input->post('comment_id')
                );
                $data = array(
                    'content' => $this->input->post('content'),
                    'image' => $full_path,
                    'write_date' => date('Y-m-d H:i:s', now())
                );

                if ($full_path == '') {
                    if ($this->get_image_path($class, $condition['comment_id'])) {
                        unlink($this->get_image_path($class, $condition['comment_id']));
                    }
                }
            } else if ($class == 'notice') {
                $condition = array(
                    'id' => $this->input->post('notice_id')
                );
                $data = array(
                    'title' => $this->input->post('title'),
                    'content' => $this->input->post('content'),
                    'write_date' => date('Y-m-d H:i:s', now())
                );
            }

            // check session validation
            if ($this->user_info->get($find_option, 'email') == '') {
                $result['code'] = '400';
                $result['message'] = 'double login has been detected. your session has expired!';
                echo json_encode($result);
                return;
            }

            // update data from database
            $this->db->where($condition);
            if ($this->db->update("community_$class", $data)) {
                $result['code'] = '200';
            } else {
                $result['code'] = '400';
                $result['message'] = 'DB update error!';
            }
        } else {
            $result['code'] = '400';
            $result['message'] = 'session invalid error!';
        }

        echo json_encode($result);
    }
}
