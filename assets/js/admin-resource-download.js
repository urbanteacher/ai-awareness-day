/**
 * Admin resource download file uploader
 * Handles PDF/PPTX file selection using WordPress media library.
 *
 * @package AI_Awareness_Day
 */

(function($) {
    'use strict';

    if (typeof wp === 'undefined' || !wp.media) {
        return;
    }

    var frame;

    // Upload/Select button handler
    $('#aiad_upload_download_btn').on('click', function(e) {
        e.preventDefault();
        if (frame) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: aiadAdminDownload.selectFileText,
            library: {
                type: [
                    'application/pdf',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'application/vnd.ms-powerpoint'
                ]
            },
            button: {
                text: aiadAdminDownload.useFileText
            },
            multiple: false
        });
        frame.on('select', function() {
            var att = frame.state().get('selection').first().toJSON();
            if (att && att.url) {
                $('#resource_download_url').val(att.url);
                var name = att.filename || att.url.split('/').pop().split('?')[0];
                $('#aiad_download_filename strong').text(name);
                $('#aiad_download_filename').show();
                $('#aiad_remove_download_btn').show();
            }
        });
        frame.open();
    });

    // Remove button handler
    $('#aiad_remove_download_btn').on('click', function(e) {
        e.preventDefault();
        $('#resource_download_url').val('');
        $('#aiad_download_filename').hide();
        $(this).hide();
    });

})(jQuery);
