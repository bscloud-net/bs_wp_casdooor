<?php
/**
 * Class Bscloudid_Settings
 * 处理插件设置和配置。
 */
class Bscloudid_Settings {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu() {
        add_options_page(
            'Bscloudid 设置',
            'Bscloudid',
            'manage_options',
            'bscloudid',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting('bscloudid_options', 'bscloudid_options');

        add_settings_section(
            'bscloudid_section',
            'Casdoor 配置',
            array($this, 'render_section'),
            'bscloudid'
        );

        add_settings_field(
            'casdoor_endpoint',
            'Casdoor 端点',
            array($this, 'render_endpoint_field'),
            'bscloudid',
            'bscloudid_section'
        );

        add_settings_field(
            'authorization_endpoint',
            '授权端点',
            array($this, 'render_authorization_endpoint_field'),
            'bscloudid',
            'bscloudid_section'
        );

        add_settings_field(
            'token_endpoint',
            '令牌端点',
            array($this, 'render_token_endpoint_field'),
            'bscloudid',
            'bscloudid_section'
        );

        add_settings_field(
            'username_field',
            '用户名字段',
            array($this, 'render_username_field'),
            'bscloudid',
            'bscloudid_section'
        );

        add_settings_field(
            'email_field',
            '邮箱字段',
            array($this, 'render_email_field'),
            'bscloudid',
            'bscloudid_section'
        );

        add_settings_field(
            'displayname_field',
            '显示名称字段',
            array($this, 'render_displayname_field'),
            'bscloudid',
            'bscloudid_section'
        );

        add_settings_field(
            'nicename_field',
            '昵称字段',
            array($this, 'render_nicename_field'),
            'bscloudid',
            'bscloudid_section'
        );

        add_settings_field(
            'phone_field',
            '电话字段',
            array($this, 'render_phone_field'),
            'bscloudid',
            'bscloudid_section'
        );

        add_settings_field(
            'avatar_field',
            '头像字段',
            array($this, 'render_avatar_field'),
            'bscloudid',
            'bscloudid_section'
        );

        add_settings_field(
            'isadmin_field',
            '管理员字段',
            array($this, 'render_isadmin_field'),
            'bscloudid',
            'bscloudid_section'
        );

        add_settings_field(
            'userinfo_endpoint',
            '用户信息端点',
            array($this, 'render_userinfo_endpoint_field'),
            'bscloudid',
            'bscloudid_section'
        );

        add_settings_field(
            'client_id',
            '客户端 ID',
            array($this, 'render_client_id_field'),
            'bscloudid',
            'bscloudid_section'
        );

        add_settings_field(
            'client_secret',
            '客户端密钥',
            array($this, 'render_client_secret_field'),
            'bscloudid',
            'bscloudid_section'
        );
    }

    public function render_username_field() {
        $options = get_option('bscloudid_options');
        ?>
        <input type="text" name="bscloudid_options[username_field]" value="<?php echo esc_attr($options['username_field'] ?? 'data.name'); ?>" class="regular-text" />
        <?php
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Bscloudid 设置</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('bscloudid_options');
                do_settings_sections('bscloudid');
                submit_button('保存更改');
                ?>
            </form>
        </div>
        <?php
    }

    public function render_section() {
        echo '<p>请在下方填写 Casdoor 配置信息：</p>';
        echo '<p>回调地址: <code>' . esc_url(home_url('/?state=bscloudid_auth')) . '</code></p>';
        echo '<p>长代码: <code>' . esc_url(wp_login_url() . '?bscloudid_auth=1') . '</code></p>';
        echo '<p>短代码: <code>[bscloudid_login_button]</code></p>';
    }

    public function render_endpoint_field() {
        $options = get_option('bscloudid_options');
        ?>
        <input type="text" name="bscloudid_options[casdoor_endpoint]" value="<?php echo esc_attr($options['casdoor_endpoint'] ?? ''); ?>" class="regular-text" />
        <?php
    }

    public function render_authorization_endpoint_field() {
        $options = get_option('bscloudid_options');
        ?>
        <input type="text" name="bscloudid_options[authorization_endpoint]" value="<?php echo esc_attr($options['authorization_endpoint'] ?? ''); ?>" class="regular-text" />
        <?php
    }

    public function render_token_endpoint_field() {
        $options = get_option('bscloudid_options');
        ?>
        <input type="text" name="bscloudid_options[token_endpoint]" value="<?php echo esc_attr($options['token_endpoint'] ?? ''); ?>" class="regular-text" />
        <?php
    }

    public function render_userinfo_endpoint_field() {
        $options = get_option('bscloudid_options');
        ?>
        <input type="text" name="bscloudid_options[userinfo_endpoint]" value="<?php echo esc_attr($options['userinfo_endpoint'] ?? ''); ?>" class="regular-text" />
        <?php
    }

    public function render_client_id_field() {
        $options = get_option('bscloudid_options');
        ?>
        <input type="text" name="bscloudid_options[client_id]" value="<?php echo esc_attr($options['client_id'] ?? ''); ?>" class="regular-text" />
        <?php
    }

    public function render_client_secret_field() {
        $options = get_option('bscloudid_options');
        ?>
        <input type="password" name="bscloudid_options[client_secret]" value="<?php echo esc_attr($options['client_secret'] ?? ''); ?>" class="regular-text" />
        <?php
    }

    public function render_nicename_field() {
        $options = get_option('bscloudid_options');
        ?>
        <input type="text" name="bscloudid_options[nicename_field]" value="<?php echo esc_attr($options['nicename_field'] ?? 'data.name'); ?>" class="regular-text" />
        <?php
    }

    public function render_isadmin_field() {
        $options = get_option('bscloudid_options');
        ?>
        <input type="text" name="bscloudid_options[isadmin_field]" value="<?php echo esc_attr($options['isadmin_field'] ?? 'data.isAdmin'); ?>" class="regular-text" />
        <?php
    }
}