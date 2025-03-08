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
            <label for="receipt_number"><?php _e('回执编号', 'archive-receipt'); ?></label>
            <input type="text" id="receipt_number" name="receipt_number" required>
            <input type="submit" value="<?php _e('查询', 'archive-receipt'); ?>">
        </form><br>
        <p>
        <div id="query-result" class="query-result-container"></div>
    </div>
    </br>
    <div>
    <script>
        jQuery(document).ready(function($) {
            $('#archive-query-form').on('submit', function(e) {
                e.preventDefault();
                var receipt_number = $('#receipt_number').val();
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
                        $('#query-result').html('<p><?php _e('查询出错，请稍后重试。', 'archive-receipt'); ?></p>');
                    }
                });
            });
        });
    </script>
    </div></p>
    <?php
    // 获取输出缓冲中的内容
    $output = ob_get_clean();
    return $output;
}

// 修改查询结果展示
function ars_ajax_query_receipt() {
    $receipt_number = sanitize_text_field($_POST['receipt_number']);
    $result = ars_query_receipt($receipt_number);
    if ($result) {
        $output = '<table class="receipt-table">';
        $output .= '<tr><td class="receipt-field-label">'.__('回执编号', 'archive-receipt').'</td><td class="receipt-field-value">'.$result['receipt_number'].'</td></tr>';
        $output .= '<tr><td class="receipt-field-label">'.__('公司名称', 'archive-receipt').'</td><td class="receipt-field-value">'.$result['company_name'].'</td></tr>';
        $output .= '<tr><td class="receipt-field-label">'.__('申请人', 'archive-receipt').'</td><td class="receipt-field-value">'.$result['applicant'].'</td></tr>';
        $output .= '<tr><td class="receipt-field-label">'.__('回执内容', 'archive-receipt').'</td><td class="receipt-field-value">'.$result['content'].'</td></tr>';
        $output .= '<tr><td class="receipt-field-label">'.__('回执接收人', 'archive-receipt').'</td><td class="receipt-field-value">'.$result['receipt_recipient'].'</td></tr>';
        $output .= '<tr><td class="receipt-field-label">'.__('申请部门', 'archive-receipt').'</td><td class="receipt-field-value">'.$result['application_department'].'</td></tr>';
        $output .= '<tr><td class="receipt-field-label">'.__('存档详情', 'archive-receipt').'</td><td class="receipt-field-value">'.$result['archive_details'].'</td></tr>';
        $output .= '<tr><td class="receipt-field-label">'.__('数据库位置', 'archive-receipt').'</td><td class="receipt-field-value">'.$result['database_location'].'</td></tr>';
        $output .= '<tr><td class="receipt-field-label">'.__('数据库名称', 'archive-receipt').'</td><td class="receipt-field-value">'.$result['database_name'].'</td></tr>';
        $output .= '<tr><td class="receipt-field-label">'.__('文件路径结构', 'archive-receipt').'</td><td class="receipt-field-value">'.$result['file_path_structure'].'</td></tr>';
        $output .= '<tr><td class="receipt-field-label">'.__('存档容量（单位：GB）', 'archive-receipt').'</td><td class="receipt-field-value">'.$result['archive_capacity'].'</td></tr>';
        $output .= '<tr><td class="receipt-field-label">'.__('存档完成时间', 'archive-receipt').'</td><td class="receipt-field-value">'.$result['archive_completion_time'].'</td></tr>';
        $output .= '</table>';
        echo $output;
    } else {
        echo '<p>'.__('未找到相关回执信息。', 'archive-receipt').'</p>';
    }
    wp_die();
}