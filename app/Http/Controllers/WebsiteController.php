<?php 

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class WebsiteController extends Controller{


	/**
     * Create basic response to return to angular
     *
     * @param integer $code
     * @param string $text
     * @return array
     */
    protected function createBasicResponse($code, $text){
    	return [
    		'error' => [
    			'no' => $code,
    			'text' => $text
    		]
    	];
    }


    /**
     * Get amazon s3 url for the file
     *
     * @param File $file
     * @return string
     */
    protected function getAmazonS3Url($fileName)
    {
        $bucket = config('filesystems.disks.s3.bucket');
        $s3 = \Storage::disk('s3');

        $s3Client = \Aws\S3\S3Client::factory(array(
            'credentials' => array(
                'key'    => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret')
            ),
            'region' => config('filesystems.disks.s3.region'),
            'signature' => 'v4',
            'version' => 'latest'
        ));

        $command = $s3Client->getCommand('GetObject', [
            'Bucket'                     => $bucket,
            'Key'                        => $fileName
        ]);
        return (string)$s3Client->createPresignedRequest($command, '+30 minutes')->getUri();
    }

    /**
     * APlly users timezone to the date
     *
     * @param Carbon $carbonObject
     * @param string $timezone
     * @return Carbon
     */
    protected function applyTimezone($carbonObject, $timezone)
    {
        if(!$carbonObject){return NULL;}
        return $carbonObject->setTimezone($timezone);
    }
}