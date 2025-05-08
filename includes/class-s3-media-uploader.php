<?php

class S3MediaUploader {

    public function __construct() {
        add_filter('wp_handle_upload', array($this, 'upload_to_s3'), 10, 2);
        add_filter('wp_get_attachment_url', array($this, 'get_s3_attachment_url'), 10, 2);
    }

    public function upload_to_s3($upload, $context) {
        //error_log('uploading_to_s3');
        $s3Client = s3_connect();
        $options = get_option('s3_media_uploader_options');
        $bucket_name = $options['s3_bucket_name'];

        // Get the current upload directory structure
        $upload_dir = 'wp-content/uploads'.wp_upload_dir()['subdir'].'/';
        

        // Upload the file to S3
        try {
            $file_path = $upload['file'];
            $file_name = basename($file_path);
            $s3_key = $upload_dir . $file_name;
            $result = $s3Client->putObject([
                'Bucket' => $bucket_name,
                'Key'    => $s3_key,
                'SourceFile' => $file_path,
                'ACL'    => 'public-read', // Set the ACL as needed
            ]);

            // Get the S3 URL of the uploaded file
            $s3_url = $result['ObjectURL'];

            // Delete the local file
            unlink($file_path);

            // Update the upload array to use the S3 URL
            $upload['url'] = $s3_url;

        } catch (Aws\Exception\AwsException $e) {
            // Handle the error
            error_log('S3 upload error: ' . $e->getMessage());
        }
        return $upload;
    }

    public function get_s3_attachment_url($url, $post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'posts';
        $s3_url = $wpdb->get_var($wpdb->prepare('SELECT guid FROM '.$table_name.' WHERE "ID" = %d', $post_id));
        
        return $s3_url ? $s3_url : $url;
    }
}