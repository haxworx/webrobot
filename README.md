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
composer update
```
See scripts/web.sh for PHP built-in web server.

## Installation (Rough Guide)

 * Install python3 and PHP dependencies.
 * Install MySQL database (webrobot.sql).
 * Install docker.io
 * Set up MYSQL database user/pass/host.
 * Configure config.ini for database credentials. Either locally or AWS Secrets Manager.
 * As a user with sudo root credentials run 'sudo sh install.sh' to create docker image.
 * Configure Apache/NGINX to serve web/ content.
 * Launch via the web interface.
