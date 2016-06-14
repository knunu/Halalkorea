<?php
/**
 * Created by PhpStorm.
 * User: Purple
 * Date: 2015. 11. 18.
 * Time: 오전 9:02
 */

require 'aws.php';

class Scan_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function save_letusknow($product_image_path,
                                   $ingredient_image_path,
                                   $barcode,
                                   $product_name,
                                   $company_name,
                                   $halal_certified, $comment)
    {

        //echo $product_image_path;
//        echo 'test\n';
//        echo $ingredient_image_path;
//        echo '\ntest\n';
        //echo basename($product_image_path);


        $response = array();
        $response['code'] = 500;
        $response['message'] = 'Halalkorea Internal Server Error';
        $response['result'] = null;

        $product_image_name = basename($product_image_path);
        $ingredient_image_name = basename($ingredient_image_path);

        if (!saveFileToS3($product_image_path, 'this information can\'t be shared', $product_image_name)) {
            $response['message'] = 'Saving product image to s3 failed';
            return json_encode($response);
        }
        if (!saveFileToS3($ingredient_image_path, 'this information can\'t be shared', $ingredient_image_name)) {
           $response['message'] = 'Saving ingredient image to s3 failed';
            return json_encode($response);
        }


        $result = $this->db->
        query('this information can\'t be shared');

        $response['code'] = 200;
        $response['message'] = "Success";

        unlink($product_image_path);
        unlink($ingredient_image_path);

        return json_encode($response);

//        $result = $this->db->query("SELECT * FROM halalkorea.restaurants where category_id = " . $category_id . " ORDER BY id ASC");
//        $result_array = $result->result_array();
//
//        return json_encode($result_array);

    }

    public function saveLetUsKnow($information)
    {
        $product_image    = $information['product_image'];
        $ingredient_image = $information['ingredient_image'];
        $barcode          = $information['barcode'];
        $product_name     = $information['product_name'];
        $company_name     = $information['company_name'];
        $halal_certified  = $information['halal_certified'];
        $comment          = $information['comment'];

        $product_image_name    = basename($product_image);
        $ingredient_image_name = basename($ingredient_image);

        //unlink($product_image);
        //unlink($ingredient_image);

        $query = 'this information can\'t be shared';

        return $this->db->query($query);
    }

    public function saveLetUsKnowImage($image)
    {
        $image_name = basename($image);

        if (!saveFileToS3($image, 'this information can\'t be shared', $image_name)) {
            return False;
        }
        return True;
    }
}

?>
