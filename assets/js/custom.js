jQuery(document).ready(function ($) {
    $('.upload-btn').click(function (e) {
        e.preventDefault();
        var t = jQuery(this);
        var image = wp.media({
            title: 'Upload Image',
            multiple: false
        }).open()
                .on('select', function (e) {
                    var uploaded_image = image.state().get('selection').first();
                    var image_url = uploaded_image.toJSON().url;
                    t.prev().val(image_url);
                });
    });
});