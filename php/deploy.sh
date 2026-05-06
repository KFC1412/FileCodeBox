#!/bin/bash

echo "FileCodeBox Apache 部署脚本"
echo "=============================="

PROJECT_ROOT="$(cd "$(dirname "$0")" && pwd)"
PUBLIC_DIR="$PROJECT_ROOT/public"
DATA_DIR="$PROJECT_ROOT/data"

echo "项目目录: $PROJECT_ROOT"
echo "Web根目录: $PUBLIC_DIR"
echo "数据目录: $DATA_DIR"

mkdir -p "$PUBLIC_DIR/api" "$PUBLIC_DIR/core" "$PUBLIC_DIR/models" "$DATA_DIR"

echo ""
echo "正在复制 PHP 文件..."
cp "$PROJECT_ROOT/php/public/index.php" "$PUBLIC_DIR/"
cp "$PROJECT_ROOT/php/public/.htaccess" "$PUBLIC_DIR/"
cp "$PROJECT_ROOT/php/config.php" "$PUBLIC_DIR/"

cp -r "$PROJECT_ROOT/php/api/"* "$PUBLIC_DIR/api/"
cp -r "$PROJECT_ROOT/php/core/"* "$PUBLIC_DIR/core/"
cp -r "$PROJECT_ROOT/php/models/"* "$PUBLIC_DIR/models/"

echo ""
echo "创建前端资源软链接..."
if [ -d "$PROJECT_ROOT/fcb-fronted/dist" ]; then
    if [ ! -L "$PUBLIC_DIR/assets" ]; then
        ln -sf "$PROJECT_ROOT/fcb-fronted/dist/assets" "$PUBLIC_DIR/assets"
    fi
else
    echo "警告: 前端 dist 目录不存在，跳过 assets 链接"
fi

echo ""
echo "复制 Apache 虚拟主机配置..."
cp "$PROJECT_ROOT/php/apache-vhost-template.conf" "$PROJECT_ROOT/filecodebox-vhost.conf"
echo "配置已复制到: $PROJECT_ROOT/filecodebox-vhost.conf"
echo "请根据实际情况修改 ServerName 后部署"

echo ""
echo "部署完成！"
echo ""
echo "下一步："
echo "1. 将 filecodebox-vhost.conf 复制到 Apache 配置目录"
echo "   - Debian/Ubuntu: sudo cp filecodebox-vhost.conf /etc/apache2/sites-available/"
echo "   - CentOS/RHEL: sudo cp filecodebox-vhost.conf /etc/httpd/conf.d/"
echo ""
echo "2. 修改 filecodebox-vhost.conf 中的 ServerName 为你的域名"
echo ""
echo "3. 启用站点并重启 Apache"
echo "   - Debian/Ubuntu: sudo a2ensite filecodebox-vhost && sudo a2enmod rewrite && sudo systemctl restart apache2"
echo "   - CentOS/RHEL: sudo systemctl restart httpd"
echo ""
echo "4. 访问你的域名"
echo ""
echo "默认管理员密码: FileCodeBox2023"
