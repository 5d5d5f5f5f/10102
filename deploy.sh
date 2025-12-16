#!/bin/bash
set -e

DB_ROOT_PASS="iptv123"
DB_NAME="iptv"
WEB_PORT=9001
PROJECT_DIR="/opt/iptv-auth"

echo "=== IPTV 接口管理系统 一键部署 ==="

# 安装 Docker 和 docker-compose
if ! command -v docker &>/dev/null; then
    curl -fsSL https://get.docker.com | bash
fi

if ! command -v docker-compose &>/dev/null; then
    curl -L "https://github.com/docker/compose/releases/download/v2.23.1/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
fi

mkdir -p $PROJECT_DIR
cd $PROJECT_DIR

# 克隆仓库
git clone https://github.com/5d5d5f5f5f/iptv-deploy.git temp
cp -r temp/* .
rm -rf temp

# 启动 MySQL
docker compose up -d mysql
echo "[*] 等待 MySQL 启动..."
until docker exec iptv-auth-mysql mysql -uroot -p$DB_ROOT_PASS -e "SELECT 1;" &>/dev/null; do
    echo "等待 MySQL..."
    sleep 2
done
echo "[*] MySQL 已启动"

# 初始化数据库
docker exec -i iptv-auth-mysql mysql -uroot -p$DB_ROOT_PASS $DB_NAME <<'SQL'
CREATE TABLE IF NOT EXISTS users(
id INT AUTO_INCREMENT PRIMARY KEY,
pwd VARCHAR(6) UNIQUE NOT NULL,
url TEXT NOT NULL,
remark VARCHAR(255),
start_date DATE,
expire_date DATE,
status TINYINT DEFAULT 1
);
CREATE TABLE IF NOT EXISTS ip_log(
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT,
ip VARCHAR(50),
access_time DATETIME
);
SQL

# 启动 PHP + Nginx
docker compose up -d php nginx

echo "======================================"
echo "部署完成"
echo "后台管理: http://VPS_IP:$WEB_PORT/admin.php?pwd=admin123"
echo "短链接访问示例: http://VPS_IP:$WEB_PORT/123456"
echo "API接口示例: http://VPS_IP:$WEB_PORT/api.php?pwd=123456"
echo "======================================"
