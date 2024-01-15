# chessgame

Steps to install the project

- Composer install

- Update .env file

- php bin/console doctrine:database:create

- php bin/console make:migration

- php bin/console doctrine:migrations:migrate

- symfony server:start

- In another terminal window : php bin/console run:websocket-server

- To compile css, install sass (apt install sass) and run : sass --update public/assets/css/chess.scss:public/assets/css/chess.css --style compressed
