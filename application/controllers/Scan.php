<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Scan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('util');
        $this->load->model('scan_model');
    }

    public function letusknow2()
    {

        $response = array();
        $response['code'] = 500;
        $response['message'] = 'Halalkorea Internal Server Error';
        $response['result'] = null;

        $product_image_path = '';
        $ingredient_image_path = '';

        $config = array();
        $config['upload_path'] = 'upload path can\'t be shared';
        $config['allowed_types'] = 'gif|jpg|jpeg|png';
        $config['max_size'] = '3072';
        $config['encrypt_name'] = TRUE;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('product_image')) {
            $response['message'] = "Didn't receive product image";
            echo json_encode($response);
        } else {
            $product_image_path =
                $this->upload->data('full_path');
        }

        if (!$this->upload->do_upload('ingredient_image')) {
                 $response['message'] = "Didn't receive ingredient image";
            echo json_encode($response);
        } else {
            $ingredient_image_path =
                $this->upload->data('full_path');
        }


        echo $this->scan_model->
        save_letusknow(
            $product_image_path,
            $ingredient_image_path,
            $this->input->post('barcode'),
            $this->input->post('product_name'),
            $this->input->post('company_name'),
            $this->input->post('halal_certified'),
            $this->input->post('comment'));


    }

    public function letusknow()
    {
        $result = $this->util->setRetCode(SUCCESS);

        $barcode = $this->input->post('barcode');
        if ($barcode == NULL) {
            $result = $this->util->setRetCode(ERR_MSG_INVALID_VALUE);
            echo json_encode($result);
            return;
        }

        $config = array();
        $config['upload_path'] = 'upload path can\'t be shared';
        $config['allowed_types'] = 'gif|jpg|jpeg|png';
        $config['max_size'] = '3072';
        $config['encrypt_name'] = TRUE;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('product_image')) {
            $result = $this->util->setRetCode(ERR_MSG_INVALID_PRODUCT_IMAGE);
            echo json_encode($result);
            return;
        } else {
            $product_image_path = $this->upload->data('full_path');
        }

        if (!$this->upload->do_upload('ingredient_image')) {
            $result = $this->util->setRetCode(ERR_MSG_INVALID_INGREDIENT_IMAGE);
            echo json_encode($result);
            return;
        } else {
            $ingredient_image_path = $this->upload->data('full_path');
        }

        $product_flag = $this->scan_model->saveLetUsKnowImage($product_image_path);
        if (!$product_flag) {
            $result = $this->util->setRetCode(ERR_MSG_INVALID_PRODUCT_IMAGE);
            echo json_encode($result);
            return;
        }
         
        $ingredient_flag = $this->scan_model->saveLetUsKnowImage($ingredient_image_path);
        if (!$ingredient_flag) {
            $result = $this->util->setRetCode(ERR_MSG_INVALID_INGREDIENT_IMAGE);
            echo json_encode($result);
            return;
        }

        $information = array(
            'product_image'    => $product_image_path,
            'ingredient_image' => $ingredient_image_path,
            'barcode'          => $barcode,
            'product_name'     => $this->input->post('product_name'),
            'company_name'     => $this->input->post('company_name'),
            'halal_certified'  => $this->input->post('halal_certified'),
            'comment'          => $this->input->post('comment'));

        $data = $this->scan_model->saveLetUsKnow($information);
        if (!$data) {
            $result = $this->util->setRetCode(ERR_FAIL);
            echo json_encode($result);
            return;
        }

        echo json_encode($result);
    }

}

?>
