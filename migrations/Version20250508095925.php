<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508095925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE supplier ADD user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE supplier ADD CONSTRAINT FK_9B2A6C7EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9B2A6C7EA76ED395 ON supplier (user_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE supplier DROP FOREIGN KEY FK_9B2A6C7EA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_9B2A6C7EA76ED395 ON supplier
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE supplier DROP user_id
        SQL);
    }
}
