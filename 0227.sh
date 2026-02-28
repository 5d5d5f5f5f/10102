#!/bin/bash
set -e

APP_DIR=/root/mytv-docker
PORT=8100
CONTAINER=mytv

echo "=== 创建目录 ==="
mkdir -p "$APP_DIR"
cd "$APP_DIR"

echo "=== 写入 PHP 文件 ==="
cat > mytv.php <<'PHP'
<?php
$request_url = isset($_GET['url']) ? urldecode($_GET['url']) : '';
if (empty($request_url)) die('missing url');

$parsed = parse_url($request_url);

$ch = curl_init($request_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);
$body = curl_exec($ch);
curl_close($ch);

if (strpos(ltrim($body), '#EXTM3U') === 0) {
    $base = $parsed['scheme'].'://'.$parsed['host'];
    $dir  = dirname($parsed['path']).'/';
    $out = [];
    foreach (preg_split("/\r?\n/", $body) as $line) {
        if ($line === '' || $line[0] === '#') {
            $out[] = $line;
            continue;
        }
        if (!preg_match('#^https?://#', $line)) {
            $line = $base.$dir.$line;
        }
        $out[] = 'mytv.php?url='.urlencode($line);
    }
    header('Content-Type: application/vnd.apple.mpegurl');
    echo implode("\n", $out);
    exit;
}

echo $body;
PHP

echo "=== 写入 Dockerfile ==="
cat > Dockerfile <<'DOCKER'
FROM php:8.1-apache
COPY mytv.php /var/www/html/mytv.php
EXPOSE 80
DOCKER

echo "=== 构建镜像 ==="
docker build -t mytv-php .

echo "=== 启动容器 ==="
docker rm -f "$CONTAINER" 2>/dev/null || true
docker run -d --name "$CONTAINER" --restart=always -p "$PORT":80 mytv-php

echo "=== 部署完成 ==="
echo "示例：http://服务器IP:$PORT/mytv.php?url=测试地址"
