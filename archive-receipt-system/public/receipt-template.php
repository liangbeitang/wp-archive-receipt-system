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

    // 定义英文字段名和中文名称的映射数组
    $field_mapping = array(
        'receipt_id' => __('回执ID', 'archive-receipt'),
        'receipt_number' => __('回执编号', 'archive-receipt'),
        'company_name' => __('公司名称', 'archive-receipt'),
        'applicant' => __('申请人', 'archive-receipt'),
        'submit_time' => __('提交时间', 'archive-receipt'),
        'status' => __('状态', 'archive-receipt'),
        'content' => __('回执内容', 'archive-receipt'),
        'receipt_recipient' => __('回执接收人', 'archive-receipt'),
        'application_department' => __('申请部门', 'archive-receipt'),
        'archive_details' => __('存档详情', 'archive-receipt'),
        'database_location' => __('数据库位置', 'archive-receipt'),
        'database_name' => __('数据库名称', 'archive-receipt'),
        'file_path_structure' => __('文件路径结构', 'archive-receipt'),
        'archive_capacity' => __('存档容量（GB）', 'archive-receipt'),
        'archive_completion_time' => __('存档完成时间', 'archive-receipt')
    );

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
                // 根据映射数组获取中文名称
                $field_label = isset($field_mapping[$field]) ? $field_mapping[$field] : __($field, 'archive-receipt');
                ?>
                <tr>
                    <td class="receipt-field-label"><?php echo esc_html($field_label); ?></td>
                    <td class="receipt-field-value"><?php echo esc_html($value); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="receipt-security-info">
            <p class="receipt-print-time receipt-align-right">
                <?php _e('信息验证查询地址：', 'archive-receipt'); ?>
                <a href="https://www.jieqiwenhua.com/digital-archive-details-query">https://www.jieqiwenhua.com/digital-archive-details-query</a>
            </p>
            <p class="receipt-print-time receipt-align-right">
                <?php _e('打印时间：', 'archive-receipt'); ?>
                <?php echo esc_html(wp_date('Y - m - d H:i:s')); ?>
            </p>
        </div>
        <a href="<?php echo add_query_arg(array('action' => 'ars_generate_image', 'receipt_id' => $receipt_data['receipt_id']), admin_url('admin-ajax.php')); ?>" class="generate-image-button">
            <?php _e('下载图片', 'archive-receipt'); ?>
        </a>
    </div>
    <?php
    // 获取输出缓冲的内容并清空缓冲
    $output = ob_get_clean();
    return $output;
}