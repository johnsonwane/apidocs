# API 文档管理网站（Uniapp v3 + PHP + MySQL）

这是一个单项目 API 文档平台，提供两级权限：
- 普通用户：无需登录，可查看与测试 API。
- 管理员：登录后可管理分类和 API（新增/编辑/删除）。

## 你要求的部署结构（已按此实现）

- 前端构建产物（Uniapp HBuilderX H5）放在：`https://apidocs.hahahaxinli.com/`
- 后端 PHP 文件放在：`https://apidocs.hahahaxinli.com/api/`
- **后端不使用单入口路由分发**，改为多文件接口（直连 `.php` 文件）。

## 后端字段增强

API 文档新增了两块结构化字段：
- `request_params`：请求参数表（JSON 数组）
- `response_fields`：返回字段表（JSON 数组）

前端详情页会优先展示这两个表格，再展示请求/响应示例 JSON。

## 后端接口文件（无路由版）

- 健康检查：`/api/health.php`
- 登录：`/api/auth/login.php`
- 分类：
  - 列表/新增：`/api/categories/index.php`
  - 更新：`/api/categories/update.php`
  - 删除：`/api/categories/delete.php`
- API 文档：
  - 列表：`/api/apis/index.php`
  - 详情：`/api/apis/detail.php?id=1`
  - 新增：`/api/apis/create.php`
  - 更新：`/api/apis/update.php`
  - 删除：`/api/apis/delete.php`
  - 测试：`/api/apis/test.php`

## 目录

- `frontend/`：Uniapp 页面与 API 调用。
- `backend/public/`：实际对外 API 文件（按文件直连）。
- `backend/sql/schema.sql`：建表 + 初始化测试数据。

## 本地启动

### 1) 初始化数据库

```bash
mysql -u root -p < backend/sql/schema.sql
```

### 2) 配置数据库连接（环境变量）

`backend/config.php` 支持：
- `DB_HOST` / `DB_PORT` / `DB_NAME`
- `DB_USER` / `DB_PASS` / `DB_CHARSET`

Apache 可加：

```apacheconf
SetEnv DB_HOST 127.0.0.1
SetEnv DB_PORT 3306
SetEnv DB_NAME api_docs
SetEnv DB_USER api_docs_user
SetEnv DB_PASS 你的强密码
```

### 3) 本地启动 PHP

```bash
php -S 0.0.0.0:8000 -t backend/public
```

### 4) 运行 Uniapp 前端

将 `frontend` 导入 HBuilderX，运行到 H5。

如果首次运行提示 `vue could not be resolved` 或 `.vue` 解析错误（提示需要 plugin-vue），先在 `frontend` 目录安装依赖：

```bash
cd frontend
npm install
```

> 前端已提供 `package.json`（含 `vue`、`vite`、`@dcloudio/vite-plugin-uni`）。
> `vite.config.js` 已启用 `uni()` 插件并保留 `/api` 代理。
> 前端 API 基地址：`/api`（见 `frontend/common/api.js`）。


## HBuilderX 本地调试避免 404 / CORS

你反馈的问题本质是：
- `BASE_URL = '/api'` 在本地会请求本地开发服务器的 `/api`（若无代理就 404）
- 直接改成线上绝对地址会触发浏览器跨域限制

项目已新增 `frontend/vite.config.js`，配置了 H5 开发代理：
- 本地：`/api/*`
- 代理到：`https://apidocs.hahahaxinli.com/api/*`

这样你在 HBuilderX 里继续使用 `BASE_URL='/api'`，既不会本地 404，也不会 CORS。

> 注意：浏览器 Network 里看到请求地址是 `http://localhost:5173/api/...` 是正常现象，这表示请求先到本地 Vite，再由代理转发到线上接口。

如果你线上域名变了，只需要改 `frontend/vite.config.js` 里的 `target`。

## Apache 443 示例（前后端分离）

```apacheconf
<VirtualHost *:443>
    ServerName apidocs.hahahaxinli.com

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/apidocs.hahahaxinli.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/apidocs.hahahaxinli.com/privkey.pem

    # 前端
    DocumentRoot /var/www/apidocs/frontend-dist
    <Directory /var/www/apidocs/frontend-dist>
        AllowOverride All
        Require all granted
    </Directory>

    # 后端（按文件访问，不走路由分发）
    Alias /api/ /var/www/apidocs/backend/public/

    # 关键：父目录和目标目录都要可访问，否则常见 403
    <Directory /var/www>
        Require all granted
    </Directory>
    <Directory /var/www/apidocs>
        Require all granted
    </Directory>
    <Directory /var/www/apidocs/backend/public>
        AllowOverride All
        Options +FollowSymLinks
        Require all granted
        DirectoryIndex index.php
    </Directory>

    # 关键：确保 Authorization 头可被 PHP 读取
    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost/"
    </FilesMatch>
</VirtualHost>
```



> 另外请确认 `/api/.htaccess` 生效（`AllowOverride All`），该文件会统一处理 `OPTIONS` 预检并补齐 CORS 响应头。

## 测试账号

- 用户名：`admin`，密码：`wangzk123`
- 用户名：`admin1`，密码：`A7k9Q2mP`
- 用户名：`admin2`，密码：`N4v8T1xR`

如果你是历史库数据，登录仍失败（`password_verify` 返回 false），请在 MySQL 中重置管理员密码哈希：

```sql
UPDATE admins
SET password_hash = '$2y$12$nQQRSuWV/7ceDY//LfD8DeB6oByGgFwA3U6On5s.5w3hWPSbjvEZy'
WHERE username = 'admin';
```


### 部署后出现 403 Forbidden（重点排查）

按顺序执行下面检查：

1. 目录权限（必须有执行位 `x`）

```bash
chmod 755 /var/www
chmod 755 /var/www/apidocs
chmod 755 /var/www/apidocs/backend
chmod 755 /var/www/apidocs/backend/public
```

2. 文件属主（Apache 用户要可读）

```bash
chown -R www-data:www-data /var/www/apidocs/backend/public
```

3. 模块与站点重载

```bash
a2enmod rewrite headers alias ssl proxy_fcgi setenvif
a2ensite apidocs.conf
systemctl reload apache2
```

4. 直接访问最小接口确认不是前端问题

- `https://apidocs.hahahaxinli.com/api/health.php`
- `https://apidocs.hahahaxinli.com/api/options.php`

5. 看 Apache 错误日志定位拒绝原因

```bash
tail -n 200 /var/log/apache2/error.log
```

如果日志出现 `client denied by server configuration`，说明仍是 Directory/Alias 权限配置问题。
