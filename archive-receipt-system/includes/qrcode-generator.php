<?php
// 生成二维码图片并返回其 URL
function ars_generate_qrcode($url) {
    // 引入 PHP QR Code 库
    require_once plugin_dir_path(__FILE__) . '../vendor/phpqrcode/qrlib.php';

    // 定义临时二维码图片存储目录
    $tempDir = plugin_dir_path(__FILE__) . '../assets/qrcodes/';

    // 检查存储目录是否存在，如果不存在则创建
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    // 根据 URL 生成唯一的文件名
    $fileName = 'qr_' . md5($url) . '.png';

    // 定义二维码图片的绝对文件路径
    $pngAbsoluteFilePath = $tempDir . $fileName;

    // 定义二维码图片的相对 URL 路径
    $urlRelativeFilePath = plugins_url('assets/qrcodes/' . $fileName, __FILE__);

    // 检查二维码图片是否已经存在，如果不存在则生成
    if (!file_exists($pngAbsoluteFilePath)) {
        // 使用 QRcode::png 方法生成二维码图片
        QRcode::png($url, $pngAbsoluteFilePath);
    }

    // 返回二维码图片的相对 URL 路径
    return $urlRelativeFilePath;
}