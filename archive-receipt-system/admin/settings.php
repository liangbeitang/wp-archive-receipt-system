<?php

// 显示存档回执系统的设置页面
function ars_display_settings_page() {
    // 检查是否有表单提交
    if (isset($_POST['ars_allowed_roles']) && isset($_POST['ars_company_name'])) {
        // 过滤并保存允许访问录入页面的用户角色
        $allowed_roles = array_map('sanitize_text_field', $_POST['ars_allowed_roles']);
        update_option('ars_allowed_roles', $allowed_roles);

        // 保存公司名称
        $company_name = sanitize_text_field($_POST['ars_company_name']);
        update_option('ars_company_name', $company_name);

        // 显示设置保存成功的提示信息
        echo '<div class="updated"><p>'.__('设置已保存。', 'archive-receipt').'</p></div>';
    }

    // 获取当前允许访问录入页面的用户角色，如果没有则使用默认值
    $allowed_roles = get_option('ars_allowed_roles', ['editor', 'administrator']);
    // 获取当前设置的公司名称
    $company_name = get_option('ars_company_name', '');

    // 获取 WordPress 中所有的用户角色
    global $wp_roles;
    $roles = $wp_roles->roles;

    // 输出设置页面的 HTML 结构
    ?>
    <div class="wrap">
        <h1><?php _e('存档回执系统设置', 'archive-receipt'); ?></h1>
        <form method="post">
            <h2><?php _e('用户权限设置', 'archive-receipt'); ?></h2>
            <!-- 遍历所有用户角色，显示复选框 -->
            <?php foreach ($roles as $role_name => $role_info) { ?>
                <label>
                    <input type="checkbox" name="ars_allowed_roles[]" value="<?php echo esc_attr($role_name); ?>" <?php checked(in_array($role_name, $allowed_roles)); ?>>
                    <?php echo esc_html($role_info['name']); ?>
                </label><br>
            <?php } ?>
            <h2><?php _e('公司名称设置', 'archive-receipt'); ?></h2>
            <label for="ars_company_name"><?php _e('公司名称', 'archive-receipt'); ?></label>
            <input type="text" id="ars_company_name" name="ars_company_name" value="<?php echo esc_attr($company_name); ?>" required>
            <p class="submit">
                <!-- 提交按钮 -->
                <input type="submit" class="button-primary" value="<?php _e('保存设置', 'archive-receipt'); ?>">
            </p>
        </form>

        <h2><?php _e('短代码使用说明', 'archive-receipt'); ?></h2>
        <p><?php _e('您可以在任何 WordPress 页面或文章中插入以下短代码来激活相应的功能。', 'archive-receipt'); ?></p>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('功能描述', 'archive-receipt'); ?></th>
                    <th><?php _e('短代码', 'archive-receipt'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php _e('显示存档回执查询表单', 'archive-receipt'); ?></td>
                    <td><code>[archive_query]</code></td>
                </tr>
                <tr>
                    <td><?php _e('显示存档回执录入表单', 'archive-receipt'); ?></td>
                    <td><code>[archive_submit]</code></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}

// 渲染设置页面
function ars_render_settings_page() {
    ars_display_settings_page();
}