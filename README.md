# Ports Used

- Websocket: 8086

- Adminer (ver tabelas): 8080

- Laravel: 8085

# Package Manager

Afonso

  - Não consegui meter a funcionar com bun, e com o node a partir da versao 23.0.0 dá erro este projeto. Se quiserem podem experimentar meter localmente com o bun

# Start up

## Run laravel:

> sail == laravel/vendor/bin/sail

1. Open docker Desktop

2. Copy `.env.example` and rename it to `.env`

3. ./laravel$ composer update

4. ./laravel$ sail up -d

5. ./laravel$ sail artisan migrate:fresh

6. ./laravel$ sail artisan db:seed

7. Choose [0] en_US

8. ./laravel$ sail artisan storage:link

## Run vue:

1. ./vue$ npm install

2. Copy `.env.example` and rename it to `.env`

3. ./vue$ npm run dev

## Run websocket:

1. ./websockets$ npm install

2. ./websockets$ node index.js