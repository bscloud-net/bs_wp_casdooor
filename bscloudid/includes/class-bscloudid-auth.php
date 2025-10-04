<?php
/**
 * Class Bscloudid_Auth
 * 处理 Casdoor 认证逻辑（已修复头像同步问题）。
 */
class Bscloudid_Auth {
    public function __construct() {
        add_action('init', array($this, 'handle_casdoor_callback'));
        add_filter('authenticate', array($this, 'authenticate_user'), 10, 3);
        add_action('login_form', array($this, 'add_casdoor_login_button'));
        add_shortcode('bscloudid_login', array($this, 'bscloudid_login_shortcode'));
        add_shortcode('bscloudid_login_button', array($this, 'bscloudid_login_button_shortcode'));
    }

    public function handle_casdoor_callback() {
        if (isset($_GET['code']) && isset($_GET['state']) && $_GET['state'] === 'bscloudid_auth') {
            $options = get_option('bscloudid_options');
            $token_url = $options['token_endpoint'];
            $redirect_uri = home_url('/');

            $response = wp_remote_post($token_url, array(
                'body' => array(
                    'grant_type' => 'authorization_code',
                    'client_id' => $options['client_id'],
                    'client_secret' => $options['client_secret'],
                    'code' => $_GET['code'],
                    'redirect_uri' => $redirect_uri,
                ),
            ));

            if (!is_wp_error($response)) {
                $body = json_decode($response['body'], true);
                if (isset($body['access_token'])) {
                    $user_info = $this->get_user_info($options['userinfo_endpoint'], $body['access_token']);
                    if ($user_info) {
                        // 【新增日志】确认获取到的 user_info 中是否有 avatar
                        error_log('Casdoor 返回的 user_info 完整数据：' . print_r($user_info, true));
                        $this->login_user($user_info);
                    }
                }
            }
        }
    }

    private function get_user_info($endpoint, $access_token) {
        $response = wp_remote_get($endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
        ));

