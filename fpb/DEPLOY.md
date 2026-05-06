## FilePhpBox (FPB)

基于 PHP + JSON 的轻量级文件分享系统，无需数据库，上传即用。

### 使用方法

1. 将 `fpb/` 目录上传到服务器
2. 设置 `data/` 目录可写权限
3. 访问网站即可使用

### 环境要求

- PHP 7.4+
- Apache/Nginx (支持 URL 重写)

### 文件结构

```
fpb/
├── index.php           # 主入口
├── config.php          # 配置文件
├── check.php           # 部署检测
├── .htaccess           # Apache 重写规则
├── index.html          # 前端页面
├── assets/             # 前端资源
├── api/                # API 接口
├── core/               # 核心类
├── models/             # 数据模型
└── data/               # 数据存储
    ├── file_codes.json # 文件信息
    ├── key_value.json  # 配置信息
    └── share/data/     # 上传文件
```

### 默认配置

- 管理员密码: `zxc123456`
- 上传限制: 10MB

### 部署检测

访问 `/check.php` 检测环境配置。
