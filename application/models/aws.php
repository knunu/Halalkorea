<?php
// Include the AWS SDK using the Composer autoloader.
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;


function deleteFileInS3($folder, $filename)
{

    $config = array('credentials' => 'this information can\'t be shared');

    $s3 = S3Client::factory($config);
    date_default_timezone_set('Asia/Seoul');

    try {
        $result = $s3->deleteObject(array(
            'Bucket' => 'halalkorea',
            'Key' => $folder . '/' . $filename,
        ));

        return $result;
    } catch (S3Exception $e) {

        return null;
    }


}

function saveFileToS3($file_path, $folder, $filename)
{

    $config = array('credentials' => 'this information can\'t be shared');

    $s3 = S3Client::factory($config);
    date_default_timezone_set('Asia/Seoul');

    try {
        $result = $s3->putObject(array(
            'Bucket' => 'halalkorea',
            'Key' => $folder . '/' . $filename,
            'SourceFile' => $file_path,
            'ACL' => 'public-read'
        ));
        
        return $result;
    } catch (S3Exception $e) {
        return null;
    }

}

function getURLFromS3($folder, $filename)
{

    $config = array('credentials' => 'this information can\'t be shared');

    // Instantiate the client.
    $s3 = S3Client::factory($config);
    date_default_timezone_set('Asia/Seoul');
    try {
        return $s3->getObjectUrl('halalkorea', $folder . '/' . $filename);
    } catch (S3Exception $e) {
        echo $e->getMessage() . "\n";
        return '';
    }

}


?>

