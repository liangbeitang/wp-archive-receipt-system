<?php

// 渲染存档回执录入表单
function ars_render_submit_form() {
    // 检查当前用户是否有录入权限
    if (!ars_check_submit_permission()) {
        return '<p>' . __('您没有权限进行回执录入操作。', 'archive-receipt') . '</p>';
    }

    $message = '';
    $excel_output = '';

    // 处理表单提交
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_receipt'])) {
        $receipt_number = sanitize_text_field($_POST['receipt_number']);
        $company_name = sanitize_text_field($_POST['company_name']);
        $applicant = sanitize_text_field($_POST['applicant']);
        $content = sanitize_textarea_field($_POST['content']);
        $receipt_recipient = sanitize_text_field($_POST['receipt_recipient']);
        $application_department = sanitize_text_field($_POST['application_department']);
        $archive_details = sanitize_textarea_field($_POST['archive_details']);
        $database_location = sanitize_text_field($_POST['database_location']);
        $database_name = sanitize_text_field($_POST['database_name']);
        $file_path_structure = sanitize_textarea_field($_POST['file_path_structure']);
        $archive_capacity = floatval($_POST['archive_capacity']);
        $archive_completion_time = sanitize_text_field($_POST['archive_completion_time']);

        // 验证回执编号格式
        if (ars_validate_receipt_number($receipt_number)) {
            global $wpdb;
            $receipts_table = $wpdb->prefix . 'archive_receipts';
            // 检查回执编号是否已存在
            $existing_receipt = $wpdb->get_row($wpdb->prepare("SELECT * FROM $receipts_table WHERE receipt_number = %s", $receipt_number));
            if ($existing_receipt) {
                $message = '<div class="error"><p style="color: red;">' . __('回执编号已存在，请勿重复录入。', 'archive-receipt') . '</p></div>';
            } else {
                $submit_time = current_time('mysql');
                $status = 'pending';

                // 插入回执数据到数据库
                if (ars_insert_receipt(
                    $receipt_number,
                    $company_name,
                    $applicant,
                    $submit_time,
                    $status,
                    $content,
                    $receipt_recipient,
                    $application_department,
                    $archive_details,
                    $database_location,
                    $database_name,
                    $file_path_structure,
                    $archive_capacity,
                    $archive_completion_time
                )) {
                    // 获取刚插入记录的 ID
                    $receipt_id = $wpdb->insert_id;

                    // 构建 HTML 内容，这里假设 ars_render_receipt_template 函数能生成正确的 HTML
                    $html = ars_render_receipt_template([
                        'receipt_number' => $receipt_number,
                        'company_name' => $company_name,
                        'applicant' => $applicant,
                        'content' => $content,
                        'receipt_recipient' => $receipt_recipient,
                        'application_department' => $application_department,
                        'archive_details' => $archive_details,
                        'database_location' => $database_location,
                        'database_name' => $database_name,
                        'file_path_structure' => $file_path_structure,
                        'archive_capacity' => $archive_capacity,
                        'archive_completion_time' => $archive_completion_time
                    ]);

                    // 将 HTML 内容保存到临时文件
                    $temp_file = tempnam(sys_get_temp_dir(), 'archive_receipt_') . '.html';
                    file_put_contents($temp_file, $html);

                    // 生成下载链接
                    $download_link = add_query_arg([
                        'action' => 'ars_download_receipt_html',
                        'file' => urlencode($temp_file)
                    ], admin_url('admin-ajax.php'));

                    $message = '<div class="updated"><p>' . __('回执录入成功。', 'archive-receipt') . '</p><p><a href="' . $download_link . '">点击下载存档回执 HTML 文件</a></p></div>';

                    // 生成 Excel 内容，调整显示顺序
                    $data = [
                        '回执编号' => $receipt_number,
                        '存档单位' => $company_name,
                        '申请部门' => $application_department,
                        '申请人' => $applicant,
                        '回执接收人' => $receipt_recipient,
                        '回执内容' => $content,
                        '存档详情' => $archive_details,
                        '数据库位置' => $database_location,
                        '数据库名称' => $database_name,
                        '文件路径结构' => $file_path_structure,
                        '存档容量（GB）' => $archive_capacity,
                        '存档完成时间' => $archive_completion_time
                    ];

                    $excel_output = '<table class="receipt-table">';
                    foreach ($data as $key => $value) {
                        $excel_output .= '<tr><td class="receipt-field-label">' . $key . '</td><td class="receipt-field-value">' . $value . '</td></tr>';
                    }
                    $excel_output .= '</table>';
                } else {
                    $message = '<div class="error"><p style="color: red;">' . __('回执录入失败，请稍后重试。', 'archive-receipt') . '</p></div>';
                }
            }
        } else {
            $message = '<div class="error"><p style="color: red;">' . __('回执编号格式不正确，请重新输入。', 'archive-receipt') . '</p></div>';
        }
    }

    // 获取设置的公司名称
    $company_name = get_option('ars_company_name', '');

    // 开始输出缓冲
    ob_start();
    echo $message;

    if (empty($message)) {
        ?>
        <div class="archive-submit-form-container">
            <h3 class="receipt-title"><?php _e('存档回执录入', 'archive-receipt'); ?></h3>
            <form method="post" enctype="multipart/form-data">
                <table class="receipt-table">
                    <?php
                    $fields = [
                        'receipt_number' => __('回执编号', 'archive-receipt'),
                        'company_name' => __('存档单位', 'archive-receipt'),
                        'application_department' => __('申请部门', 'archive-receipt'),
                        'applicant' => __('申请人', 'archive-receipt'),
                        'receipt_recipient' => __('回执接收人', 'archive-receipt'),
                        'content' => __('回执内容', 'archive-receipt'),
                        'archive_details' => __('存档详情', 'archive-receipt'),
                        'database_location' => __('数据库位置', 'archive-receipt'),
                        'database_name' => __('数据库名称', 'archive-receipt'),
                        'file_path_structure' => __('文件路径结构', 'archive-receipt'),
                        'archive_capacity' => __('存档容量（单位：GB）', 'archive-receipt'),
                        'archive_completion_time' => __('存档完成时间', 'archive-receipt')
                    ];

                    foreach ($fields as $field => $label) {
                        ?>
                        <tr>
                            <td class="receipt-field-label"><label for="<?php echo $field; ?>"><?php echo $label; ?></label></td>
                            <td class="receipt-field-value">
                                <?php if ($field === 'content' || $field === 'archive_details' || $field === 'file_path_structure') { ?>
                                    <textarea id="<?php echo $field; ?>" name="<?php echo $field; ?>" required></textarea>
                                <?php } elseif ($field === 'archive_capacity') { ?>
                                    <input type="number" step="1" id="<?php echo $field; ?>" name="<?php echo $field; ?>" required>
                                <?php } elseif ($field === 'archive_completion_time') { ?>
                                    <input type="datetime-local" id="<?php echo $field; ?>" name="<?php echo $field; ?>" required>
                                <?php } elseif ($field === 'receipt_number') { ?>
                                    <p><input type="text" id="<?php echo $field; ?>" name="<?php echo $field; ?>" required></p>
                                    <button type="button" id="generate-receipt-number"><?php _e('生成编号', 'archive-receipt'); ?></button>
                                <?php } elseif ($field === 'company_name') { ?>
                                    <input type="text" id="<?php echo $field; ?>" name="<?php echo $field; ?>" value="<?php echo esc_attr($company_name); ?>" required>
                                <?php } else { ?>
                                    <input type="text" id="<?php echo $field; ?>" name="<?php echo $field; ?>" required>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td></td>
                        <td><input type="submit" name="submit_receipt" value="<?php _e('提交回执', 'archive-receipt'); ?>"></td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    if (!empty($excel_output)) {
        // 获取刚插入记录的 ID
        if (isset($receipt_id)) {
            $image_download_link = add_query_arg(array('action' => 'ars_generate_image', 'receipt_id' => $receipt_id), admin_url('admin-ajax.php'));
        } else {
            $image_download_link = '';
        }

        echo '<h4 class="receipt-title receipt-left-align">' . __('<p>该回执已存入数据库，请将此页面打印存档。</br>回执详情：</p>', 'archive-receipt') . '</h4>';
        echo '<div class="archive-receipt-container">';
        echo '<h1 class="receipt-title">' . __('数字存档回执', 'archive-receipt') . '</h1>';
        echo '<table class="receipt-table">';
        echo $excel_output;
        echo '</table>';
        echo '
            <div class="receipt-security-info">
                <p class="receipt-print-time receipt-align-right">
                    ' . __('验真链接：', 'archive-receipt') . '
                    <a href="https://www.jieqiwenhua.com/digital-archive-details-query">https://www.jieqiwenhua.com/digital-archives</a>
                </p>
                <p class="receipt-print-time receipt-align-right">
                    ' . __('打印时间：', 'archive-receipt') . '
                    ' . esc_html(wp_date('Y - m - d H:i:s')) . '
                </p>
            </div>
            <a href="' . $image_download_link . '" class="generate-image-button">
                ' . __('下载图片', 'archive-receipt') . '
            </a>';
        echo '</div>';
    }

    // 获取并清空输出缓冲内容
    $output = ob_get_clean();

    // 引入样式文件
    wp_enqueue_style('archive-receipt-style', plugins_url('assets/css/receipt-style.css', __FILE__));

    return $output;
}