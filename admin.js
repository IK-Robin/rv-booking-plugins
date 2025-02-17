jQuery(document).ready(function($) {
    // Handle image upload
    $('#upload_image_button').click(function(e) {
        e.preventDefault();
        var custom_uploader = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        }).on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#rv_lots_images').val(attachment.url);
        }).open();
    });
});