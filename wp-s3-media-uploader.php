<?php
/**
 * Plugin Name: WP S3 Media Uploader
 * Description: A plugin to upload media files to Amazon S3 and manage settings.
 * Version: 1.0.0
 * Author: Marcio Fao
 * Author URI: https://marciofao.github.io
 * License: GPL2
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'WP_S3_MEDIA_UPLOADER_VERSION', '1.0.0' );
define( 'WP_S3_MEDIA_UPLOADER_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_S3_MEDIA_UPLOADER_URL', plugin_dir_url( __FILE__ ) );

// Include necessary files
require_once WP_S3_MEDIA_UPLOADER_DIR . 'includes/class-s3-media-uploader.php';
require_once WP_S3_MEDIA_UPLOADER_DIR . 'includes/class-s3-settings.php';
require_once WP_S3_MEDIA_UPLOADER_DIR . 'includes/s3-functions.php';


// Enqueue admin scripts
function wp_s3_media_uploader_enqueue_scripts($hook) {
    if ($hook !== 'settings_page_s3_media_uploader') {
        return;
    }
    wp_enqueue_script('s3-media-uploader-admin', WP_S3_MEDIA_UPLOADER_URL . 'assets/js/admin.js', array('jquery'), WP_S3_MEDIA_UPLOADER_VERSION, true);
    wp_localize_script('s3-media-uploader-admin', 's3MediaUploader', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('s3_media_uploader_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'wp_s3_media_uploader_enqueue_scripts');

// Add settings link to plugin description
function wp_s3_media_uploader_action_links($links) {
    $settings_link = '<a href="options-general.php?page=s3_media_uploader">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wp_s3_media_uploader_action_links');

// Initialize the plugin
function wp_s3_media_uploader_init() {
    // Initialize classes
    $s3_media_uploader = new S3MediaUploader();
    $s3_settings = new S3Settings();
    $s3_settings->init();
}
add_action( 'plugins_loaded', 'wp_s3_media_uploader_init' );

// Filter the URLs of the media files to point to the S3 bucket
function wp_s3_media_uploader_filter_media_urls($url, $post_id) {
    return wp_s3_guid_to_url($url);
    
}
add_filter('wp_get_attachment_url', 'wp_s3_media_uploader_filter_media_urls', 10, 2);

// Filter the URLs of the image sizes to point to the S3 bucket
function wp_s3_media_uploader_filter_image_src($image, $attachment_id, $size, $icon) {
    return wp_s3_get_img_array($image);
}
add_filter('wp_get_attachment_image_src', 'wp_s3_media_uploader_filter_image_src', 20, 4);

// Filter the URLs of the image sizes in the admin panel to point to the S3 bucket
function wp_s3_media_uploader_filter_admin_image_src($image, $attachment_id, $size) {  
    return wp_s3_get_img_array($image);
}
add_filter('wp_prepare_attachment_for_js', 'wp_s3_media_uploader_filter_admin_image_src', 20, 3);

function wp_s3_guid_to_url($guid) {
    global $wpdb;
	
    // Early return if guid has aws url
    if (str_contains($guid, 'amazonaws')) {
        return $guid;
    }
     
    //get the s3 fallback url from offload media plugin table if file name matches
    $media_name = basename($guid);   
    $s3_fallback = $wpdb->get_var("SELECT url FROM ".$wpdb->prefix."acoofm_items WHERE source_path LIKE '%".$media_name."'");

    if ($s3_fallback){
        return $s3_fallback;
    }
    //early return if plugin is not configured
    $options = get_option('s3_media_uploader_options');
    if(!is_array($options) || !isset($options['s3_bucket_name']) || !isset($options['s3_region'])){
        return $guid;
    }
	
    $media_array = $wpdb->get_var("SELECT meta_value FROM ".$wpdb->prefix."postmeta WHERE meta_value LIKE '%".$media_name."%' AND meta_key = '_wp_attachment_metadata'");
    
    if($media_array){
        $media_array = unserialize($media_array);
        $media_name = $media_array['file']; 
    }    
    // if nothing works, build the aws url
    $bucket_name = $options['s3_bucket_name'];
    $region = $options['s3_region'];
    $url = "https://$bucket_name.s3.$region.amazonaws.com/wp-content/uploads/".$media_name;
    return $url;
}

function wp_s3_get_img_array($img_array){
    //error_log("get_img_array: ".var_export($img_array, true));
    if(!is_array($img_array)){
        return $img_array;
    }
    
    if(isset($img_array['url'])){
        $filename = basename($img_array['url']);
        global $wpdb;
        $s3_fallback = $wpdb->get_results("SELECT url, extra_info FROM ".$wpdb->prefix."acoofm_items WHERE source_path LIKE '%".$filename."'");
    }else{
        $s3_fallback = false;
    }

    
    if(!$s3_fallback){ //not offload-media uploaded media, return original plus add meta for listing view in gallery 
        if(!isset($img_array['sizes'])){
            $img = isset($img_array[0])? $img_array[0] : $img_array['url'];
            $img_array[0] = wp_s3_guid_to_url($img);
            // set icon same as url otherwise media preview will not work
            $img_array[1] = 60;
            $img_array[2] = 60;
        }
        if(isset($img_array['url'])){
            $img_array['url'] = wp_s3_guid_to_url($img_array['url']);
            $img_array['icon'] = $img_array['url'];
        }
        //error_log("return_img_array: ".var_export($img_array, true));
        return $img_array;
    }
    
    $img_array['url'] = $s3_fallback[0]->url;
    $img_array['link'] = $s3_fallback[0]->url;
    $img_array['icon'] = $s3_fallback[0]->url;
    $img_array['sizes'] = unserialize($s3_fallback[0]->extra_info);
    return $img_array;
}