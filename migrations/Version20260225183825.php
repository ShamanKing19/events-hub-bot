<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225183825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Базовые таблицы';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(512) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE students (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE student_event (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, event_id INT NOT NULL, score DOUBLE PRECISION NOT NULL, INDEX IDX_B399733ACB944F1A (student_id), INDEX IDX_B399733A71F7E88B (event_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE student_event ADD CONSTRAINT FK_B399733ACB944F1A FOREIGN KEY (student_id) REFERENCES students (id)');
        $this->addSql('ALTER TABLE student_event ADD CONSTRAINT FK_B399733A71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE student_event DROP FOREIGN KEY FK_B399733ACB944F1A');
        $this->addSql('ALTER TABLE student_event DROP FOREIGN KEY FK_B399733A71F7E88B');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE student_event');
        $this->addSql('DROP TABLE students');
    }
}
