<?php

// 渲染查询表单
function ars_render_query_form() {
    // 开启输出缓冲，用于捕获后续生成的 HTML 内容
    ob_start();
    // 引入样式文件
    wp_enqueue_style('archive-receipt-style', plugins_url('assets/css/receipt-style.css', __FILE__));
    // 引入 jQuery 验证插件
    wp_enqueue_script('jquery-validate', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js', array('jquery'), '1.19.5', true);
    // 引入自定义表单验证脚本
    wp_enqueue_script('archive-receipt-form-validator', plugins_url('assets/js/form-validator.js', __FILE__), array('jquery', 'jquery-validate'), '1.0', true);
    ?>
    <div class="archive-query-form-container">
        <h2><?php _e('存档回执查询', 'archive-receipt'); ?></h2>
        <form id="archive-query-form" method="post">
            <p>
                <label for="receipt_number"><?php _e('回执编号', 'archive-receipt'); ?></label>
            </p>
            <p>
                <input type="text" id="receipt_number" name="receipt_number" required>
            </p>
            <p>
                <input type="submit" value="<?php _e('查询', 'archive-receipt'); ?>">
            </p>
        </form>
        <p id="query-error" style="color: red; display: none;"><?php _e('回执编号不正确，请查证。', 'archive-receipt'); ?></p>
        <p id="query-result" class="query-result-container"></p>
    </div>
    <script>
        jQuery(document).ready(function($) {
            $('#archive-query-form').on('submit', function(e) {
                e.preventDefault();
                var receipt_number = $('#receipt_number').val();
                if (!validateReceiptNumber(receipt_number)) {
                    $('#query-error').show();
                    return;
                }
                $('#query-error').hide();
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'ars_query_receipt',
                        receipt_number: receipt_number
                    },
                    success: function(response) {
                        $('#query-result').html(response);
                    },
                    error: function() {
                        $('#query-result').html('<p><?php _e('查询失败，请稍后重试。', 'archive-receipt'); ?></p>');
                    }
                });
            });

            function validateReceiptNumber(receiptNumber) {
                var regex = /^\d{14}[A-Za-z0-9]{3}$/;
                return regex.test(receiptNumber);
            }
        });
    </script>
    <?php
    return ob_get_clean();
}