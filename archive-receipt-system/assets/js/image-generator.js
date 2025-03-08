jQuery(document).ready(function ($) {
    $('.generate-image').on('click', function (e) {
        e.preventDefault();
        var receiptId = $(this).data('receipt-id');
        var imageUrl = '<?php echo admin_url('admin-ajax.php'); ?>?action=ars_generate_image&receipt_id=' + receiptId;
        window.open(imageUrl, '_blank');
    });
});