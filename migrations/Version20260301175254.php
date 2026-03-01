<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260301175254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Индекс по ФИО студентов';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_A4698DB25E237E06 ON students (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_A4698DB25E237E06 ON students');
    }
}
