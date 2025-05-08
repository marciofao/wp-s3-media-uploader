# WP S3 Media Uploader

WP S3 Media Uploader is a WordPress plugin that allows you to seamlessly upload media files to an Amazon S3 bucket whenever they are added to the media gallery. This plugin ensures that your media files are stored securely and efficiently in the cloud, providing a scalable solution for managing your media assets.

## Features

- Upload media files directly to Amazon S3 upon upload to the media gallery.
- Supports configuration of S3 bucket name, region, and IAM roles or access keys.
- Creates a tracking table in the PostgreSQL database upon activation.
- Checks S3 connection status to ensure successful uploads.
- Returns the S3 URL for media files when requested.
- Allow retro compatibility with plevious uploads using the [offload media plugin](https://wordpress.org/plugins/offload-media-cloud-storage/).

## Installation

1. Upload the `wp-s3-media-uploader` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the plugin settings page on settings>S3 Media Uploader to configure your S3 bucket details.

## Configuration

- After activation, go to **Settings > WP S3 Media Uploader**.
- Enter your S3 bucket name and region.
- Configure IAM roles or provide your AWS access key and secret key.
- Save your settings to establish a connection with your S3 bucket.

## Usage

Once configured, any media file uploaded to the WordPress media library will automatically be uploaded to your specified S3 bucket. The plugin will handle the upload process and store the S3 URL for each media file.

## Troubleshooting

- Ensure that your AWS credentials are correct and have the necessary permissions to upload files to the specified S3 bucket.
- Check your server's PHP configuration to ensure that the Amazon S3 PHP library is properly included and configured.

## Support

For support, please open an issue on the plugin's GitHub repository or contact the plugin author directly.

## Knwon Issues
- Wordpress native xml file content importing fails as the system cannot locate the local file. Disable the plugin if you need to use the xml import feature. Then re-enable it after the import is complete.

## Changelog

### 1.0.0
- Initial release of WP S3 Media Uploader.

## License

This plugin is licensed under the GPL v2 or later. See the LICENSE file for more details.