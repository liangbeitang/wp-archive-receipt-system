<?php
// 显示页面管理界面
function ars_display_page_manager() {
    // 获取存档回执查询系统页面 ID
    $query_page_id = get_option('ars_query_page_id');
    // 获取存档回执录入页面 ID
    $submit_page_id = get_option('ars_submit_page_id');

    // 获取存档回执查询系统页面信息
    $query_page = get_post($query_page_id);
    // 获取存档回执录入页面信息
    $submit_page = get_post($submit_page_id);

    // 处理删除页面请求
    if (isset($_GET['action']) && $_GET['action'] === 'delete_page' && isset($_GET['page_id'])) {
        $page_id_to_delete = intval($_GET['page_id']);
        if ($page_id_to_delete === $query_page_id || $page_id_to_delete === $submit_page_id) {
            wp_delete_post($page_id_to_delete, true);
            if ($page_id_to_delete === $query_page_id) {
                delete_option('ars_query_page_id');
            } elseif ($page_id_to_delete === $submit_page_id) {
                delete_option('ars_submit_page_id');
            }
            echo '<div class="updated"><p>'.__('页面已成功删除。', 'archive-receipt').'</p></div>';
            // 刷新页面以更新显示
            $query_page_id = get_option('ars_query_page_id');
            $submit_page_id = get_option('ars_submit_page_id');
            $query_page = get_post($query_page_id);
            $submit_page = get_post($submit_page_id);
        }
    }

    // 输出页面管理界面 HTML
    ?>
    <div class="wrap">
        <h1><?php _e('存档回执系统页面管理', 'archive-receipt'); ?></h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('页面名称', 'archive-receipt'); ?></th>
                    <th><?php _e('页面状态', 'archive-receipt'); ?></th>
                    <th><?php _e('操作', 'archive-receipt'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query_page) { ?>
                    <tr>
                        <td><?php echo esc_html($query_page->post_title); ?></td>
                        <td><?php echo esc_html($query_page->post_status); ?></td>
                        <td>
                            <a href="<?php echo get_edit_post_link($query_page_id); ?>"><?php _e('编辑', 'archive-receipt'); ?></a> |
                            <a href="<?php echo add_query_arg(array('action' => 'delete_page', 'page_id' => $query_page_id), $_SERVER['REQUEST_URI']); ?>" onclick="return confirm('<?php _e('确定要删除此页面吗？', 'archive-receipt'); ?>');"><?php _e('删除', 'archive-receipt'); ?></a>
                        </td>
                    </tr>
                <?php } else { ?>
                    <tr>
                        <td><?php _e('存档回执查询系统', 'archive-receipt'); ?></td>
                        <td><?php _e('未找到页面', 'archive-receipt'); ?></td>
                        <td></td>
                    </tr>
                <?php } ?>
                <?php if ($submit_page) { ?>
                    <tr>
                        <td><?php echo esc_html($submit_page->post_title); ?></td>
                        <td><?php echo esc_html($submit_page->post_status); ?></td>
                        <td>
                            <a href="<?php echo get_edit_post_link($submit_page_id); ?>"><?php _e('编辑', 'archive-receipt'); ?></a> |
                            <a href="<?php echo add_query_arg(array('action' => 'delete_page', 'page_id' => $submit_page_id), $_SERVER['REQUEST_URI']); ?>" onclick="return confirm('<?php _e('确定要删除此页面吗？', 'archive-receipt'); ?>');"><?php _e('删除', 'archive-receipt'); ?></a>
                        </td>
                    </tr>
                <?php } else { ?>
                    <tr>
                        <td><?php _e('存档回执录入', 'archive-receipt'); ?></td>
                        <td><?php _e('未找到页面', 'archive-receipt'); ?></td>
                        <td></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php
}

// 添加页面管理菜单项到插件管理菜单下
function ars_add_page_manager_menu() {
    add_submenu_page(
        'archive-receipt-settings',
        __('页面管理', 'archive-receipt'),
        __('页面管理', 'archive-receipt'),
        'manage_options',
        'archive-receipt-page-manager',
        'ars_display_page_manager'
    );
}
add_action('admin_menu', 'ars_add_page_manager_menu');