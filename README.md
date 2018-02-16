proxy-balancer
==============

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
2. Создайте proxy-balancer/etc/config.yml по следующему шаблону:
```
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
4. Запустите ```php ./proxy-balancer/bin/run.php```

Использование
-------------
Клиент для данного сервиса https://github.com/avallac/proxy-balancer-client. Пример находится в файлу ```proxy-balancer/bin/client.php```
