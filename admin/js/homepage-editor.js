(function ($) {
    'use strict';

    $(document).on('click', '.aiad-upload-media', function (e) {
        e.preventDefault();
        var btn = $(this);
        var target = btn.data('target');
        var store = btn.data('store') || 'image';
        var frame = wp.media({
            library: { type: 'image' },
            multiple: false
        });

        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            var val = store === 'url' ? (att.url || '') : (att.id || '');
            $('#' + target).val(val);

            var preview = btn.siblings('.aiad-media-preview');
            var imgUrl = (att.sizes && att.sizes.medium && att.sizes.medium.url) ? att.sizes.medium.url : (att.url || '');
            if (!imgUrl) {
                return;
            }
            preview.html('<img src="' + imgUrl + '" alt="" aria-hidden="true" style="max-width:120px;height:auto;vertical-align:middle;" />').show();
        });

        frame.open();
    });
})(jQuery);
