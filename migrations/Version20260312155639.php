<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312155639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Каскадное удаление очков при удалении студента или события';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE student_event DROP FOREIGN KEY `FK_B399733A71F7E88B`');
        $this->addSql('ALTER TABLE student_event DROP FOREIGN KEY `FK_B399733ACB944F1A`');
        $this->addSql('ALTER TABLE student_event ADD CONSTRAINT FK_B399733A71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_event ADD CONSTRAINT FK_B399733ACB944F1A FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE students CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE students CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE student_event DROP FOREIGN KEY FK_B399733ACB944F1A');
        $this->addSql('ALTER TABLE student_event DROP FOREIGN KEY FK_B399733A71F7E88B');
        $this->addSql('ALTER TABLE student_event ADD CONSTRAINT `FK_B399733ACB944F1A` FOREIGN KEY (student_id) REFERENCES students (id)');
        $this->addSql('ALTER TABLE student_event ADD CONSTRAINT `FK_B399733A71F7E88B` FOREIGN KEY (event_id) REFERENCES events (id)');
    }
}
