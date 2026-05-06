#!/bin/bash

echo "==========================================="
echo "FilePhpBox (FPB) 一键部署脚本"
echo "==========================================="

PROJECT_DIR=$(cd "$(dirname "$0")" && pwd)
TARGET_DIR="/var/www/filephpbox"

echo "项目目录: $PROJECT_DIR"
echo "目标目录: $TARGET_DIR"

echo ""
echo "1. 创建目标目录..."
mkdir -p "$TARGET_DIR"

echo ""
echo "2. 复制核心文件..."
cp "$PROJECT_DIR/index.php" "$TARGET_DIR/"
cp "$PROJECT_DIR/config.php" "$TARGET_DIR/"
cp "$PROJECT_DIR/check.php" "$TARGET_DIR/"
cp "$PROJECT_DIR/.htaccess" "$TARGET_DIR/"

mkdir -p "$TARGET_DIR/api" "$TARGET_DIR/core" "$TARGET_DIR/models" "$TARGET_DIR/data"

cp "$PROJECT_DIR/api/admin.php" "$TARGET_DIR/api/"
cp "$PROJECT_DIR/api/share.php" "$TARGET_DIR/api/"

cp "$PROJECT_DIR/core/JSONStorage.php" "$TARGET_DIR/core/"
cp "$PROJECT_DIR/core/RateLimit.php" "$TARGET_DIR/core/"
cp "$PROJECT_DIR/core/Response.php" "$TARGET_DIR/core/"
cp "$PROJECT_DIR/core/Storage.php" "$TARGET_DIR/core/"
cp "$PROJECT_DIR/core/Utils.php" "$TARGET_DIR/core/"

cp "$PROJECT_DIR/models/FileCodes.php" "$TARGET_DIR/models/"
cp "$PROJECT_DIR/models/KeyValue.php" "$TARGET_DIR/models/"

echo ""
echo "3. 设置前端资源..."
if [ -d "$PROJECT_DIR/../fcb-fronted/dist" ]; then
    echo "找到前端构建目录，复制 assets..."
    cp -r "$PROJECT_DIR/../fcb-fronted/dist/assets" "$TARGET_DIR/"
    cp "$PROJECT_DIR/../fcb-fronted/dist/index.html" "$TARGET_DIR/"
else
    echo "警告: 前端 dist 目录不存在"
    echo "请先构建前端: cd ../fcb-fronted && npm run build"
fi

echo ""
echo "4. 设置权限..."
chown -R www-data:www-data "$TARGET_DIR"
chmod -R 755 "$TARGET_DIR"
chmod -R 777 "$TARGET_DIR/data"

echo ""
echo "==========================================="
echo "部署完成!"
echo "==========================================="
echo ""
echo "访问地址: http://your-domain.com"
echo "管理后台: http://your-domain.com/#/admin"
echo "管理员密码: zxc123456"
echo ""
echo "Apache 配置参考:"
echo "<VirtualHost *:80>"
echo "    ServerName your-domain.com"
echo "    DocumentRoot /var/www/filephpbox"
echo "    <Directory /var/www/filephpbox>"
echo "        AllowOverride All"
echo "        Require all granted"
echo "    </Directory>"
echo "</VirtualHost>"
