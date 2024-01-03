# chessgame

Steps to install the project

- Composer install

- Update .env file

- php bin/console make:migration

- php bin/console doctrine:migrations:migrate

- symfony server:start

- In another terminal window : php bin/console run:websocket-server
