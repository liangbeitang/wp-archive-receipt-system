<?php
/**
 * 生成存档回执的图片文件
 *
 * @param array $receipt_data 包含回执信息的数组
 */
function ars_generate_image($receipt_data) {
    // 构建 HTML 内容
    $html = '<!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: "宋体", SimSun, sans-serif;
                padding: 20px;
                width: 210mm; /* A4 宽度 */
                height: 297mm; /* A4 高度 */
            }
            h1 {
                text-align: center;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            table td {
                border: 1px solid #ccc;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <h1>数字存档回执</h1>
        <table>';
    foreach ($receipt_data as $field => $value) {
        if ($field === 'verification_url') {
            continue; // 跳过验证 URL，避免在表格中显示
        }
        $field_label = __($field, 'archive-receipt');
        $html .= '<tr><td>' . $field_label . '</td><td>' . $value . '</td></tr>';
    }
    $html .= '</table>';
    $verification_url = $receipt_data['verification_url'];
    $html .= '<div style="text-align: right; margin-top: 20px;">';
    $html .= '<p>永久链接查询地址：<a href="' . $verification_url . '">' . $verification_url . '</a></p>';
    $html .= '<p>打印时间：' . date('Y-m-d H:i:s') . '</p>';
    $html .= '</div>
    </body>
    </html>';

    // 将 HTML 内容保存到临时文件
    $temp_html_file = tempnam(sys_get_temp_dir(), 'receipt_html_') . '.html';
    file_put_contents($temp_html_file, $html);

    // 使用 Puppeteer 生成 PNG 图片
    $temp_png_file = tempnam(sys_get_temp_dir(), 'receipt_png_') . '.png';
    $node_script = plugin_dir_path(__FILE__) . '../assets/js/generate-image.js';
    exec("node $node_script $temp_html_file $temp_png_file", $output, $return_var);

    // 删除临时 HTML 文件
    unlink($temp_html_file);

    if ($return_var === 0) {
        // 输出图片
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="archive_receipt_' . $receipt_data['receipt_id'] . '.png"');
        readfile($temp_png_file);
        // 删除临时 PNG 文件
        unlink($temp_png_file);
    } else {
        echo '生成图片失败';
    }
    exit;
}