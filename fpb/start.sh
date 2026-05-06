#!/bin/bash

echo "FileCodeBox PHP 版本安装脚本"
echo "=============================="

# 检查Docker是否安装
if ! command -v docker &> /dev/null; then
    echo "错误: Docker 未安装"
    exit 1
fi

# 检查docker-compose是否安装
if ! command -v docker-compose &> /dev/null; then
    echo "错误: docker-compose 未安装"
    exit 1
fi

# 检查PHP文件是否存在
if [ ! -d "$(dirname "$0")/php" ]; then
    echo "错误: php 目录不存在"
    exit 1
fi

# 启动服务
echo "正在启动服务..."
cd "$(dirname "$0")" || exit 1
docker-compose up -d

echo ""
echo "服务已启动!"
echo "访问 http://localhost:8080 使用"
echo ""
echo "管理后台: http://localhost:8080/#/admin"
echo "默认管理员密码: FileCodeBox2023"
