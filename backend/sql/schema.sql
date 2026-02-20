CREATE DATABASE IF NOT EXISTS api_docs DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE api_docs;

DROP TABLE IF EXISTS admin_sessions;
DROP TABLE IF EXISTS api_items;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    expired_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token_expired (token, expired_at)
);

CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    sort_order INT DEFAULT 99,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE api_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    method VARCHAR(10) NOT NULL,
    path VARCHAR(255) NOT NULL,
    description TEXT,
    request_params TEXT,
    response_fields TEXT,
    request_example TEXT,
    response_example TEXT,
    test_url VARCHAR(500) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category_id (category_id)
);

INSERT INTO admins(username, password_hash) VALUES
('admin', '$2y$12$nQQRSuWV/7ceDY//LfD8DeB6oByGgFwA3U6On5s.5w3hWPSbjvEZy'),
('admin1', '$2y$12$/mWRWy8gWG6L4035oefZeuZVn.Z1jC48Q8SgLtm/DgESh5X.YrhOW'),
('admin2', '$2y$12$7nH38nL2hFv3vldYmB74FuAGPEu8FWFSDmoduP1GCvlWM7ARm76Ly');

INSERT INTO categories(name, sort_order) VALUES
('用户模块', 1),
('订单模块', 2),
('系统模块', 3);

INSERT INTO api_items(category_id, name, method, path, description, request_params, response_fields, request_example, response_example, test_url) VALUES
(1, '获取用户详情', 'GET', '/v1/users/{id}', '根据用户ID获取用户资料。', '[{"name":"id","type":"number","required":true,"description":"用户ID"}]', '[{"name":"code","type":"number","description":"状态码"},{"name":"data.id","type":"number","description":"用户ID"},{"name":"data.name","type":"string","description":"用户姓名"}]', '{"id":1}', '{"code":0,"data":{"id":1,"name":"张三","email":"zhangsan@example.com"}}', ''),
(1, '创建用户', 'POST', '/v1/users', '创建新用户账号。', '[{"name":"name","type":"string","required":true,"description":"姓名"},{"name":"email","type":"string","required":true,"description":"邮箱"}]', '[{"name":"code","type":"number","description":"状态码"},{"name":"message","type":"string","description":"提示信息"},{"name":"data.id","type":"number","description":"新用户ID"}]', '{"name":"李四","email":"lisi@example.com"}', '{"code":0,"message":"创建成功","data":{"id":2}}', 'https://httpbin.org/post'),
(2, '查询订单列表', 'GET', '/v1/orders', '分页查询订单。', '[{"name":"page","type":"number","required":false,"description":"页码"},{"name":"pageSize","type":"number","required":false,"description":"分页大小"}]', '[{"name":"code","type":"number","description":"状态码"},{"name":"data.list","type":"array","description":"订单列表"},{"name":"data.total","type":"number","description":"总数"}]', '{"page":1,"pageSize":20}', '{"code":0,"data":{"list":[{"id":1001,"amount":29.9}],"total":1}}', 'https://httpbin.org/get'),
(3, '健康检查', 'GET', '/v1/health', '服务健康状态检查。', '[]', '[{"name":"code","type":"number","description":"状态码"},{"name":"status","type":"string","description":"健康状态"}]', '{}', '{"code":0,"status":"ok"}', 'https://httpbin.org/get');
