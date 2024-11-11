# PHPイメージを取得
FROM php:8.1-apache

# 必要なPHPの拡張機能をインストール
RUN docker-php-ext-install pdo pdo_mysql

# Apacheのドキュメントルートを設定
WORKDIR /var/www/html

# プロジェクトファイルをコンテナにコピー
COPY . /var/www/html