        if (!is_wp_error($response)) {
            $user_info = json_decode($response['body'], true);
            // 关键：确保提取 Casdoor 返回的 "data" 层（你的 JSON 中头像在 data.avatar）
            $data = $user_info['data'] ?? $user_info;

            // 【新增日志】确认提取到的 data 中是否有 avatar
            error_log('提取到的 Casdoor data 层数据：' . print_r($data, true));
            error_log('提取到的 avatar 字段值：' . ($data['avatar'] ?? '无'));

            return $data; // 返回 data 层，后续直接从 data 中取字段
        }
        return false;
    }

    /**
     * 【核心修复】确保头像字段正确存储到 WordPress 元数据
     */
    private function login_user($user_info) {
        // 1. 提取 Casdoor 字段（重点：明确从 user_info 中取 avatar，对应 data.avatar）
        $casdoor_data = array(
            'sub'           => $user_info['id'] ?? $user_info['sub'] ?? '',
            'name'          => $user_info['name'] ?? '',
            'email'         => $user_info['email'] ?? '',
            'given_name'    => $user_info['firstName'] ?? '',
            'family_name'   => $user_info['lastName'] ?? '',
            'display_name'  => $user_info['displayName'] ?? $user_info['name'] ?? '',
            'avatar'        => $user_info['avatar'] ?? '', // 【关键】直接取 user_info.avatar（对应 data.avatar）
            'phone'         => $user_info['phone'] ?? '',
            'id_card'       => $user_info['idCard'] ?? '',
            'owner'         => $user_info['owner'] ?? '',
            'is_admin'      => $user_info['isAdmin'] ?? false,
            'created_time'  => $user_info['createdTime'] ?? ''
        );

        // 验证必填字段
        if (empty($casdoor_data['email']) || empty($casdoor_data['name'])) {
            wp_die('用户信息不完整：缺少邮箱或用户名', '登录错误', array('response' => 400));
        }

        // 【新增日志】确认 casdoor_data 中的 avatar 是否正确
        error_log('casdoor_data 中的 avatar 值：' . $casdoor_data['avatar']);

        // 2. 查找/创建 WordPress 用户
        $user = get_user_by('email', $casdoor_data['email']);
        if (!$user) {
            // 处理用户名重复
            $username = sanitize_user($casdoor_data['name'], true);
            $suffix = 1;
            while (username_exists($username)) {
                $username = sanitize_user($casdoor_data['name'] . '_' . $suffix, true);
                $suffix++;
            }

            // 创建用户
            $user_id = wp_insert_user(array(
                'user_login'    => $username,
                'user_email'    => $casdoor_data['email'],
                'first_name'    => $casdoor_data['given_name'],
                'last_name'     => $casdoor_data['family_name'],
                'display_name'  => $casdoor_data['display_name'],
                'role'          => $casdoor_data['is_admin'] ? 'administrator' : 'subscriber',
                'user_registered' => empty($casdoor_data['created_time']) ? current_time('mysql') : date('Y-m-d H:i:s', strtotime($casdoor_data['created_time']))
            ));

            if (is_wp_error($user_id)) {
                error_log('用户创建失败：' . $user_id->get_error_message());
                wp_die('用户创建失败：' . $user_id->get_error_message(), '登录错误', array('response' => 500));
            }
            $user = get_user_by('id', $user_id);
        }

        // 3. 同步字段（重点：确保头像存储到 usermeta）
        if ($user) {
            // 更新基础信息
            wp_update_user(array(
                'ID'            => $user->ID,
                'first_name'    => $casdoor_data['given_name'],
                'last_name'     => $casdoor_data['family_name'],
                'display_name'  => $casdoor_data['display_name']
            ));

            // 【核心】存储 Casdoor 特有字段（含 avatar）
            $meta_mapping = array(
                'casdoor_user_id'   => $casdoor_data['sub'],
                'casdoor_avatar'    => $casdoor_data['avatar'], // 【关键】头像字段存储到 casdoor_avatar 元数据
                'casdoor_phone'     => $casdoor_data['phone'],
                'casdoor_id_card'   => $casdoor_data['id_card'],
                'casdoor_owner'     => $casdoor_data['owner'],
                'casdoor_is_admin'  => $casdoor_data['is_admin'] ? '1' : '0'
            );

            // 循环存储，并打印日志确认结果
            foreach ($meta_mapping as $meta_key => $meta_value) {
                $update_result = update_user_meta($user->ID, $meta_key, $meta_value);
                // 【新增日志】确认每个字段的存储结果（true=成功，false=无变化，null=失败）
                error_log('存储 ' . $meta_key . '：值=' . $meta_value . '，结果=' . print_r($update_result, true));
            }

            // 登录并重定向
            wp_set_auth_cookie($user->ID, true, is_ssl());
            wp_redirect(home_url('/'));
            exit;
        }

        wp_die('登录失败：无法找到或创建用户', '登录错误', array('response' => 500));
    }

    // 以下函数保持不变
    public function authenticate_user($user, $username, $password) {
        if (empty($username) && empty($password)) {
            $options = get_option('bscloudid_options');
            $auth_url = $options['authorization_endpoint'];
            $redirect_uri = home_url('/');

            // 确保获取完整用户信息（含头像）
            $auth_url .= '?client_id=' . urlencode($options['client_id'])
                . '&redirect_uri=' . urlencode($redirect_uri)
                . '&response_type=code'
                . '&state=bscloudid_auth'
                . '&scope=openid email profile avatar'; // 必须加 profile 才能返回 avatar 等字段

            wp_redirect($auth_url);
            exit;
        }
        return $user;
    }

    public function add_casdoor_login_button() {
        ?>
        <div style="margin-top: 20px; text-align: center;">
            <p>或使用以下方式登录：</p>
            <a href="<?php echo esc_url(wp_login_url() . '?bscloudid_auth=1'); ?>" class="button button-primary">
                使用 Casdoor 登录
            </a>
        </div>
        <?php
    }

    public function bscloudid_login_shortcode($atts) {
        ob_start();
        $this->add_casdoor_login_button();
        return ob_get_clean();
    }

    public function bscloudid_login_button_shortcode($atts) {
        ob_start();
        ?>
        <a href="<?php echo esc_url(wp_login_url() . '?bscloudid_auth=1'); ?>" class="button button-primary">
            使用 Casdoor 登录
        </a>
        <?php
        return ob_get_clean();
    }
}