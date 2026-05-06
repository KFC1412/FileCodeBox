# 请将此文件复制到项目根目录
# 然后将所有 PHP 文件移动到 public 目录

# 推荐目录结构
# project/
# ├── public/           # Web 根目录（Apache DocumentRoot）
# │   ├── index.php
# │   ├── .htaccess
# │   ├── api/
# │   ├── core/
# │   ├── models/
# │   └── assets/       # 可选：软链接到 fcb-fronted/dist/assets
# ├── fcb-fronted/       # 前端构建文件
# │   └── dist/
# ├── data/              # 数据存储（上传文件、数据库）
# └── config.php         # 配置文件（如果不在 public 内）

# 快速设置脚本
# -----------------------------
# 在项目根目录执行：

# mkdir -p public
# cp php/index.php public/
# cp -r php/api public/
# cp -r php/core public/
# cp -r php/models public/
# cp php/public/.htaccess public/
# cp php/config.php public/
# mkdir -p data
