# Web Robot

A single-threaded web robot.

The crawer is written in Python with the management user-
interface written with Symfony in PHP.

## Docker.

The project is runnable with docker compose.

To bring up the application, run:

```
docker-compose build
docker-compose up
```

## Requirements

Python3 and the following:

```
python3 -m pip install mysql-connector-python
python3 -m pip install paho-mqtt
python3 -m pip install boto3
```

