<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220512120623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE content_types (content_id INT AUTO_INCREMENT NOT NULL, content_type VARCHAR(128) DEFAULT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(content_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crawl_allowed_content (id INT AUTO_INCREMENT NOT NULL, bot_id INT DEFAULT NULL, content_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crawl_data (id INT AUTO_INCREMENT NOT NULL, bot_id INT DEFAULT NULL, srv_time_stamp DATETIME DEFAULT CURRENT_TIMESTAMP, scan_date DATE DEFAULT NULL, scan_time_stamp DATETIME DEFAULT NULL, scan_time_zone VARCHAR(64) DEFAULT NULL, domain VARCHAR(253) DEFAULT NULL, scheme VARCHAR(32) DEFAULT NULL, link_source VARCHAR(4096) DEFAULT NULL, modified DATETIME DEFAULT NULL, url VARCHAR(4096) DEFAULT NULL, status_code INT DEFAULT NULL, path TEXT DEFAULT NULL, query TEXT DEFAULT NULL, content_type VARCHAR(255) DEFAULT NULL, metadata TEXT DEFAULT NULL, checksum VARCHAR(32) DEFAULT NULL, encoding VARCHAR(32) DEFAULT NULL, length INT DEFAULT NULL, data MEDIUMBLOB DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crawl_errors (id INT AUTO_INCREMENT NOT NULL, bot_id INT DEFAULT NULL, srv_time_stamp DATETIME DEFAULT CURRENT_TIMESTAMP, scan_date DATE DEFAULT NULL, scan_time_stamp DATETIME DEFAULT NULL, scan_time_zone VARCHAR(64) DEFAULT NULL, status_code INT DEFAULT NULL, url VARCHAR(4096) DEFAULT NULL, link_source VARCHAR(4096) DEFAULT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crawl_log (id INT AUTO_INCREMENT NOT NULL, bot_id INT DEFAULT NULL, srv_time_stamp DATETIME DEFAULT CURRENT_TIMESTAMP, scan_date DATE DEFAULT NULL, scan_time_stamp DATETIME DEFAULT NULL, crawler_name VARCHAR(32) DEFAULT NULL, hostname VARCHAR(128) DEFAULT NULL, ip_address VARCHAR(128) DEFAULT NULL, level_number INT DEFAULT NULL, level_name VARCHAR(32) DEFAULT NULL, message TEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crawl_settings (bot_id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, scheme VARCHAR(32) DEFAULT NULL, address VARCHAR(260) DEFAULT NULL, domain VARCHAR(253) DEFAULT NULL, agent VARCHAR(255) DEFAULT NULL, delay DOUBLE PRECISION DEFAULT NULL, ignore_query TINYINT(1) DEFAULT NULL, import_sitemaps TINYINT(1) DEFAULT NULL, retry_max INT DEFAULT NULL, start_time TIME DEFAULT NULL, end_time DATETIME DEFAULT NULL, is_running TINYINT(1) NOT NULL, has_error TINYINT(1) NOT NULL, PRIMARY KEY(bot_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE global_settings (id INT AUTO_INCREMENT NOT NULL, time_stamp DATETIME DEFAULT NULL, in_use TINYINT(1) DEFAULT NULL, max_crawlers INT DEFAULT NULL, debug TINYINT(1) DEFAULT NULL, docker_image VARCHAR(128) DEFAULT NULL, mqtt_host VARCHAR(128) DEFAULT NULL, mqtt_port INT DEFAULT NULL, mqtt_topic VARCHAR(8192) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE content_types');
        $this->addSql('DROP TABLE crawl_allowed_content');
        $this->addSql('DROP TABLE crawl_data');
        $this->addSql('DROP TABLE crawl_errors');
        $this->addSql('DROP TABLE crawl_log');
        $this->addSql('DROP TABLE crawl_settings');
        $this->addSql('DROP TABLE global_settings');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
