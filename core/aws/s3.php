<?php

use Aws\Command;
use Aws\S3\S3Client;
use Aws\S3\MultipartUploader;
use Aws\Exception\AwsException;

class OSC_AWS_S3 extends OSC_Object
{
    protected $_client = null;
    protected $_region = '';
    protected $_bucket = '';
    protected $_version = '';
    protected $_object_prefix = '';
    protected $_client_id = '';
    protected $_client_secret = '';

    protected $_s3_domain = null;
    protected $_var_dir_url = null;
    protected $_root_dir_url = null;
    protected $_storage_dir_url = null;
    protected $_s3_bucket_url = null;
    protected $_s3_cdn_url = null;

    /**
     * @throws Exception
     */
    public function __construct() {
        $this->getObjectPrefix();

        $config = OSC::systemRegistry('aws_s3');
        $this->_bucket = $config['bucket'];
        $this->_region = $config['region'];
        $this->_version = $config['version'];
        $this->_client_id = $config['key'];
        $this->_client_secret = $config['secret'];

        $this->_getClient();
    }

    /**
     * Connect to s3 client
     * @return S3Client|null
     */
    protected function _getClient() {
        if ($this->_client !== null) {
            return $this->_client;
        }

        $this->_client = new S3Client([
            'region' => $this->_region,
            'version' => $this->_version,
            'credentials' => [
                'key' => $this->_client_id,
                'secret' => $this->_client_secret
            ]
        ]);

        return $this->_client;
    }

    /**
     * Get store or master object prefix
     * @throws Exception
     */
    public function getObjectPrefix() {
        if ($this->_object_prefix) {
            return $this->_object_prefix;
        }

        $store_id = OSC::getStoreInfo()['store_id'];

        $this->_object_prefix = $store_id > 0 ? $store_id : 'master';

        return $this->_object_prefix;
    }

    /**
     * Function add prefix to file path, because multistore using 1 bucket
     * @param $file_path
     * @return string
     */
    protected function _getS3ObjectPath($file_path) {
        if (empty($file_path)) {
            return $this->_object_prefix;
        }

        return $this->_object_prefix . '/' . $file_path;
    }

