FROM php:8.4-fpm

# 必要なシステムパッケージをインストール
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip \
    && apt-get clean

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリを設定
WORKDIR /app

# アプリケーションファイルを先にコピー（★ここを変更）
COPY . .

# Composerの依存関係をインストール
RUN composer install --optimize-autoloader --no-dev --no-interaction

# npmパッケージをインストールしてビルド
RUN npm install && npm run build

# 設定をキャッシュ
RUN php artisan config:cache

# ポートを公開
EXPOSE 8080

# アプリケーションを起動
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}