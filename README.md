安裝環境設定:

.env檔DB,redis設定

DB_CONNECTION=mysql

DB_HOST=mysql_db

DB_PORT=3306

DB_DATABASE=projectA

REDIS_CLIENT=predis

REDIS_HOST=redis_cache

REDIS_PASSWORD=null

REDIS_PORT=6379

建立container

docker-compose up -d --build

進入container laravel_app 安裝套件,執行migration:

docker exec -it laravel_app bash

cd src

composer install

php artisan key:generate

php artisan migrate

--註冊登入api說明--

註冊:

參數:

string name 名稱

string email

string password 密碼6碼

POST localhost:8000/api/users/register

登入:

登入後取得token

POST localhost:8000/api/users/login

--分派任務api說明--

取得使用者的任務:

GET localhost:8000/api/tasks

建立任務

參數:

 string title 標題

 string description 內容描述

 string status 狀態(pending,in-progress,completed)

 int assignee_id 分派user編號

POST localhost:8000/api/tasks

編輯任務

參數:

 string title 標題
 
 string description 內容描述
 
 string status 狀態(pending,in-progress,completed)
 
 int assignee_id 分派user編號
 
PUT localhost:8000/api/tasks/{id}

刪除任務

DELETE localhost:8000/tasks/{id}

