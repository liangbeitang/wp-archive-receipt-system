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

                    // 生成 Excel 内容
                    $data = [
                        '回执编号' => $receipt_number,
                        '公司名称' => $company_name,
                        '申请人' => $applicant,
                        '回执内容' => $content,
                        '回执接收人' => $receipt_recipient,
                        '申请部门' => $application_department,
                        '存档详情' => $archive_details,
                        '数据库位置' => $database_location,
                        '数据库名称' => $database_name,
                        '文件路径结构' => $file_path_structure,
                        '存档容量（单位：GB）' => $archive_capacity,
                        '存档完成时间' => $archive_completion_time
                    ];

                    $excel_output = '<table border="1">';
                    foreach ($data as $key => $value) {
                        $excel_output .= '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
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
            <h2><?php _e('存档回执录入', 'archive-receipt'); ?></h2>
            <form method="post" enctype="multipart/form-data">
                <table>
                    <tr>
                        <td><label for="receipt_number"><?php _e('回执编号', 'archive-receipt'); ?></label></td>
                        <td>
                            <input type="text" id="receipt_number" name="receipt_number" required>
                            <button type="button" id="generate-receipt-number"><?php _e('生成编号', 'archive-receipt'); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="company_name"><?php _e('公司名称', 'archive-receipt'); ?></label></td>
                        <td><input type="text" id="company_name" name="company_name" value="<?php echo esc_attr($company_name); ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="application_department"><?php _e('申请部门', 'archive-receipt'); ?></label></td>
                        <td><input type="text" id="application_department" name="application_department" required></td>
                    </tr>
                    <tr>
                        <td><label for="applicant"><?php _e('申请人', 'archive-receipt'); ?></label></td>
                        <td><input type="text" id="applicant" name="applicant" required></td>
                    </tr>
                    <tr>
                        <td><label for="receipt_recipient"><?php _e('回执接收人', 'archive-receipt'); ?></label></td>
                        <td><input type="text" id="receipt_recipient" name="receipt_recipient" required></td>
                    </tr>
                    <tr>
                        <td><label for="content"><?php _e('回执内容', 'archive-receipt'); ?></label></td>
                        <td><textarea id="content" name="content" required></textarea></td>
                    </tr>
                    <tr>
                        <td><label for="archive_details"><?php _e('存档详情', 'archive-receipt'); ?></label></td>
                        <td><textarea id="archive_details" name="archive_details" required></textarea></td>
                    </tr>
                    <tr>
                        <td><label for="database_location"><?php _e('数据库位置', 'archive-receipt'); ?></label></td>
                        <td><input type="text" id="database_location" name="database_location" required></td>
                    </tr>
                    <tr>
                        <td><label for="database_name"><?php _e('数据库名称', 'archive-receipt'); ?></label></td>
                        <td><input type="text" id="database_name" name="database_name" required></td>
                    </tr>
                    <tr>
                        <td><label for="file_path_structure"><?php _e('文件路径结构', 'archive-receipt'); ?></label></td>
                        <td><textarea id="file_path_structure" name="file_path_structure" required></textarea></td>
                    </tr>
                    <tr>
                        <td><label for="archive_capacity"><?php _e('存档容量（单位：GB）', 'archive-receipt'); ?></label></td>
                        <td><input type="number" step="0.01" id="archive_capacity" name="archive_capacity" required></td>
                    </tr>
                    <tr>
                        <td><label for="archive_completion_time"><?php _e('存档完成时间', 'archive-receipt'); ?></label></td>
                        <td><input type="datetime-local" id="archive_completion_time" name="archive_completion_time" required></td>
                    </tr>
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
        echo '<h2>' . __('<p>该回执已存入数据库，请将此页面打印存档。</p></br><p>回执详情：</p>', 'archive-receipt') . '</h2>';
        echo $excel_output;
    }

    // 获取并清空输出缓冲内容
    $output = ob_get_clean();

    // 引入样式文件
    wp_enqueue_style('archive-receipt-style', plugins_url('assets/css/receipt-style.css', __FILE__));

    return $output;
}