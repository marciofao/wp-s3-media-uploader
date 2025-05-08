<?php

class S3Settings {
    private $options;

    public function __construct() {
        $this->options = get_option('s3_media_uploader_options');
    }

    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('wp_ajax_check_s3_connection', array($this, 'check_s3_connection'));
    }

    public function add_admin_menu() {
        add_options_page('S3 Media Uploader', 'S3 Media Uploader', 'manage_options', 's3_media_uploader', array($this, 'options_page'));
    }

    public function settings_init() {
        register_setting('pluginPage', 's3_media_uploader_options', array($this, 'validate_options'));

        add_settings_section(
            's3_media_uploader_section',
            __('Configure your S3 settings', 'wp-s3-media-uploader'),
            null,
            'pluginPage'
        );

        add_settings_field(
            's3_bucket_name',
            __('Bucket Name <span style="color: red;">*</span>', 'wp-s3-media-uploader'),
            array($this, 'bucket_name_render'),
            'pluginPage',
            's3_media_uploader_section'
        );

        add_settings_field(
            's3_region',
            __('Region <span style="color: red;">*</span>', 'wp-s3-media-uploader'),
            array($this, 'region_render'),
            'pluginPage',
            's3_media_uploader_section'
        );

        add_settings_field(
            's3_access_key',
            __('Access Key', 'wp-s3-media-uploader'),
            array($this, 'access_key_render'),
            'pluginPage',
            's3_media_uploader_section'
        );

        add_settings_field(
            's3_secret_key',
            __('Secret Key', 'wp-s3-media-uploader'),
            array($this, 'secret_key_render'),
            'pluginPage',
            's3_media_uploader_section'
        );
    }

    public function bucket_name_render() {
        ?>
        <input type='text' name='s3_media_uploader_options[s3_bucket_name]' value='<?php echo isset($this->options['s3_bucket_name']) ? esc_attr($this->options['s3_bucket_name']) : ''; ?>' required>
        <?php
    }

    public function region_render() {
        ?>
        <input type='text' name='s3_media_uploader_options[s3_region]' value='<?php echo isset($this->options['s3_region']) ? esc_attr($this->options['s3_region']) : ''; ?>' required>
        <?php
    }

    public function access_key_render() {
        ?>
        <input type='text' name='s3_media_uploader_options[s3_access_key]' value='<?php echo isset($this->options['s3_access_key']) ? esc_attr($this->options['s3_access_key']) : ''; ?>'>
        <p>leave blank if using IAM roles</p>
        <?php
    }

    public function secret_key_render() {
        ?>
        <input type='password' name='s3_media_uploader_options[s3_secret_key]' value='<?php echo isset($this->options['s3_secret_key']) ? esc_attr($this->options['s3_secret_key']) : ''; ?>'>
        <p>leave blank if using IAM roles</p>
        <?php
    }

    public function options_page() {
        ?>
        <form action='options.php' method='post'>
            <h2>S3 Media Uploader</h2>
            <?php
            settings_fields('pluginPage');
            do_settings_sections('pluginPage');
            submit_button();
            ?>
        </form>
        <button id="check-s3-connection" class="button button-secondary">Check S3 Connection</button>
        <div id="s3-connection-result"></div>
        <?php
    }

    public function check_s3_connection() {
        check_ajax_referer('s3_media_uploader_nonce', 'nonce');

        $s3Client = s3_connect();

        // Check if the connection is successful
        try {
            $s3Client->headBucket(['Bucket' => $this->options['s3_bucket_name']]);
            wp_send_json_success('Connection successful.');
        } catch (Aws\Exception\AwsException $e) {
            wp_send_json_error('Connection failed: ' . $e->getMessage());
        }
    }

    public function validate_options($input) {
        $errors = array();
    
        if (empty($input['s3_bucket_name'])) {
            $errors[] = __('Bucket Name is required.', 'wp-s3-media-uploader');
        }
    
        if (empty($input['s3_region'])) {
            $errors[] = __('Region is required.', 'wp-s3-media-uploader');
        }
    
        if (!empty($errors)) {
            add_settings_error(
                's3_media_uploader_options',
                's3_media_uploader_options_error',
                implode('<br>', $errors),
                'error'
            );
            return get_option('s3_media_uploader_options');
        }
    
        return $input;
    }
}