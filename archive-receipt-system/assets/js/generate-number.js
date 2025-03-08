jQuery(document).ready(function ($) {
    $('#generate-receipt-number').on('click', function () {
        // 获取当前日期和时间
        var now = new Date();
        var year = now.getFullYear();
        var month = String(now.getMonth() + 1).padStart(2, '0');
        var day = String(now.getDate()).padStart(2, '0');
        var hours = String(now.getHours()).padStart(2, '0');
        var minutes = String(now.getMinutes()).padStart(2, '0');
        var seconds = String(now.getSeconds()).padStart(2, '0');

        // 生成 3 个随机数字或字母
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var randomPart = '';
        for (var i = 0; i < 3; i++) {
            randomPart += characters.charAt(Math.floor(Math.random() * characters.length));
        }

        // 组合编号
        var receiptNumber = year + month + day + hours + minutes + seconds + randomPart;

        // 将生成的编号填入回执编号输入框
        $('#receipt_number').val(receiptNumber);
    });
});