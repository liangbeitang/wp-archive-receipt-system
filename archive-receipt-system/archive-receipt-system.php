<?php
/**
 * Plugin Name: 存档回执管理系统
 * Plugin URI: https://www.liangbeitang.com/open-source-coding/wp-plugin/archive-receipt-system/
 * Description: 企业级存档回执管理解决方案
 * Version: 1.0
 * Author: 梁北棠 <contact@liangbeitang.com>
 * Author URI: https://www.liangbeitang.com
 * License: GPL-2.0+
 * Text Domain: archive-receipt
 */

// 插件激活时创建必要页面和数据库表
register_activation_hook(__FILE__, 'ars_create_required_pages');
register_activation_hook(__FILE__, 'ars_create_database_tables');

// 插件加载时初始化操作
function ars_initialize() {
    // 加载多语言支持
    load_plugin_textdomain('archive-receipt', false, dirname(plugin_basename(__FILE__)) . '/languages/');

    // 包含必要的文件
    require_once plugin_dir_path(__FILE__) . 'includes/database.php';
    require_once plugin_dir_path(__FILE__) . 'admin/settings.php';
    require_once plugin_dir_path(__FILE__) . 'includes/qrcode-generator.php';
    require_once plugin_dir_path(__FILE__) . 'public/query-form.php';
    require_once plugin_dir_path(__FILE__) . 'public/submit-form.php';
    // 引入定义 ars_render_receipt_template 函数的文件
    require_once plugin_dir_path(__FILE__) . 'public/receipt-template.php';

    // 注册短代码，用于在页面中显示查询和录入表单
    add_shortcode('archive_query', 'ars_render_query_form');
    add_shortcode('archive_submit', 'ars_render_submit_form');

    // 添加管理菜单到 WordPress 管理后台
    add_action('admin_menu', 'ars_add_admin_menu');

    // 注册 AJAX 动作处理查询和生成图片请求
    add_action('wp_ajax_ars_query_receipt', 'ars_ajax_query_receipt');
    add_action('wp_ajax_nopriv_ars_query_receipt', 'ars_ajax_query_receipt');
    add_action('wp_ajax_ars_generate_image', 'ars_ajax_generate_image');
    add_action('wp_ajax_nopriv_ars_generate_image', 'ars_ajax_generate_image');

    // 挂载脚本和样式加载函数到 wp_enqueue_scripts 钩子
    add_action('wp_enqueue_scripts', 'ars_enqueue_scripts_and_styles');
}

add_action('plugins_loaded', 'ars_initialize');

// 创建必要的页面
function ars_create_required_pages() {
    $pages = [
        [
            'title' => __('存档回执查询系统', 'archive-receipt'),
            'content' => '[archive_query]',
            'option' => 'ars_query_page_id'
        ],
        [
            'title' => __('存档回执录入', 'archive-receipt'),
            'content' => '[archive_submit]',
            'option' => 'ars_submit_page_id'
        ]
    ];

    foreach ($pages as $page) {
        // 检查页面是否已存在
        $args = array(
            'post_type' => 'page',
            'post_title' => $page['title'],
            'posts_per_page' => 1
        );
        $query = new WP_Query($args);
        $existing_page = $query->have_posts() ? $query->posts[0] : null;

        if (!$existing_page) {
            // 创建新页面
            $new_page = [
                'post_title' => $page['title'],
                'post_content' => $page['content'],
                'post_status' => 'publish',
                'post_type' => 'page'
            ];
            $page_id = wp_insert_post($new_page);
            // 更新选项保存页面 ID
            update_option($page['option'], $page_id);
        }
    }
}

// 添加管理菜单到 WordPress 管理后台
function ars_add_admin_menu() {
    add_menu_page(
        __('存档回执系统设置', 'archive-receipt'),
        __('存档回执系统', 'archive-receipt'),
        'manage_options',
        'archive-receipt-settings',
        'ars_render_settings_page',
        'dashicons-media-spreadsheet',
        25
    );
}

// 渲染设置页面
// 这里假设 ars_render_settings_page 函数在其他文件中定义

