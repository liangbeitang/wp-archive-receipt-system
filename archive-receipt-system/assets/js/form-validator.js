jQuery(document).ready(function ($) {
    // 验证存档回执查询表单
    $('#archive-query-form').validate({
        rules: {
            receipt_number: {
                required: true,
                // 可根据实际回执编号规则添加自定义正则验证
                // pattern: /^[A-Za-z0-9]{8}$/ 
            }
        },
        messages: {
            receipt_number: {
                required: '<?php _e('请输入回执编号', 'archive-receipt'); ?>',
                // pattern: '<?php _e('回执编号格式不正确', 'archive-receipt'); ?>'
            }
        },
        errorElement: 'span',
        errorClass: 'error-message',
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        }
    });

    // 验证存档回执录入表单
    $('#archive-submit-form').validate({
        rules: {
            receipt_number: {
                required: true,
                // 可根据实际回执编号规则添加自定义正则验证
                // pattern: /^[A-Za-z0-9]{8}$/ 
            },
            company_name: {
                required: true
            },
            applicant: {
                required: true
            },
            content: {
                required: true
            }
        },
        messages: {
            receipt_number: {
                required: '<?php _e('请输入回执编号', 'archive-receipt'); ?>',
                // pattern: '<?php _e('回执编号格式不正确', 'archive-receipt'); ?>'
            },
            company_name: {
                required: '<?php _e('请输入公司名称', 'archive-receipt'); ?>'
            },
            applicant: {
                required: '<?php _e('请输入申请人姓名', 'archive-receipt'); ?>'
            },
            content: {
                required: '<?php _e('请输入回执内容', 'archive-receipt'); ?>'
            }
        },
        errorElement: 'span',
        errorClass: 'error-message',
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        }
    });
});