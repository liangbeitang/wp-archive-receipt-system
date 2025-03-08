<?php
/**
 * 渲染存档回执的 HTML 模板
 *
 * @param array $receipt_data 包含回执信息的数组
 * @return string 渲染后的 HTML 内容
 */
function ars_render_receipt_template($receipt_data) {
    // 引入样式文件
    wp_enqueue_style('archive-receipt-style', plugins_url('assets/css/receipt-style.css', __FILE__));

    // 开始输出缓冲
    ob_start();
    ?>
    <div class="archive-receipt-container">
        <h1 class="receipt-title"><?php _e('数字存档回执', 'archive-receipt'); ?></h1>
        <table class="receipt-table">
            <?php foreach ($receipt_data as $field => $value) :
                if ($field === 'verification_url') {
                    continue; // 跳过验证 URL，避免在表格中显示
                }
                $field_label = __($field, 'archive-receipt');
                ?>
                <tr>
                    <td class="receipt-field-label"><?php echo esc_html($field_label); ?></td>
                    <td class="receipt-field-value"><?php echo esc_html($value); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="receipt-security-info">
            <p class="receipt-verification-url">
                <?php _e('永久链接查询地址：', 'archive-receipt'); ?>
                <a href="<?php echo esc_url($receipt_data['verification_url']); ?>"><?php echo esc_url($receipt_data['verification_url']); ?></a>
            </p>
            <p class="receipt-print-time"><?php _e('打印时间：', 'archive-receipt'); ?><?php echo esc_html(date('Y - m - d H:i:s')); ?></p>
        </div>
        <a href="<?php echo add_query_arg(array('action' => 'ars_generate_image', 'receipt_id' => $receipt_data['receipt_id']), admin_url('admin - ajax.php')); ?>" class="generate - image - button">
            <?php _e('下载图片', 'archive-receipt'); ?>
        </a>
    </div>
    <?php
    // 获取输出缓冲的内容并清空缓冲
    $output = ob_get_clean();
    return $output;
}