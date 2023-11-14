# ディレクトリに入る
cd gpt-works-biz

# 必要ファイルのセットアップ
npm install
npm run dev
composer install

# データベース設定
vim .env

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=gpt-works-biz
DB_USERNAME=root
DB_PASSWORD=***

# DB migration
php artisan migrate

# サーバ起動
php artisan serve --port=18000