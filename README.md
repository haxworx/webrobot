# Web Robot

A single-threaded web robot.

## Requirements

Python3 and the following:

```
python3 -m pip install mysql-connector-python
python3 -m pip install paho-mqtt
python3 -m pip install boto3
```

## Web Interface

The browser-based user interface is not mandatory for
operation, but it makes life much, much easier.

Install PHP and the following:

```
cd web
composer require twig/twig
composer require php-mqtt/client
composer require aws/aws-sdk-php
```
See scripts/web.sh for PHP built-in web server.
