## FilePhpBox (FPB) - 上传即部署

### 项目简介

FilePhpBox (简称 FPB) 是一个基于 PHP + JSON 的轻量级文件分享系统，无需数据库，上传即部署。

### 部署方式

**方式一：上传即部署**
1. 将整个 `php/` 目录上传到服务器
2. 设置 Apache/Nginx DocumentRoot 指向该目录
3. 确保 `data/` 目录有写权限
4. 访问网站即可使用

**方式二：脚本部署**
```bash
cd php
./deploy.sh
```

### 环境要求

| 项目 | 要求 |
|------|------|
| PHP | 7.4+ |
| 扩展 | JSON |
| 服务器 | Apache/Nginx |

### 文件结构

```
php/                          # DocumentRoot
├── index.php                 # 主入口
├── config.php                # 配置文件
├── check.php                 # 部署检测
├── .htaccess                 # Apache 重写规则
├── index.html                # 前端页面
├── assets/                   # 前端资源
├── api/                      # API 接口
├── core/                     # 核心类
├── models/                   # 数据模型
└── data/                     # 数据存储 (JSON)
    ├── file_codes.json       # 文件信息
    ├── key_value.json        # 配置信息
    └── share/data/           # 上传文件
```

### 默认配置

- **管理员密码**: `zxc123456`
- **上传限制**: 10MB
- **默认过期时间**: 1天

### 部署检测

上传后访问 `/check.php` 可检测部署状态。

### 快速配置

修改 `config.php` 中的配置项：

```php
'admin_token' => 'your-password',  // 管理员密码
'uploadSize' => 1024 * 1024 * 20,  // 上传大小限制(字节)
'openUpload' => 1,                 // 允许游客上传
```

### 特性

- 🚀 上传即部署，零配置
- 📦 纯 PHP + JSON，无需数据库
- 🔐 支持密码保护
- ⏰ 支持多种过期方式
- 📱 响应式设计
