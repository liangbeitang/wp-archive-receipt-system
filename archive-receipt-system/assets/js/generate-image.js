const puppeteer = require('puppeteer');

// 获取命令行参数
const htmlFilePath = process.argv[2];
const pngFilePath = process.argv[3];

(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();

    // 设置页面大小为 A4
    await page.setViewport({
        width: 210 * 3.7795275591, // 210mm 转换为像素
        height: 297 * 3.7795275591, // 297mm 转换为像素
        deviceScaleFactor: 1
    });

    // 打开 HTML 文件
    await page.goto('file://' + htmlFilePath, { waitUntil: 'networkidle2' });

    // 生成 PNG 图片
    await page.screenshot({ path: pngFilePath, fullPage: true });

    await browser.close();
})();