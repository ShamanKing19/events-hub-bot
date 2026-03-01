<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260301175701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Поля created_at и updated_at студентов';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE students ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE students DROP updated_at, DROP created_at');
    }
}
