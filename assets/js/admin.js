jQuery(document).ready(function($) {
    $('#check-s3-connection').on('click', function(e) {
        e.preventDefault();
        $('#s3-connection-result').html('Checking connection...');

        $.ajax({
            url: s3MediaUploader.ajax_url,
            type: 'POST',
            data: {
                action: 'check_s3_connection',
                nonce: s3MediaUploader.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#s3-connection-result').html('<span style="color: green;">' + response.data + '</span>');
                } else {
                    $('#s3-connection-result').html('<span style="color: red;">' + response.data + '</span>');
                }
            },
            error: function() {
                $('#s3-connection-result').html('<span style="color: red;">An error occurred.</span>');
            }
        });
    });
});