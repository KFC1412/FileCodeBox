#!/bin/bash

echo "==========================================="
echo "FileCodeBox PHP 部署脚本 (Apache 虚拟主机)"
echo "==========================================="

PROJECT_ROOT="$(cd "$(dirname "$0")" && pwd)"

echo ""
echo "步骤 1: 复制文件到项目根目录"
echo "-------------------------------------------"

echo "复制 PHP 核心文件..."
cp "$PROJECT_ROOT/php/public/index.php" "$PROJECT_ROOT/"
cp "$PROJECT_ROOT/php/public/.htaccess" "$PROJECT_ROOT/"
cp "$PROJECT_ROOT/php/public/config.php" "$PROJECT_ROOT/"
cp "$PROJECT_ROOT/php/database.php" "$PROJECT_ROOT/"

mkdir -p "$PROJECT_ROOT/api" "$PROJECT_ROOT/core" "$PROJECT_ROOT/models"
cp -r "$PROJECT_ROOT/php/api/"* "$PROJECT_ROOT/api/"
cp -r "$PROJECT_ROOT/php/core/"* "$PROJECT_ROOT/core/"
cp -r "$PROJECT_ROOT/php/models/"* "$PROJECT_ROOT/models/"

mkdir -p "$PROJECT_ROOT/data"

echo ""
echo "步骤 2: 设置前端资源"
echo "-------------------------------------------"
if [ -d "$PROJECT_ROOT/fcb-fronted/dist" ]; then
    echo "前端 dist 目录已找到，创建 assets 软链接..."
    if [ ! -L "$PROJECT_ROOT/assets" ]; then
        ln -sf "$PROJECT_ROOT/fcb-fronted/dist/assets" "$PROJECT_ROOT/assets"
    fi
    if [ ! -f "$PROJECT_ROOT/index.html" ] && [ -f "$PROJECT_ROOT/fcb-fronted/dist/index.html" ]; then
        ln -sf "$PROJECT_ROOT/fcb-fronted/dist/index.html" "$PROJECT_ROOT/index.html"
    fi
    echo "软链接创建完成"
else
    echo "警告: 前端 dist 目录不存在 ($PROJECT_ROOT/fcb-fronted/dist)"
    echo "请先构建前端: cd fcb-fronted && npm run build"
fi

echo ""
echo "步骤 3: 设置权限"
echo "-------------------------------------------"
chmod -R 755 "$PROJECT_ROOT"
chmod -R 777 "$PROJECT_ROOT/data"

echo ""
echo "步骤 4: 复制 Apache 虚拟主机配置"
echo "-------------------------------------------"
cp "$PROJECT_ROOT/php/apache-vhost-template.conf" "$PROJECT_ROOT/filecodebox.conf"
echo "配置文件已创建: $PROJECT_ROOT/filecodebox.conf"

echo ""
echo "==========================================="
echo "部署完成!"
echo "==========================================="
echo ""
echo "下一步操作:"
echo ""
echo "1. 编辑 Apache 虚拟主机配置"
echo "   编辑 $PROJECT_ROOT/filecodebox.conf"
echo "   将 'your-domain.com' 替换为你的域名"
echo ""
echo "2. 部署虚拟主机配置"
echo "   Debian/Ubuntu:"
echo "     sudo cp $PROJECT_ROOT/filecodebox.conf /etc/apache2/sites-available/"
echo "     sudo a2ensite filecodebox"
echo "     sudo a2enmod rewrite"
echo "     sudo systemctl restart apache2"
echo ""
echo "   CentOS/RHEL:"
echo "     sudo cp $PROJECT_ROOT/filecodebox.conf /etc/httpd/conf.d/"
echo "     sudo systemctl restart httpd"
echo ""
echo "3. 访问你的网站"
echo ""
echo "==========================================="
echo "默认管理员密码: FileCodeBox2023"
echo "==========================================="
