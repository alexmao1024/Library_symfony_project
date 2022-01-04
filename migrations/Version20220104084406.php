<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220104084406 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_ISBN ON book');
        $this->addSql('ALTER TABLE book CHANGE isbn isbn VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE borrow CHANGE isbn isbn VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book CHANGE isbn isbn INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_ISBN ON book (isbn)');
        $this->addSql('ALTER TABLE borrow CHANGE isbn isbn INT NOT NULL');
    }
}
