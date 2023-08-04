<?php
class Helper_Post_Post
{
    public function formatPostApi(Model_Post_Post_Collection $post_collection)
    {
        $result = [];

        if ($post_collection->length() > 0) {

            foreach ($post_collection as $post) {
                $image_url = $post->data['image'] ? OSC::core('aws_s3')->getStorageUrl($post->data['image']) : '';
                $result[] = [
                    'id' => $post->data['post_id'],
                    'title' => $post->data['title'],
                    'author' => $post->getAuthor(),
                    'description' => $post->data['description'],
                    'url' => $post->getDetailUrl(false),
                    'image' => OSC::wrapCDN(OSC::helper('core/image')->imageOptimize($image_url, 500, 500, false, true)),
                    'added_timestamp' => (int)$post->data['added_timestamp'],
                    'modified_timestamp' => (int)$post->data['modified_timestamp']
                ];
            }

        }

        return $result;
    }

    public function formatPostCollectionApi(Model_Post_Collection $collection){
        $result = [];
        if($collection) {
            $result = [
                'collection_id' => $collection->data['collection_id'],
                'title' => $collection->data['title'],
                'slug' => $collection->data['slug'],
                'description' => $collection->data['description'],
                'image' => OSC::wrapCDN($collection->getImageUrl()),
                'url' => $collection->getDetailUrl()
            ];
        }

        return $result;
    }

    public function formatPostAuthorApi(Model_Post_Author $author) {
        $result = [];
        if ($author) {
            $result = [
                'author_id'     => $author->data['author_id'],
                'name'          => $author->data['name'],
                'slug'          => $author->data['slug'],
                'description'   => $author->data['description'],
                'avatar'        => $author->getAvatarUrl(),
            ];
        }

        return $result;
    }

    public function saveFooterBannerOnS3($data_image, $model_image) {
        if ($data_image != $model_image) {
            if (!$data_image) {
                $data_image = '';
            } else {
                $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($data_image);
                if (!OSC::core('aws_s3')->doesObjectExist($tmp_image_path_s3)) {
                    $data_image = $model_image;
                } else {
                    $filename = 'post/' . str_replace('post.', '', $data_image);
                    $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);
                    try {
                        OSC::core('aws_s3')->copy($tmp_image_path_s3, $storage_filename_s3);
                        $data_image = $filename;
                    } catch (Exception $ex) {
                        $data_image = $model_image;
                    }
                }
            }
        }

        return $data_image;
    }
}