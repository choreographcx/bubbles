(function ($) {
    'use strict';

    var frame;
    var $input = $('#bpr_pet_gallery');
    var $preview = $('#bpr_pet_gallery_preview');

    function syncInput() {
        var ids = [];
        $preview.find('.bpr-core-gallery-item').each(function () {
            ids.push($(this).data('id'));
        });
        $input.val(ids.join(','));
    }

    function addImage(attachment) {
        var thumb = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
        if ($preview.find('[data-id="' + attachment.id + '"]').length) {
            return;
        }

        var $item = $('<div class="bpr-core-gallery-item" />').attr('data-id', attachment.id);
        $('<img alt="" />').attr('src', thumb).appendTo($item);
        $('<button type="button" class="bpr-core-remove-image" aria-label="Remove image">&times;</button>').appendTo($item);
        $preview.append($item);
    }

    $('#bpr_pet_gallery_select').on('click', function (event) {
        event.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'Select Pet Gallery Images',
            button: { text: 'Use Selected Images' },
            library: { type: 'image' },
            multiple: true
        });

        frame.on('select', function () {
            frame.state().get('selection').each(function (model) {
                addImage(model.toJSON());
            });
            syncInput();
        });

        frame.open();
    });

    $preview.on('click', '.bpr-core-remove-image', function () {
        $(this).closest('.bpr-core-gallery-item').remove();
        syncInput();
    });

    $('#bpr_pet_gallery_clear').on('click', function (event) {
        event.preventDefault();
        $preview.empty();
        syncInput();
    });

    $preview.sortable({
        items: '.bpr-core-gallery-item',
        update: syncInput
    });
})(jQuery);
