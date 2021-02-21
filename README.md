# Установка

1. Создайте свой репозиторий из [шаблона](https://docs.github.com/en/github/creating-cloning-and-archiving-repositories/creating-a-repository-from-a-template#creating-a-repository-from-a-template)
1. Склонируйте свой [репозиторий](https://docs.github.com/en/github/creating-cloning-and-archiving-repositories/cloning-a-repository)
1. Установите [docker](https://docs.docker.com/engine/install/ubuntu/) и [docker-compose](https://docs.docker.com/compose/install/);
1. В директории заготовки выполните команду `docker-compose up --build -d`;
1. Зайдите в контейнер `workspace` с помощью команды `docker-compose exec workspace sh`;
1. Установите зависимости командой `composer install` ;

Сервис будет доступен по адресу http://localhost/employee-schedule.

Консольный интерфес будет доступен в контейнере `docker-compose exec workspace sh`, `php artisan`.

Запуск тестов возможен в контейнере `docker-compose exec workspace sh`, `./vendor/bin/phpunit`.

Вы можете менять в заготовке все что Вам захочется.
