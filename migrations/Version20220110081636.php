<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220110081636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE normal_user DROP FOREIGN KEY FK_9811D429C72A4771');
        $this->addSql('DROP INDEX IDX_9811D429C72A4771 ON normal_user');
        $this->addSql('ALTER TABLE normal_user DROP subscribe_id');
        $this->addSql('ALTER TABLE subscribe ADD normal_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE subscribe ADD CONSTRAINT FK_68B95F3EDE39982E FOREIGN KEY (normal_user_id) REFERENCES normal_user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_68B95F3EDE39982E ON subscribe (normal_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE normal_user ADD subscribe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE normal_user ADD CONSTRAINT FK_9811D429C72A4771 FOREIGN KEY (subscribe_id) REFERENCES subscribe (id)');
        $this->addSql('CREATE INDEX IDX_9811D429C72A4771 ON normal_user (subscribe_id)');
        $this->addSql('ALTER TABLE subscribe DROP FOREIGN KEY FK_68B95F3EDE39982E');
        $this->addSql('DROP INDEX UNIQ_68B95F3EDE39982E ON subscribe');
        $this->addSql('ALTER TABLE subscribe DROP normal_user_id');
    }
}
