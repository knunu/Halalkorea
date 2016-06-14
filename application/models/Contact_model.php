<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Contact_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add($email, $content)
    {
        $result = array();

        if (!$email ||
            !$content ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {


            $result['code'] = 400;
            $result['message'] = "Invalid Input";

            return json_encode($result);

        } else {


            $query = "INSERT INTO halalkorea.contact (email, content) VALUES(?,?)";
            $db_result = $this->db->query($query, array($email, $content));

            if ($db_result) {

                $result['code'] = 200;
                $result['message'] = 'Success';

                $this->load->library('email');

                $this->email->from("platformstory@gmail.com");
                $this->email->to("halalkorea@platformstory.com");
                $this->email->subject(sprintf("Halalkorea support contact from %s", $email));
                $this->email->message($content);

                if ($this->email->send()) {

                    return json_encode($result);

                } else {

                    $result['code'] = '600';
                    $result['message'] = $this->email->print_debugger();
                    return json_encode($result);
                }

            } else {

                $result['code'] = 500;
                $result['message'] = "Internal Server Error";

                return json_encode($result);
            }

        }


    }

}

?>

