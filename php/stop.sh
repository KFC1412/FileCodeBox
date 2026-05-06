#!/bin/bash

echo "FileCodeBox PHP 版本停止脚本"
echo "=============================="

cd "$(dirname "$0")" || exit 1

docker-compose down

echo "服务已停止"
