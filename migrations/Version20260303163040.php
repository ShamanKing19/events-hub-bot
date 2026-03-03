<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303163040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Даты начала, окончания, обновления и создания мероприятия';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events ADD start_date DATETIME NOT NULL, ADD finish_date DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP start_date, DROP finish_date, DROP updated_at, DROP created_at');
    }
}
