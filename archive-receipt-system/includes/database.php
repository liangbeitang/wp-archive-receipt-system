<?php
// 删除 ars_create_database_tables 函数定义

function ars_insert_receipt($receipt_number, $company_name, $applicant, $submit_time, $status, $content, $receipt_recipient, $application_department, $archive_details, $database_location, $database_name, $file_path_structure, $archive_capacity, $archive_completion_time) {
    global $wpdb;
    // 修复此处，添加缺失的引号
    $table_name = $wpdb->prefix . 'archive_receipts'; 

    $result = $wpdb->insert(
        $table_name,
        array(
            'receipt_number' => $receipt_number,
            'company_name' => $company_name,
            'applicant' => $applicant,
            'submit_time' => $submit_time,
            'status' => $status,
            'content' => $content,
            'receipt_recipient' => $receipt_recipient,
            'application_department' => $application_department,
            'archive_details' => $archive_details,
            'database_location' => $database_location,
            'database_name' => $database_name,
            'file_path_structure' => $file_path_structure,
            'archive_capacity' => $archive_capacity,
            'archive_completion_time' => $archive_completion_time
        )
    );

    if ($result === false) {
        error_log('Database insert error: ' . $wpdb->last_error);
        return false;
    }

    return true;
}

function ars_query_receipt($receipt_number) {
    global $wpdb;
    $table = $wpdb->prefix . 'archive_receipts';

    $query = $wpdb->prepare(
        "SELECT * FROM $table WHERE receipt_number = %s",
        sanitize_text_field($receipt_number)
    );
    
    $result = $wpdb->get_row($query, ARRAY_A);

    if ($result) {
        $result['verification_url'] = add_query_arg(
            'receipt_id',
            absint($result['receipt_id']),
            get_permalink(get_option('ars_query_page_id'))
        );
    }
    return $result;
}

function ars_check_submit_permission() {
    $allowed = get_option('ars_allowed_roles', ['editor', 'administrator']);
    $user = wp_get_current_user();
    
    // 空值安全处理
    $allowed = is_array($allowed) ? $allowed : [];
    $roles = is_array($user->roles) ? $user->roles : [];

    return (bool) array_intersect($allowed, $roles);
}

function ars_validate_receipt_number($number) {
    // 类型安全处理
    $number = is_scalar($number) ? (string)$number : '';
    
    // 新版验证规则：14位数字 + 3位字母数字组合（总长度17）
    return preg_match('/^\d{14}[A-Za-z0-9]{3}$/', $number);
}