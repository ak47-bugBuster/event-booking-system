<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250607084619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE attendee (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL, UNIQUE INDEX UNIQ_1150D567E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, attendee_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_E00CEDDE71F7E88B (event_id), INDEX IDX_E00CEDDEBCFD782A (attendee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, location VARCHAR(100) NOT NULL, capacity INT NOT NULL, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEBCFD782A FOREIGN KEY (attendee_id) REFERENCES attendee (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE71F7E88B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEBCFD782A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE attendee
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE booking
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