    /**
     * Using for upload single file from local to s3
     * @param string $s3_file_path
     * @param string $local_file_path
     * @param array $options
     * @return string|null
     * @throws Exception
     */
    public function upload(
        $local_file_path = '',
        $s3_file_path = '',
        $options = [
            'overwrite' => false,
            'permission_access_file' => 'public-read'
        ]
    ) {
        /*
         * If file exist in s3 and not have option overwrite, return object url
         * Else put file to s3
        */
        if (!$options['overwrite'] && $this->doesObjectExist($s3_file_path)) {
            return $this->getObjectUrl($s3_file_path);
        }

        if (!file_exists($local_file_path)) {
            throw new Exception("File {$local_file_path} is not exists");
        }

        try {
            OSC::core('debug')->startProcess('s3Upload', $local_file_path);
            $s3_object = $this->_client->putObject([
                'Bucket' => $this->_bucket,
                'Key' => $this->_getS3ObjectPath($s3_file_path),
                'SourceFile' => $local_file_path,
                'ACL' => $options['permission_access_file'],
                'ContentType' => GuzzleHttp\Psr7\MimeType::fromFilename($local_file_path)
            ]);
            OSC::core('debug')->endProcess();
            if ($s3_object['@metadata']['statusCode'] == '200') {
                /* Remove file in storage server when upload s3 success */
                if (strpos($local_file_path, 'storage') !== false) {
                    unlink($local_file_path);
                }

                return $s3_object['ObjectURL'];
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return null;
    }

    /**
     * Using for upload large file from local to s3
     * @param string $s3_file_path
     * @param string $local_file_path
     * @param array $options
     * @return string|null
     * @throws Exception
     */
    public function uploadMultipart(
        $s3_file_path = '',
        $local_file_path = '',
        $options = [
            'overwrite' => false,
            'permission_access_file' => 'public-read'
        ]
    ) {
        /*
         * If file exist in s3 and not have option overwrite, return object url
         * Else put file to s3
        */
        if (!$options['overwrite'] && $this->doesObjectExist($s3_file_path)) {
            return $this->getObjectUrl($s3_file_path);
        }

        if (!file_exists($local_file_path)) {
            throw new Exception("File {$local_file_path} is not exists");
        }

        $uploader = new MultipartUploader(
            $this->_client,
            $local_file_path,
            [
                'Bucket' => $this->_bucket,
                'Key'    => $this->_getS3ObjectPath($s3_file_path),
                'ACL' => $options['permission_access_file'],
                'ContentType' => GuzzleHttp\Psr7\MimeType::fromFilename($local_file_path)
            ]
        );

        try {
            $result = $uploader->upload();

            if ($result['@metadata']['statusCode'] == '200') {
                return $result['ObjectURL'];
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return null;
    }

    /**
     * @param string $local_dir_path
     * @param string $s3_dir_path
     * @param array $options
     * @return bool
     * @throws Exception
     */
    public function uploadDirectory(
        $local_dir_path = '',
        $s3_dir_path = '',
        $options = [
            'overwrite' => false,
            'permission_access_file' => 'public-read'
        ]
    ) {
        $s3_dir_path = $this->_getS3ObjectPath($s3_dir_path);

        if (!is_dir($local_dir_path)) {
            throw new Exception("Folder {$local_dir_path} is not exists");
        }

        try {
            $this->_client->uploadDirectory(
                $local_dir_path,
                $this->_bucket,
                $s3_dir_path,
                [
                    'before' => function(Command $command) use ($options) {
                        $command['ACL'] = $options['permission_access_file'];
                    },

                ]
            );
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return true;
    }

    /**
     * @param $file_path
     * @return string
     * @throws Exception
     */
    public function headObject($file_path) {
        try {
            $s3_file_path = $this->_getS3ObjectPath($file_path);

            $result = $this->_client->headObject([
                'Bucket' => $this->_bucket,
                'Key' => $s3_file_path
            ]);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return $result;
    }

    /**
     * @param $file_path
     * @return string
     * @throws Exception
     */
    public function getObjectUrl($file_path = null) {
        try {
            $file_path = $this->_getS3ObjectPath($file_path);

            OSC::core('debug')->startProcess('s3GetObjectUrl', $file_path);
            $result = $this->_client->getObjectUrl($this->_bucket, $file_path);
            OSC::core('debug')->endProcess();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return $result;
    }

    /**
     * @param $file_path
     * @return bool
     * @throws Exception
     */
    public function doesObjectExist($file_path) {
        try {
            $file_path = $this->_getS3ObjectPath($file_path);

            OSC::core('debug')->startProcess('s3DoesObjectExist', $file_path);
            $result = $this->_client->doesObjectExist($this->_bucket, $file_path);
            OSC::core('debug')->endProcess();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return $result;
    }

    /**
     * @param $file_path
     * @return string
     * @throws Exception
     */
    public function getFileUrl($file_path) {
        return $this->getRootDirUrl() . '/' . $file_path;
    }

    /**
     * @param $file_path
     * @return bool
     * @throws Exception
     */
    public function doesStorageObjectExist($file_path) {
        try {
            $file_path = $this->_getS3ObjectPath($this->getStoragePath($file_path));

            OSC::core('debug')->startProcess('s3DoesStorageObjectExist', $file_path);
            $result = $this->_client->doesObjectExist($this->_bucket, $file_path);
            OSC::core('debug')->endProcess();

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return $result;
    }

    /**
     * @param $file_path
     * @param $file_destination
     * @return mixed
     * @throws Exception
     */
    public function download($file_path, $file_destination) {
        try {
            OSC::core('debug')->startProcess('s3Download', $file_path);
            $s3_file_url = $this->getObjectUrl($file_path);
            $response = OSC::core('network')->curl($s3_file_url, ['timeout' => 1800]);
            OSC::core('debug')->endProcess();

            if ($response['content'] === false || $response['content'] === null || $response['response_code'] !== 200) {
                throw new Exception('Cannot get content from URL: ' . $s3_file_url);
            }

            if (OSC::writeToFile($file_destination, $response['content'], ['chmod' => 0644]) === false) {
                throw new Exception('Cannot save to destination path');
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        @chown($file_destination, OSC_FS_USERNAME);
        @chgrp($file_destination, OSC_FS_USERNAME);
        chmod($file_destination, 0644);

        return $file_destination;
    }

    /**
     * @param $from_file_path
     * @param $destination_file_path
     * @param array $options
     * @return string|null
     * @throws Exception
     */
    public function copy(
        $from_file_path,
        $destination_file_path,
        $options = [
            'overwrite' => false,
            'permission_access_file' => 'public-read'
        ]
    ) {
        try {
            if (!$this->doesObjectExist($from_file_path)) {
                throw new Exception("File {$from_file_path} is not exists");
            }

            /*
             * If file exist in s3 and not have option overwrite, return object url
             * Else put file to s3
            */
            if (!$options['overwrite'] && $this->doesObjectExist($destination_file_path)) {
                return $this->getObjectUrl($destination_file_path);
            }

            OSC::core('debug')->startProcess('s3Copy', $from_file_path . ' to ' . $destination_file_path);
            $s3_object = $this->_client->copy(
                $this->_bucket,
                $this->_getS3ObjectPath($from_file_path),
                $this->_bucket,
                $this->_getS3ObjectPath($destination_file_path),
                $options['permission_access_file']
            );
            OSC::core('debug')->endProcess();

            if ($s3_object['@metadata']['statusCode'] == '200') {
                return $s3_object['ObjectURL'];
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return null;
    }

    /**
     * @param $file_path
     * @return bool
     * @throws Exception
     */
    public function delete($file_path) {
        $result = false;

        try {
            $s3_file_path = $this->_getS3ObjectPath($file_path);

            if (!$this->doesObjectExist($file_path)) {
                throw new Exception("File {$file_path} is not exists");
            }

            OSC::core('debug')->startProcess('s3Delete', $s3_file_path);
            $s3_object = $this->_client->deleteObject([
                'Bucket' => $this->_bucket,
                'Key' => $s3_file_path
            ]);
            OSC::core('debug')->endProcess();

            if ($s3_object['@metadata']['statusCode'] == '200' ||
                $s3_object['@metadata']['statusCode'] == '204'
            ) {
                $result = true;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return $result;
    }

    /**
     * @param $file_path
     * @return bool
     * @throws Exception
     */
    public function deleteStorageFile($file_path) {
        $storage_file_path = $this->getStoragePath($file_path);

        return $this->delete($storage_file_path);
    }

    /**
     * @param $from_file_path
     * @param $destination_file_path
     * @param array $options
     * @return bool
     * @throws Exception
     */
    public function rename(
        $from_file_path,
        $destination_file_path,
        $options = [
            'overwrite' => false,
            'permission_access_file' => 'public-read'
        ]
    ) {
        try {
            $this->copy($from_file_path, $destination_file_path, $options);
            $result = $this->delete($from_file_path);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return $result;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function getRootDirUrl() {
        if ($this->_root_dir_url) {
            return $this->_root_dir_url;
        }

        $this->_root_dir_url = $this->getObjectUrl();

        return $this->_root_dir_url;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function getS3CDNUrl() {
        if ($this->_s3_cdn_url) {
            return $this->_s3_cdn_url;
        }

        $this->_s3_cdn_url = OSC::enableCDN() ?
            OSC::systemRegistry('CDN_CONFIG')['base_url'] . '/' . $this->getObjectPrefix():
            $this->getRootDirUrl();

        return $this->_s3_cdn_url;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function getS3Domain() {
        if ($this->_s3_domain) {
            return $this->_s3_domain;
        }

        $prefix = 'https://';
        $postfix = '/' . $this->_object_prefix;
        $this->_s3_domain = OSC::core('string')->getStringBetween($this->getRootDirUrl(), $prefix, $postfix);

        return $this->_s3_domain;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function getS3BucketUrl() {
        if ($this->_s3_bucket_url) {
            return $this->_s3_bucket_url;
        }

        $postfix = '/' . $this->_object_prefix;
        $this->_s3_bucket_url = str_replace($postfix, '', $this->getRootDirUrl());

        return $this->_s3_bucket_url;
    }

    /**
     * @param $file_path
     * @return string
     * @throws Exception
     */
    public function getStoragePath($file_path) {
        return 'storage/' . $file_path;
    }

    /**
     * @param $file_path
     * @return string
     * @throws Exception
     */
    public function getStorageUrl($file_path) {
        return $this->getStorageDirUrl() . '/' . $file_path;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getStorageDirUrl() {
        if ($this->_storage_dir_url) {
            return $this->_storage_dir_url;
        }
        $this->_storage_dir_url = $this->getObjectUrl('storage');

        return $this->_storage_dir_url;
    }

    /**
     * @param $file_path
     * @return string
     * @throws Exception
     */
    public function getVarPath($file_path) {
        return 'var/' . $file_path;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getVarDirUrl() {
        if ($this->_var_dir_url) {
            return $this->_var_dir_url;
        }

        return $this->getObjectUrl('var');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getTmpDirPath() {
        $tmp_dir = 'tmp/' . mktime(0, 0, 1);

        return $this->getVarPath($tmp_dir);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getTmpFilePath($file_name) {
        return $this->getTmpDirPath() . '/' . $file_name;
    }

    /**
     * @param $file
     * @param $file_name
     * @return string
     * @throws Exception
     */
    public function tmpSaveFile($file, $file_name, $overwrite = false) {
        $tmp_file_path_local = OSC_Storage::tmpSaveFile($file, $file_name);
        $tmp_file_path_s3 = $this->getTmpFilePath($file_name);
        $options = [
            'overwrite' => $overwrite,
            'permission_access_file' => 'public-read'
        ];

        return $this->upload($tmp_file_path_local, $tmp_file_path_s3, $options);
    }

    /**
     * @param $url
     * @return bool
     * @throws Exception
     */
    public function tmpIsUrl($url) {
        return $this->tmpGetFileNameFromUrl($url) !== '';
    }

    /**
     * @param $url
     * @return mixed|string
     * @throws Exception
     */
    public function tmpGetFileNameFromUrl($url) {
        $url = preg_replace('/^https(:.+)$/i', 'http\\1', $url);

        $url = str_replace(preg_replace('/^https(:.+)$/i', 'http\\1', $this->getRootDirUrl() . '/'), '', $url);

        if (!preg_match('/^var\/+tmp\/+[0-9]+\/+([^\/]+.*)$/i', $url, $matches)) {
            return '';
        }

        return $matches[1];
    }

    /**
     * @param $file_path
     * @return false|int
     * @throws Exception
     */
    public function getLastModifiedFile($file_path) {
        $head_object = $this->headObject($this->getStoragePath($file_path));
        
        return strtotime($head_object->get('LastModified')->format('m/d/Y H:i:s'));
    }
}
