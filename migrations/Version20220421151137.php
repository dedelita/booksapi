<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220421151137 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CBE5A331CC1CF4E6 ON book (isbn)');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4EAFAD8B');
        $this->addSql('DROP INDEX UNIQ_9474526C4EAFAD8B ON comment');
        $this->addSql('ALTER TABLE comment DROP user_book_id');
        $this->addSql('ALTER TABLE user_book ADD comment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_book ADD CONSTRAINT FK_B164EFF8F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B164EFF8F8697D13 ON user_book (comment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_CBE5A331CC1CF4E6 ON book');
        $this->addSql('ALTER TABLE comment ADD user_book_id INT NOT NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4EAFAD8B FOREIGN KEY (user_book_id) REFERENCES user_book (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9474526C4EAFAD8B ON comment (user_book_id)');
        $this->addSql('ALTER TABLE user_book DROP FOREIGN KEY FK_B164EFF8F8697D13');
        $this->addSql('DROP INDEX UNIQ_B164EFF8F8697D13 ON user_book');
        $this->addSql('ALTER TABLE user_book DROP comment_id');
    }
}
