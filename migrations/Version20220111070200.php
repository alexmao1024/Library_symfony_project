<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220111070200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin_user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, balance DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book (id INT AUTO_INCREMENT NOT NULL, book_name VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, press VARCHAR(255) NOT NULL, quantity INT NOT NULL, price DOUBLE PRECISION NOT NULL, isbn VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE borrow (id INT AUTO_INCREMENT NOT NULL, borrower_id INT NOT NULL, book_name VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, borrow_at DATETIME DEFAULT NULL, return_at DATETIME DEFAULT NULL, spend DOUBLE PRECISION DEFAULT NULL, isbn VARCHAR(255) NOT NULL, INDEX IDX_55DBA8B011CE312B (borrower_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, normal_user_id INT NOT NULL, content VARCHAR(255) NOT NULL, INDEX IDX_B6BD307FDE39982E (normal_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE normal_user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscribe (id INT AUTO_INCREMENT NOT NULL, book_id INT NOT NULL, normal_user_id INT NOT NULL, subscribe_at DATETIME NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_68B95F3E16A2B381 (book_id), UNIQUE INDEX UNIQ_68B95F3EDE39982E (normal_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE borrow ADD CONSTRAINT FK_55DBA8B011CE312B FOREIGN KEY (borrower_id) REFERENCES normal_user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FDE39982E FOREIGN KEY (normal_user_id) REFERENCES normal_user (id)');
        $this->addSql('ALTER TABLE subscribe ADD CONSTRAINT FK_68B95F3E16A2B381 FOREIGN KEY (book_id) REFERENCES book (id)');
        $this->addSql('ALTER TABLE subscribe ADD CONSTRAINT FK_68B95F3EDE39982E FOREIGN KEY (normal_user_id) REFERENCES normal_user (id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_ISBN ON book (isbn)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscribe DROP FOREIGN KEY FK_68B95F3E16A2B381');
        $this->addSql('ALTER TABLE borrow DROP FOREIGN KEY FK_55DBA8B011CE312B');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FDE39982E');
        $this->addSql('ALTER TABLE subscribe DROP FOREIGN KEY FK_68B95F3EDE39982E');
        $this->addSql('DROP TABLE admin_user');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE borrow');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE normal_user');
        $this->addSql('DROP TABLE subscribe');
        $this->addSql('DROP INDEX uniq_ISBN ON book');
    }
}
