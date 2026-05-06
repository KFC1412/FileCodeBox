#!/bin/bash

echo "==========================================="
echo "FileCodeBox 停止脚本"
echo "==========================================="

PROJECT_ROOT="$(cd "$(dirname "$0")" && pwd)"

echo "清理 Apache 虚拟主机配置..."
if [ -f "$PROJECT_ROOT/filecodebox.conf" ]; then
    echo "移除配置文件: $PROJECT_ROOT/filecodebox.conf"
    rm -f "$PROJECT_ROOT/filecodebox.conf"
fi

echo ""
echo "停止完成!"
echo "如需完全卸载，请手动删除项目文件"
