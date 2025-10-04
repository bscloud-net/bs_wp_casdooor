<?php
/**
 * Plugin Name: Bscloudid
 * Description: WordPress插件用于集成Casdoor认证。
 * Version: 1.0.0
 * Author: xiaozhi
 * License: GPL-2.0+
 */

// 防止直接访问。
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量。
define('BSCLOUDID_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BSCLOUDID_PLUGIN_URL', plugin_dir_url(__FILE__));

// 包含必要文件。
require_once BSCLOUDID_PLUGIN_DIR . 'includes/class-bscloudid-settings.php';
require_once BSCLOUDID_PLUGIN_DIR . 'includes/class-bscloudid-auth.php';

// 初始化插件。
function bscloudid_init() {
    if (is_admin()) {
        new Bscloudid_Settings();
    }
    new Bscloudid_Auth();
}
add_action('plugins_loaded', 'bscloudid_init');