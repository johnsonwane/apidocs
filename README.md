# API 文档管理网站（Uniapp v3 + PHP + MySQL）

这是一个单项目 API 文档平台，提供两级权限：
- 普通用户：无需登录，可查看与测试 API。
- 管理员：登录后可管理分类和 API（新增/编辑/删除）。

## 架构思路

- 前端：Uniapp v3（Vue3）实现 PC 页面，左侧分类、右侧 API 列表与详情测试区，另有独立管理页。
- 后端：纯 PHP 路由入口（`backend/public/index.php`）+ PDO 访问 MySQL。
- 鉴权：管理员登录后获取 Bearer Token，写入 `admin_sessions`，仅管理接口校验 Token。
- 测试能力：
  - 若 API 配置了 `test_url`，后端会按方法代理请求并返回真实响应。
  - 未配置 `test_url` 时返回文档中的响应示例（mock 模式）。

## 目录

- `frontend/`：Uniapp 页面与 API 调用。
- `backend/`：PHP 接口服务。
- `backend/sql/schema.sql`：建表 + 初始化测试数据。

## 启动步骤（本地开发）

### 1) 初始化数据库

```bash
mysql -uroot -proot < backend/sql/schema.sql
```

### 2) 启动后端

```bash
php -S 0.0.0.0:8000 -t backend/public
```

### 3) 运行前端

将 `frontend` 导入 HBuilderX 作为 Uniapp 项目，运行到 H5。

> 前端默认 API 基地址为 `/api`（见 `frontend/common/api.js`），适配前后端同域部署。

## 你的部署方式（前后端分离，同域不同路径）

你描述的方式是：
- `https://apidocs.hahahaxinli.com/` 提供前端 `index.html`
- `https://apidocs.hahahaxinli.com/api/*` 提供 PHP 接口

这是完全可行且推荐的方式，不需要把站点根目录指向 `backend/public`。

### Apache 参考配置（你当前环境）

确保启用模块：`rewrite`、`alias`、`proxy_fcgi`（或你的 PHP 处理方式）。

```apacheconf
<VirtualHost *:80>
    ServerName apidocs.hahahaxinli.com

    # 前端静态目录
    DocumentRoot /var/www/apidocs/frontend-dist
    <Directory /var/www/apidocs/frontend-dist>
        AllowOverride All
        Require all granted
    </Directory>

    # 后端 API 映射到 /api
    Alias /api /var/www/apidocs/backend/public
    <Directory /var/www/apidocs/backend/public>
        AllowOverride All
        Require all granted
    </Directory>

    # PHP-FPM 示例（按你的版本调整 sock 路径）
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost/"
    </FilesMatch>
</VirtualHost>
```

然后确认 `backend/public/.htaccess` 已生效（仓库已提供），用于把 `/api/*` 路由转发到 `index.php`。

前端如果使用 history 路由，可在前端发布目录放置 `.htaccess`：

```apacheconf
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.html [L]
</IfModule>
```

## CORS 说明

- 如果前后端同域（都在 `apidocs.hahahaxinli.com`，只是路径不同），通常不会触发跨域。
- 若你本地联调（例如 `http://localhost:5174` 调线上 `/api`），后端已支持预检与白名单。
- 白名单在 `backend/config.php` 的 `cors.allowed_origins`。

## 快速自检

1. 先访问：`https://apidocs.hahahaxinli.com/api/health`（应返回 JSON `ok`）。
2. 再调用：`POST https://apidocs.hahahaxinli.com/api/auth/login`。
3. 若仍 404，优先检查 Apache 的 `Alias /api` 与 `AllowOverride` 是否生效，及 PHP-FPM 是否正常。

## 测试账号

- 用户名：`admin`
- 密码：`admin123`
