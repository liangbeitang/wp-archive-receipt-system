jQuery(document).ready(function ($) {
    // 为所有具有 "generate-pdf" 类的按钮绑定点击事件
    $('.generate-pdf').on('click', function (e) {
        // 阻止按钮的默认点击行为
        e.preventDefault();

        // 获取按钮上 data-receipt-id 属性的值，该值为回执的 ID
        var receiptId = $(this).data('receipt-id');

        // 构建生成 PDF 的请求 URL
        var pdfUrl = '<?php echo admin_url('admin-ajax.php'); ?>?action=ars_generate_pdf&receipt_id=' + receiptId;

        // 打开新窗口下载 PDF 文件
        window.open(pdfUrl, '_blank');
    });
});