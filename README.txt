Websocket: localhost:8086
Adminer (ver tabelas): localhost:8080
Laravel: localhost:8085

----------------------------------------------------

Login BD:
System: MySQL
Server: mysql
Username: sail
Password: password
Database: laravel

----------------------------------------------------

Não consegui meter a funcionar com bun, e com o node a partir da versao 23.0.0 dá erro este projeto
Se quiserem podem experimentar meter localmente com o bun

Para correr vue:
/vue$ npm install
/vue$ npm run dev

----------------------------------------------------

Para correr websocket:
/websockets$ npm install
/websockets$ node index.js

----------------------------------------------------

Para correr laravel:
/laravel$ composer update

/laravel$ sail up -d     (com o docker desktop aberto)

Podem ter que fazer também:
/laravel$ sail artisan migrate:fresh

Para meter valores na base dados:
/laravel$ sail artisan db:seed
[0] en_US

/laravel$ sail artisan storage:link

