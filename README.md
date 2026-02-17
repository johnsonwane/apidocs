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

## 启动步骤

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

> 默认后端地址在 `frontend/common/api.js`：`http://localhost:8000/api`

## 测试账号

- 用户名：`admin`
- 密码：`admin123`
