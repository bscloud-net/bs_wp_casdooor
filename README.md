# WordPress Casdoor 插件


一个用于将 WordPress 用户系统与 [Casdoor](https://casdoor.org/) 集成的插件。通过此插件，用户可以使用 Casdoor 作为身份提供商（IdP）登录 WordPress 网站。

## 功能特性

- **单点登录（SSO）**：支持通过 Casdoor 进行用户认证。
- **用户信息同步**：自动同步 Casdoor 用户信息到 WordPress。
- **字段映射**：支持自定义 Casdoor 字段与 WordPress 用户字段的映射。
- **管理员角色控制**：根据 Casdoor 返回的用户角色自动设置 WordPress 用户权限。

## 安装

1. 下载插件 ZIP 文件。
2. 在 WordPress 后台导航到 **插件 > 添加新插件 > 上传插件**。
3. 上传 ZIP 文件并激活插件。

## 配置

1. 在 WordPress 后台导航到 **设置 > Bscloudid**。
2. 填写以下信息：
   - **Casdoor 端点**：您的 Casdoor 实例地址。
   - **客户端 ID** 和 **客户端密钥**：从 Casdoor 应用中获取。
   - **字段映射**：配置 Casdoor 字段与 WordPress 用户字段的对应关系。

## 使用

1. 在 WordPress 登录页面，点击 **使用 Casdoor 登录** 按钮。
2. 跳转到 Casdoor 登录页面，输入您的凭据。
3. 登录成功后，返回 WordPress 网站。

## 开发

### 依赖

- WordPress 5.0+
- PHP 7.4+
- Casdoor 1.0+

### 构建

1. 克隆仓库：
   ```bash
   git clone https://github.com/bscloud-net/bs_wp_casdooor.git
