INSERT INTO `content_types` (`content_type`, `description`)
VALUES
    ('text/plain', 'Text'),
    ('text/html', 'HTML'),
    ('text/css', 'CSS'),
    ('application/xml', 'Application XML'),
    ('text/xml', 'Text XML'),
    ('application/json', 'Application JSON');

INSERT INTO `global_settings`
    (`time_stamp`, `in_use`, `max_crawlers`, `debug`, `mqtt_host`, `mqtt_port`, `mqtt_topic`)
    VALUES
    (NOW(), true, 5, true, 'mosquitto', 1883, 'testing');

