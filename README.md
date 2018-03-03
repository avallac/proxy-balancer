proxy-balancer [![Build Status](https://img.shields.io/travis/avallac/proxy-balancer.svg)](https://travis-ci.org/avallac/proxy-balancer)
==============

[![Latest Stable Version](https://img.shields.io/packagist/v/avallac/proxy-balancer.svg)](https://packagist.org/packages/avallac/proxy-balancer)


Описание
--------
Демон, написанный на react-PHP, для балансировки запросов между прокси серверами в распределенной системе. 

Особенности:
 * Поддерживает множество сервисов, каждый со своим таймаутом
 * Предлагает самый доступный прокси, на основе статистики времени ответа от клиента.
 * Клиент может пожаловаться на прокси (не работает, заблокировано сайтом), отключив ее на 60 минут.
 * Статистика сохраняется на диск каждые 60 минут, автоматически будет загружена при старте приложения.
 * Авторизация через Basic access authentication
 
Установка
---------
1. Запустите ```composer create-project avallac/proxy-balancer```
2. При необходимости отредактируйте proxy-balancer/etc/config.yml:
```
listenPort: Номер слушающего порта
debug: Отладка
auth:
    username: <username>
    password: <password>
service:
    <service 1>: <colddown sec>
    <service 2>: <colddown sec>
```
3. Создайте proxy-balancer/etc/proxy.list. Каждая прокси на новой строуке, по следующему шаблону:
```
<username>:<password>@<ip/host>:<port>
<username>:<password>@<ip/host>:<port>
<username>:<password>@<ip/host>:<port>
<username>:<password>@<ip/host>:<port>
```
4. Запустите ```php ./proxy-balancer/bin/proxy-balancer.php```

Методы
------
1. GET / в ответ возвращать JSON с количеством доступных прокси для каждого сервиса.
2. GET /status в ответ возвращать JSON с временем с момента запуска сервиса, в секундах.
3. GET /debug
4. GET /get/{service}
5. POST /report/{service}
6. POST /complaint/{service}

Использование
-------------
Клиент для данного сервиса https://github.com/avallac/proxy-balancer-client. Пример находится в файлу ```proxy-balancer/bin/client.php```
