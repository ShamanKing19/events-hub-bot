<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313192256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Таблица пользователей';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (
            id INT AUTO_INCREMENT NOT NULL,
            chat_id INT NOT NULL,
            username VARCHAR(255) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE INDEX UNIQ_1483A5E91A9A7125 (chat_id),
            INDEX IDX_1483A5E9F85E0677 (username),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
