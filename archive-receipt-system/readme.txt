=== 存档回执管理系统 ===
Contributors: 梁北棠
Donate link: https://www.liangbeitang.com/open-source-coding/wp-plugin/archive-receipt-system/
Tags: 存档管理, 审批系统, 回执生成
Requires at least: 5.6
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

专业的企业级数字存档回执管理解决方案，支持多语言、权限分级和自动化PDF生成。

== 功能特性 ==
- 自动化创建查询/录入双页面系统
- 基于角色的访问控制（RBAC）
- 智能回执编号验证（正则表达式：^\d{14}[A-Za-z0-9]{4}$）
- 数据加密存储（AES-256）
- 多格式导出支持（HTML/PDF/PNG）
- 集成二维码防伪验证
- WP-CLI命令支持
- 多语言支持（内置中英文）

== 文件目录 ==
```
archive-receipt-system/
├── archive-receipt-system.php      // 主插件文件
├── admin/                         // 后台模块
│   ├── settings-page.php          // 系统设置界面
│   └── role-manager.php           // 角色权限管理
├── public/                        // 前端模块
│   ├── query-form.php             // 查询表单模板
│   └── submission-form.php         // 录入表单模板
├── templates/                     // 输出模板
│   ├── pdf-template.php           // PDF生成引擎
│   └── receipt-template.php       // 回执显示模板
├── assets/                        // 静态资源
│   ├── css/                       // 样式表
│   │   └── admin-styles.css       // 后台样式
│   └── js/                        // 脚本文件
│       ├── form-validation.js     // 表单验证
│       └── qrcode-generator.js    // 二维码生成
├── languages/                     // 翻译文件
│   ├── archive-receipt-zh_CN.po   // 中文翻译
│   └── archive-receipt-en_US.po   // 英文翻译
└── includes/                      // 核心模块
    ├── database.php               // 数据库操作
    └── security.php              // 安全验证
```

== 安装指南 ==

1. 通过WordPress仪表盘安装：
   - 访问"插件 → 安装插件"
   - 搜索"存档回执管理系统"
   - 点击"立即安装"并激活

2. 手动安装：
   - 下载ZIP压缩包
   - 解压至`wp-content/plugins/`目录
   - 在插件列表激活

== 使用说明 ==

1. 初始设置：
   - 访问"设置 → 存档系统"
   - 配置允许访问的用户角色
   - 设置默认公司信息

2. 快速使用：
   - 前台查询：访问`/存档回执查询系统`
   - 数据录入：访问`/存档回执录入`（需权限）

3. WP-CLI命令：
   ```
   wp archive generate-receipt [数量]  # 批量生成测试数据
   wp archive export-all              # 导出全量数据为CSV
   ```

== 常见问题 ==

= 数据库表未自动创建怎么办？ =
1. 检查`wp_options`表中是否存在`ars_db_version`记录
2. 执行`wp db repair`检查表完整性
3. 手动运行`ars_create_database_tables()`函数

= 如何自定义回执模板？ =
1. 复制`templates/receipt-template.php`到主题目录
2. 在子主题中创建`archive-receipt/`目录
3. 修改模板文件后需清除缓存

= 权限验证失败怎么处理？ =
1. 检查"设置 → 存档系统 → 用户权限"
2. 确保用户角色包含以下能力：
   - `edit_archive_receipts` (录入权限)
   - `view_archive_receipts` (查询权限)

== 升级通知 ==
1.0 初始版本：
- 实现核心存档管理功能
- 支持基础PDF导出
- 集成权限管理系统

== 翻译贡献 ==
- 中文翻译：由开发者维护
- 英文翻译：通过POEdit更新

== 代码规范 ==
本插件遵循：
- WordPress编码标准 (WPCS)
- PSR-4自动加载规范
- SemVer版本控制

== 安全披露 ==
发现漏洞请邮件联系：contact@liangbeitang.com
``` 

该文件符合WordPress插件目录的以下要求：

1. **格式规范**：严格遵循标准头信息格式
2. **兼容性声明**：明确标注PHP和WP版本要求
3. **目录透明度**：详细列出所有核心文件
4. **多语言支持**：包含翻译指南和文件路径
5. **安全声明**：提供漏洞披露渠道
6. **许可协议**：使用GPLv2+兼容协议
