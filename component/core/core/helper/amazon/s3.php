<?php

class Helper_Core_Amazon_S3 {

    protected $_client = null;

    protected function _getClient() {
        if ($this->_client !== null) {
            return $this->_client;
        }

        $this->_client = new Aws\S3\S3Client([
            'region' => S3_REGION,
            'version' => 'latest',
//		'http' => [
//			'proxy' => 'dls_supplier:u1yHUzAk3T1En3dtCZBY@203.113.174.97:3128'
//		],
            'credentials' => [
                'key' => S3_CREDENTIALS_KEY,
                'secret' => S3_CREDENTIALS_SECRET,
            ]
        ]);

        return $this->_client;
    }

    public function sendFile($file_path, $s3_file_name) {
        if (strpos(OSC_ENV, 'local-') === 0) {
            return OSC_Storage::storageSendFile($file_path, 'amazons3/' . $s3_file_name, true);
        }

        $s3_client = $this->_getClient();

        $source = fopen($file_path, 'rb');

        $s3_uploader = new Aws\S3\ObjectUploader($s3_client, S3_BUCKET, 'storage/' . $s3_file_name, $source, 'public-read');

        do {
            try {
                $result = $s3_uploader->upload();

                if ($result["@metadata"]["statusCode"] == '200') {
                    return $result["ObjectURL"];
                }
            } catch (Aws\Exception\AwsException\MultipartUploadException $e) {
                rewind($source);
                $s3_uploader = new Aws\S3\MultipartUploader($s3_client, $source, ['state' => $e->getState(), 'bucket' => S3_BUCKET, 'key' => 'storage/' . $s3_file_name]);
            }
        } while (!isset($result));

        return null;
    }

    public function fileExists($s3_file_name) {
        if (strpos(OSC_ENV, 'local-') === 0) {
            return OSC_Storage::isExists('amazons3/' . $s3_file_name);
        }

        return $this->_getClient()->doesObjectExist(S3_BUCKET, 'storage/' . $s3_file_name);
    }

}
