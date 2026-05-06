# FileCodeBox PHP 版本部署指南

## Apache 虚拟主机部署

### 1. 准备工作

确保您的系统已安装以下软件：
- Apache 2.4+
- PHP 7.4+ (推荐 8.0+)
- PHP 扩展：pdo_sqlite, zip

检查 PHP 模块：
```bash
php -m | grep -E 'pdo_sqlite|zip|json|mbstring'
```

### 2. 文件结构

将项目文件按以下结构部署：
```
/var/www/filecodebox/
├── php/                  # PHP 后端
│   ├── index.php
│   ├── config.php
│   ├── database.php
│   ├── api/
│   ├── core/
│   └── models/
├── fcb-fronted/          # 前端
│   └── dist/
└── data/                 # 数据目录（需可写）
```

### 3. 权限设置

```bash
# 设置目录权限
chown -R www-data:www-data /var/www/filecodebox
chmod -R 755 /var/www/filecodebox
chmod -R 775 /var/www/filecodebox/data  # 数据目录需要可写
```

### 4. 配置 Apache

```bash
# 复制配置文件
sudo cp /path/to/project/php/apache.conf /etc/apache2/sites-available/filecodebox.conf

# 启用所需的 Apache 模块
sudo a2enmod rewrite
sudo a2enmod ssl  # 如需 HTTPS
sudo a2enmod deflate

# 启用站点
sudo a2ensite filecodebox.conf

# 禁用默认站点（可选）
sudo a2dissite 000-default.conf

# 重载 Apache
sudo systemctl reload apache2
# 或
sudo service apache2 reload
```

### 5. 修改域名配置

编辑 `/etc/apache2/sites-available/filecodebox.conf`：
- 将 `ServerName filecodebox.local` 改为您的域名
- 如需 HTTPS，配置 SSL 证书路径

### 6. 配置 hosts 文件（测试环境）

如果是本地测试，修改 `/etc/hosts`：
```
127.0.0.1 filecodebox.local
```

### 7. 验证部署

访问 http://filecodebox.local 查看是否正常显示首页。

## FAQ

### 页面显示 404
确保 Apache 的 mod_rewrite 模块已启用，且 .htaccess 文件可读取。

### 文件上传失败
检查 PHP 配置中的 `upload_max_filesize` 和 `post_max_size`。

### 数据库错误
确保 data 目录有正确的写入权限。
