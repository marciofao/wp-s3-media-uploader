<?php

function s3_connect() {
    // Include the AWS SDK for PHP
    require_once WP_S3_MEDIA_UPLOADER_DIR.'/vendor/autoload.php';

    $options = get_option('s3_media_uploader_options');
    $bucket_name = $options['s3_bucket_name'];
    $region = $options['s3_region'];
    $access_key = $options['s3_access_key'];
    $secret_key = $options['s3_secret_key'];

    // Create a new S3 client
    $s3ClientConfig = [
        'version' => 'latest',
        'region'  => $region,
    ];

    if (!empty($access_key) && !empty($secret_key)) {
        $s3ClientConfig['credentials'] = [
            'key'    => $access_key,
            'secret' => $secret_key,
        ];
    }

    $s3Client = new Aws\S3\S3Client($s3ClientConfig);

    // Check if the connection is successful
    try {
        $s3Client->headBucket(['Bucket' => $bucket_name]);
        return $s3Client;
    } catch (Aws\Exception\AwsException $e) {
        error_log('S3 Connection Error: ' . $e->getMessage());
        return false; // Connection failed
    }
}