// 处理脚本和样式加载的函数
function ars_enqueue_scripts_and_styles() {
    // 引入生成编号的脚本
    wp_enqueue_script('archive-receipt-generate-number', plugins_url('assets/js/generate-number.js', __FILE__), array('jquery'), '1.0', true);

    // 如果你有样式文件，也可以在这里加载
    wp_enqueue_style('archive-receipt-style', plugins_url('assets/css/receipt-style.css', __FILE__));
}

// 定义 ars_create_database_tables 函数
function ars_create_database_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'archive_receipts';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        receipt_number varchar(255) NOT NULL,
        company_name varchar(255) NOT NULL,
        applicant varchar(255) NOT NULL,
        submit_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        status varchar(255) NOT NULL,
        content text NOT NULL,
        receipt_recipient varchar(255) NOT NULL,
        application_department varchar(255) NOT NULL,
        archive_details text NOT NULL,
        database_location varchar(255) NOT NULL,
        database_name varchar(255) NOT NULL,
        file_path_structure text NOT NULL,
        archive_capacity float NOT NULL,
        archive_completion_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// 定义 ars_query_receipt 函数
function ars_query_receipt($receipt_number) {
    global $wpdb;
    $table = $wpdb->prefix . 'archive_receipts';

    $query = $wpdb->prepare(
        "SELECT * FROM $table WHERE receipt_number = %s",
        sanitize_text_field($receipt_number)
    );
    
    $result = $wpdb->get_row($query, ARRAY_A);

    if ($result) {
        // 过滤掉“回执ID”、“状态”和“提交时间”这三个参数
        unset($result['receipt_id']);
        unset($result['status']);
        unset($result['submit_time']);

        $result['verification_url'] = add_query_arg(
            'receipt_id',
            absint($result['id']),
            get_permalink(get_option('ars_query_page_id'))
        );
    }
    return $result;
}

// 处理存档回执查询的 AJAX 请求
function ars_ajax_query_receipt() {
    if (isset($_POST['receipt_number'])) {
        $receipt_number = sanitize_text_field($_POST['receipt_number']);
        $result = ars_query_receipt($receipt_number);

        if ($result) {
            $html = ars_render_receipt_template($result);
            echo $html;
        } else {
            echo '<p>' . __('未找到匹配的回执，请检查回执编号。', 'archive-receipt') . '</p>';
        }
    }
    wp_die(); // 必须调用 wp_die 来结束 AJAX 请求
}

//处理单据下载代码
function ars_ajax_download_receipt_html() {
    if (isset($_GET['file'])) {
        $file = urldecode($_GET['file']);
        if (file_exists($file)) {
            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="archive_receipt_' . $_GET['receipt_number'] . '.html"');
            readfile($file);
            unlink($file); // 删除临时文件
            exit;
        }
    }
    echo '<div class="error"><p>' . __('文件下载失败，请稍后重试。', 'archive-receipt') . '</p></div>';
    exit;
}

add_action('wp_ajax_ars_query_receipt', 'ars_query_receipt_callback');
add_action('wp_ajax_nopriv_ars_query_receipt', 'ars_query_receipt_callback');

function ars_query_receipt_callback() {
    global $wpdb;
    $receipt_number = sanitize_text_field($_POST['receipt_number']);
    $receipts_table = $wpdb->prefix . 'archive_receipts';
    $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $receipts_table WHERE receipt_number = %s", $receipt_number));

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
    if ($result) {
        $result = (array) $result;
        // 过滤掉“回执ID”、“状态”和“提交时间”这三个参数
        unset($result['receipt_id']);
        unset($result['status']);
        unset($result['submit_time']);

        $result['verification_url'] = add_query_arg(
            'receipt_id',
            absint($result['id']),
            get_permalink(get_option('ars_query_page_id'))
        );
        $html = ars_render_receipt_template($result);
        echo $html;
    } else {
        echo '<p>' . __('未找到相关回执信息。', 'archive-receipt') . '</p>';
    }

    wp_die();
}

// 错误日志记录
if (!empty($wpdb->last_error)) {
    error_log('Archive System DB Error: ' . $wpdb->last_error);
}