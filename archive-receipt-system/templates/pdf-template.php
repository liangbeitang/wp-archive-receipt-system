<?php
/**
 * 生成存档回执的 PDF 文件
 *
 * @param array $receipt_data 包含回执信息的数组
 */
function ars_generate_pdf($receipt_data) {
    // 引入 TCPDF 库
    require_once plugin_dir_path(__FILE__) . '../vendor/tcpdf/tcpdf.php';

    // 创建一个新的 TCPDF 实例
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');

    // 设置 PDF 的创建者、作者和标题
    $pdf->SetCreator('Archive Receipt System');
    $pdf->SetAuthor(get_bloginfo('name'));
    $pdf->SetTitle(__('存档回执', 'archive-receipt'));

    // 设置文档的页眉和页脚信息
    $pdf->setHeaderData('', 0, __('存档回执', 'archive-receipt'), '');
    $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

    // 设置页眉和页脚字体
    $pdf->setHeaderFont(array('stsongstdlight', '', 10));
    $pdf->setFooterFont(array('stsongstdlight', '', 8));

    // 设置默认的单倍行距
    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);
    $pdf->SetDefaultMonospacedFont('courier');
    $pdf->SetMargins(15, 20, 15);
    $pdf->SetAutoPageBreak(true, 25);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // 设置中文字体
    $pdf->SetFont('stsongstdlight', '', 12);

    // 添加新页面
    $pdf->AddPage();

    // 构建 PDF 内容的 HTML
    $html = '<h1 style="text-align: center;">' . __('数字存档回执', 'archive-receipt') . '</h1>';
    $html .= '<table border="1" cellpadding="5">';
    foreach ($receipt_data as $field => $value) {
        if ($field === 'verification_url') {
            continue; // 跳过验证 URL，避免在表格中显示
        }
        $field_label = __($field, 'archive-receipt');
        $html .= '<tr><td width="30%">' . $field_label . '</td><td width="70%">' . $value . '</td></tr>';
    }
    $html .= '</table>';

    // 添加防伪元素，如二维码和打印时间
    $qr_code = ars_generate_qrcode($receipt_data['verification_url']);
    $html .= '<div style="text-align: right; margin-top: 20px;">';
    $html .= '<img src="' . $qr_code . '" width="100">';
    $html .= '<p>' . __('打印时间：', 'archive-receipt') . date('Y-m-d H:i:s') . '</p>';
    $html .= '</div>';

    // 将 HTML 内容写入 PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // 输出 PDF 文件，D 表示直接下载
    $pdf->Output('archive_receipt_' . $receipt_data['receipt_id'] . '.pdf', 'D');
}